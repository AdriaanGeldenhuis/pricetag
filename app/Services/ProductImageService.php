<?php
/**
 * Product Image Service
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles all image operations for products including upload, processing,
 * conversion, and management with proper security measures.
 */

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

class ProductImageService
{
    /**
     * Allowed MIME types for image uploads
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    /**
     * Extension mapping for MIME types
     */
    private const MIME_TO_EXTENSION = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /**
     * Maximum file size in bytes (10MB default)
     */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /**
     * Default WebP quality
     */
    private const DEFAULT_WEBP_QUALITY = 85;

    /**
     * Default max dimensions
     */
    private const DEFAULT_MAX_WIDTH = 1024;
    private const DEFAULT_MAX_HEIGHT = 1024;

    /**
     * Upload directory relative to STORAGE_PATH/uploads
     */
    private const UPLOAD_DIR = 'products';

    /**
     * Database instance
     */
    private PDO $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // =========================================================================
    // UPLOAD & PROCESSING
    // =========================================================================

    /**
     * Upload and process an image for a product
     *
     * @param int $productId The product ID to associate the image with
     * @param array $file The $_FILES array element for the uploaded file
     * @param bool $isPrimary Whether this should be the primary image
     * @return array ['success' => bool, 'image_id' => int|null, 'path' => string|null, 'error' => string|null]
     */
    public function uploadImage(int $productId, array $file, bool $isPrimary = false): array
    {
        // Validate the file first
        $validation = $this->validateImageFile($file);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'image_id' => null,
                'path' => null,
                'error' => $validation['error'],
            ];
        }

        // Verify product exists
        $stmt = $this->db->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        if (!$stmt->fetch()) {
            logMessage('error', 'ProductImageService: Product not found', ['product_id' => $productId]);
            return [
                'success' => false,
                'image_id' => null,
                'path' => null,
                'error' => 'Product not found',
            ];
        }

        try {
            // Generate unique filename
            $filename = $this->generateFilename($productId);
            $relativePath = self::UPLOAD_DIR . '/' . $filename;
            $fullPath = STORAGE_PATH . '/uploads/' . $relativePath;

            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new \RuntimeException('Failed to create upload directory');
                }
            }

            // Process and convert the image
            $processed = $this->processImage(
                $file['tmp_name'],
                $fullPath,
                self::DEFAULT_MAX_WIDTH,
                self::DEFAULT_MAX_HEIGHT
            );

            if (!$processed) {
                return [
                    'success' => false,
                    'image_id' => null,
                    'path' => null,
                    'error' => 'Failed to process image',
                ];
            }

            // If setting as primary, reset other images first
            if ($isPrimary) {
                $stmt = $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
                $stmt->execute([$productId]);
            } else {
                // Check if this is the first image (should be primary by default)
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?");
                $stmt->execute([$productId]);
                if ((int) $stmt->fetchColumn() === 0) {
                    $isPrimary = true;
                }
            }

            // Get the next sort order
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM product_images WHERE product_id = ?");
            $stmt->execute([$productId]);
            $sortOrder = (int) $stmt->fetchColumn();

            // Insert into database
            $stmt = $this->db->prepare(
                "INSERT INTO product_images (product_id, path, is_primary, sort_order, created_at)
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$productId, $relativePath, $isPrimary ? 1 : 0, $sortOrder]);
            $imageId = (int) $this->db->lastInsertId();

            logMessage('info', 'ProductImageService: Image uploaded successfully', [
                'product_id' => $productId,
                'image_id' => $imageId,
                'path' => $relativePath,
            ]);

            return [
                'success' => true,
                'image_id' => $imageId,
                'path' => $relativePath,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            logMessage('error', 'ProductImageService: Upload failed', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            // Clean up any partially created file
            if (isset($fullPath) && file_exists($fullPath)) {
                @unlink($fullPath);
            }

            return [
                'success' => false,
                'image_id' => null,
                'path' => null,
                'error' => 'Upload failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process an image: resize and optionally crop to fit within dimensions
     *
     * @param string $sourcePath Path to the source image
     * @param string $destPath Path where processed image will be saved
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @return bool Success status
     */
    public function processImage(
        string $sourcePath,
        string $destPath,
        int $maxWidth = self::DEFAULT_MAX_WIDTH,
        int $maxHeight = self::DEFAULT_MAX_HEIGHT
    ): bool {
        // Check memory limit for large images
        $this->ensureMemoryForImage($sourcePath);

        // Get image info
        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            logMessage('error', 'ProductImageService: Could not get image info', ['path' => $sourcePath]);
            return false;
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Create source image based on type
        $sourceImage = $this->createImageFromFile($sourcePath, $mimeType);
        if (!$sourceImage) {
            logMessage('error', 'ProductImageService: Could not create image resource', [
                'path' => $sourcePath,
                'mime' => $mimeType,
            ]);
            return false;
        }

        try {
            // Calculate dimensions for square crop (center crop)
            $cropSize = min($sourceWidth, $sourceHeight);
            $cropX = (int) (($sourceWidth - $cropSize) / 2);
            $cropY = (int) (($sourceHeight - $cropSize) / 2);
            $targetSize = min($maxWidth, $maxHeight);

            // Create destination image
            $destImage = imagecreatetruecolor($targetSize, $targetSize);
            if (!$destImage) {
                throw new \RuntimeException('Failed to create destination image');
            }

            // Preserve transparency
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
            imagefilledrectangle($destImage, 0, 0, $targetSize, $targetSize, $transparent);

            // Resize and crop
            $result = imagecopyresampled(
                $destImage,
                $sourceImage,
                0, 0,
                $cropX, $cropY,
                $targetSize, $targetSize,
                $cropSize, $cropSize
            );

            if (!$result) {
                throw new \RuntimeException('Failed to resample image');
            }

            // Save as WebP
            $success = $this->convertToWebp($destImage, $destPath, self::DEFAULT_WEBP_QUALITY);

            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($destImage);

            return $success;
        } catch (\Throwable $e) {
            logMessage('error', 'ProductImageService: Image processing failed', [
                'error' => $e->getMessage(),
            ]);

            if (isset($sourceImage) && $sourceImage) {
                imagedestroy($sourceImage);
            }
            if (isset($destImage) && $destImage) {
                imagedestroy($destImage);
            }

            return false;
        }
    }

    /**
     * Convert an image resource to WebP format
     *
     * @param \GdImage|resource|string $source GD image resource or path to source file
     * @param string $destPath Destination path for WebP file
     * @param int $quality WebP quality (0-100)
     * @return bool Success status
     */
    public function convertToWebp(mixed $source, string $destPath, int $quality = self::DEFAULT_WEBP_QUALITY): bool
    {
        // Ensure .webp extension
        if (!str_ends_with(strtolower($destPath), '.webp')) {
            $destPath = preg_replace('/\.[^.]+$/', '.webp', $destPath);
            if (!str_ends_with(strtolower($destPath), '.webp')) {
                $destPath .= '.webp';
            }
        }

        // If source is a path string, load it first
        if (is_string($source)) {
            $imageInfo = @getimagesize($source);
            if (!$imageInfo) {
                logMessage('error', 'ProductImageService: Cannot read source for WebP conversion', ['path' => $source]);
                return false;
            }

            $sourceImage = $this->createImageFromFile($source, $imageInfo['mime']);
            if (!$sourceImage) {
                return false;
            }

            $success = imagewebp($sourceImage, $destPath, $quality);
            imagedestroy($sourceImage);

            return $success;
        }

        // Source is already a GD image resource
        return imagewebp($source, $destPath, $quality);
    }

    // =========================================================================
    // MANAGEMENT
    // =========================================================================

    /**
     * Delete an image by ID
     *
     * @param int $imageId The image ID to delete
     * @return bool Success status
     */
    public function deleteImage(int $imageId): bool
    {
        try {
            // Get image info
            $stmt = $this->db->prepare("SELECT * FROM product_images WHERE id = ?");
            $stmt->execute([$imageId]);
            $image = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$image) {
                logMessage('warning', 'ProductImageService: Image not found for deletion', ['image_id' => $imageId]);
                return false;
            }

            $productId = (int) $image['product_id'];
            $imagePath = $image['path'];
            $wasPrimary = (bool) $image['is_primary'];

            // Delete from database first
            $stmt = $this->db->prepare("DELETE FROM product_images WHERE id = ?");
            $stmt->execute([$imageId]);

            // Check if this image file is used by any other product
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM product_images WHERE path = ?");
            $stmt->execute([$imagePath]);
            $usageCount = (int) $stmt->fetchColumn();

            // Only delete the file if no other products are using it
            if ($usageCount === 0) {
                $fullPath = STORAGE_PATH . '/uploads/' . $imagePath;
                if (file_exists($fullPath)) {
                    if (!@unlink($fullPath)) {
                        logMessage('warning', 'ProductImageService: Could not delete file', ['path' => $fullPath]);
                    }
                }
            }

            // If this was primary, set another image as primary
            if ($wasPrimary) {
                $stmt = $this->db->prepare(
                    "UPDATE product_images SET is_primary = 1
                     WHERE product_id = ?
                     ORDER BY sort_order ASC, id ASC
                     LIMIT 1"
                );
                $stmt->execute([$productId]);
            }

            logMessage('info', 'ProductImageService: Image deleted', [
                'image_id' => $imageId,
                'product_id' => $productId,
            ]);

            return true;
        } catch (\Throwable $e) {
            logMessage('error', 'ProductImageService: Delete failed', [
                'image_id' => $imageId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Set an image as the primary image for a product
     *
     * @param int $productId The product ID
     * @param int $imageId The image ID to set as primary
     * @return bool Success status
     */
    public function setPrimaryImage(int $productId, int $imageId): bool
    {
        try {
            // Verify the image belongs to this product
            $stmt = $this->db->prepare("SELECT id FROM product_images WHERE id = ? AND product_id = ?");
            $stmt->execute([$imageId, $productId]);
            if (!$stmt->fetch()) {
                logMessage('warning', 'ProductImageService: Image not found for product', [
                    'image_id' => $imageId,
                    'product_id' => $productId,
                ]);
                return false;
            }

            // Start transaction for atomic update
            $this->db->beginTransaction();

            // Reset all images for this product to non-primary
            $stmt = $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
            $stmt->execute([$productId]);

            // Set this image as primary
            $stmt = $this->db->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?");
            $stmt->execute([$imageId]);

            $this->db->commit();

            logMessage('info', 'ProductImageService: Primary image set', [
                'image_id' => $imageId,
                'product_id' => $productId,
            ]);

            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            logMessage('error', 'ProductImageService: Set primary failed', [
                'image_id' => $imageId,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Reorder images for a product
     *
     * @param int $productId The product ID
     * @param array $imageIds Array of image IDs in the desired order
     * @return bool Success status
     */
    public function reorderImages(int $productId, array $imageIds): bool
    {
        if (empty($imageIds)) {
            return true;
        }

        try {
            $this->db->beginTransaction();

            foreach ($imageIds as $sortOrder => $imageId) {
                $imageId = (int) $imageId;
                if ($imageId <= 0) {
                    continue;
                }

                $stmt = $this->db->prepare(
                    "UPDATE product_images SET sort_order = ? WHERE id = ? AND product_id = ?"
                );
                $stmt->execute([$sortOrder, $imageId, $productId]);
            }

            $this->db->commit();

            logMessage('info', 'ProductImageService: Images reordered', [
                'product_id' => $productId,
                'image_count' => count($imageIds),
            ]);

            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            logMessage('error', 'ProductImageService: Reorder failed', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get all images for a product
     *
     * @param int $productId The product ID
     * @return array Array of image records
     */
    public function getProductImages(int $productId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, product_id, path, is_primary, sort_order, created_at
             FROM product_images
             WHERE product_id = ?
             ORDER BY sort_order ASC, is_primary DESC, id ASC"
        );
        $stmt->execute([$productId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // =========================================================================
    // BATCH OPERATIONS
    // =========================================================================

    /**
     * Upload multiple images for a product
     *
     * @param int $productId The product ID
     * @param array $files The $_FILES array (can be multiple files or single)
     * @return array ['success' => [], 'failed' => []]
     */
    public function uploadMultipleImages(int $productId, array $files): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        // Normalize the files array structure
        $normalizedFiles = $this->normalizeFilesArray($files);

        foreach ($normalizedFiles as $index => $file) {
            // Skip empty uploads
            if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
                if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                    $results['failed'][] = [
                        'name' => $file['name'] ?? "File $index",
                        'error' => $this->getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE),
                    ];
                }
                continue;
            }

            // First image should be primary if no images exist yet
            $isPrimary = empty($results['success']) && empty($this->getProductImages($productId));

            $result = $this->uploadImage($productId, $file, $isPrimary);

            if ($result['success']) {
                $results['success'][] = [
                    'name' => $file['name'],
                    'image_id' => $result['image_id'],
                    'path' => $result['path'],
                ];
            } else {
                $results['failed'][] = [
                    'name' => $file['name'],
                    'error' => $result['error'],
                ];
            }
        }

        logMessage('info', 'ProductImageService: Batch upload completed', [
            'product_id' => $productId,
            'success_count' => count($results['success']),
            'failed_count' => count($results['failed']),
        ]);

        return $results;
    }

    /**
     * Delete all images for a product
     *
     * @param int $productId The product ID
     * @return int Number of images deleted
     */
    public function deleteAllProductImages(int $productId): int
    {
        try {
            // Get all images for the product
            $images = $this->getProductImages($productId);
            $deletedCount = 0;

            foreach ($images as $image) {
                if ($this->deleteImage((int) $image['id'])) {
                    $deletedCount++;
                }
            }

            logMessage('info', 'ProductImageService: All product images deleted', [
                'product_id' => $productId,
                'deleted_count' => $deletedCount,
            ]);

            return $deletedCount;
        } catch (\Throwable $e) {
            logMessage('error', 'ProductImageService: Batch delete failed', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================

    /**
     * Validate an uploaded image file
     *
     * @param array $file The $_FILES array element
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validateImageFile(array $file): array
    {
        // Check for upload errors
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'error' => $this->getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE),
            ];
        }

        // Check if file exists
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [
                'valid' => false,
                'error' => 'No file was uploaded or file is invalid',
            ];
        }

        // Check file size
        $maxSize = $this->getMaxFileSize();
        if ($file['size'] > $maxSize) {
            return [
                'valid' => false,
                'error' => sprintf('File size exceeds maximum allowed (%s)', $this->formatBytes($maxSize)),
            ];
        }

        // Validate MIME type using finfo (more secure than relying on browser-provided type)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $this->getAllowedMimeTypes(), true)) {
            return [
                'valid' => false,
                'error' => sprintf('Invalid file type: %s. Allowed types: %s',
                    $mimeType,
                    implode(', ', $this->getAllowedMimeTypes())
                ),
            ];
        }

        // Verify it's actually an image by attempting to read its dimensions
        $imageInfo = @getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            return [
                'valid' => false,
                'error' => 'File does not appear to be a valid image',
            ];
        }

        // Check for reasonable image dimensions (prevent DoS with huge images)
        $maxDimension = 10000; // 10000px max
        if ($imageInfo[0] > $maxDimension || $imageInfo[1] > $maxDimension) {
            return [
                'valid' => false,
                'error' => sprintf('Image dimensions exceed maximum allowed (%dpx)', $maxDimension),
            ];
        }

        return [
            'valid' => true,
            'error' => null,
        ];
    }

    /**
     * Get allowed MIME types for image uploads
     *
     * @return array
     */
    public function getAllowedMimeTypes(): array
    {
        return self::ALLOWED_MIME_TYPES;
    }

    /**
     * Get maximum file size in bytes
     *
     * @return int
     */
    public function getMaxFileSize(): int
    {
        // Check PHP configuration limits
        $uploadMax = $this->parseIniSize(ini_get('upload_max_filesize'));
        $postMax = $this->parseIniSize(ini_get('post_max_size'));

        // Return the smallest of our limit and PHP limits
        return min(self::MAX_FILE_SIZE, $uploadMax, $postMax);
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Generate a unique, secure filename for an uploaded image
     *
     * @param int $productId The product ID
     * @return string The generated filename
     */
    private function generateFilename(int $productId): string
    {
        // Use random bytes for security (prevents filename guessing)
        $randomPart = bin2hex(random_bytes(8));
        $timestamp = time();

        return sprintf('product-%d-%d-%s.webp', $productId, $timestamp, $randomPart);
    }

    /**
     * Sanitize a filename for safe storage
     *
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove path information
        $filename = basename($filename);

        // Remove any non-alphanumeric characters except dots, hyphens, and underscores
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

        // Remove multiple dots (security measure)
        $filename = preg_replace('/\.+/', '.', $filename);

        // Ensure it doesn't start with a dot
        $filename = ltrim($filename, '.');

        // If empty after sanitization, generate a random name
        if (empty($filename)) {
            $filename = bin2hex(random_bytes(8));
        }

        return $filename;
    }

    /**
     * Create a GD image resource from a file
     *
     * @param string $path Path to the image file
     * @param string $mimeType The MIME type of the image
     * @return \GdImage|resource|false
     */
    private function createImageFromFile(string $path, string $mimeType): mixed
    {
        return match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/gif' => @imagecreatefromgif($path),
            default => false,
        };
    }

    /**
     * Normalize a $_FILES array to handle both single and multiple uploads
     *
     * @param array $files The $_FILES array
     * @return array Normalized array of file info
     */
    private function normalizeFilesArray(array $files): array
    {
        $normalized = [];

        // Check if this is a multiple file upload
        if (isset($files['name']) && is_array($files['name'])) {
            $fileCount = count($files['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                $normalized[] = [
                    'name' => $files['name'][$i] ?? '',
                    'type' => $files['type'][$i] ?? '',
                    'tmp_name' => $files['tmp_name'][$i] ?? '',
                    'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $files['size'][$i] ?? 0,
                ];
            }
        } elseif (isset($files['tmp_name'])) {
            // Single file upload
            $normalized[] = $files;
        }

        return $normalized;
    }

    /**
     * Get a human-readable error message for an upload error code
     *
     * @param int $errorCode The PHP upload error code
     * @return string Human-readable error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds maximum upload size allowed by server',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds maximum size specified in form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by a PHP extension',
            default => 'Unknown upload error',
        };
    }

    /**
     * Parse PHP ini size notation to bytes
     *
     * @param string $size Size string (e.g., '8M', '512K')
     * @return int Size in bytes
     */
    private function parseIniSize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1] ?? '');
        $value = (int) $size;

        return match ($last) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    /**
     * Format bytes to human-readable string
     *
     * @param int $bytes Number of bytes
     * @return string Formatted string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Ensure sufficient memory is available to process an image
     *
     * @param string $imagePath Path to the image
     * @return void
     */
    private function ensureMemoryForImage(string $imagePath): void
    {
        $imageInfo = @getimagesize($imagePath);
        if (!$imageInfo) {
            return;
        }

        // Calculate approximate memory needed (width * height * 4 bytes per pixel * 2 for processing)
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $channels = $imageInfo['channels'] ?? 4;
        $bits = $imageInfo['bits'] ?? 8;

        // Formula: width * height * (bits/8) * channels * 2 (source + destination)
        // Add 20% buffer
        $requiredMemory = (int) ($width * $height * ($bits / 8) * $channels * 2 * 1.2);

        // Add current usage
        $currentUsage = memory_get_usage(true);
        $totalRequired = $currentUsage + $requiredMemory;

        // Get current limit
        $currentLimit = $this->parseIniSize(ini_get('memory_limit'));

        // If -1 (unlimited), no need to adjust
        if ($currentLimit === -1) {
            return;
        }

        // If we need more, try to increase the limit
        if ($totalRequired > $currentLimit) {
            $newLimit = min($totalRequired + (32 * 1024 * 1024), 512 * 1024 * 1024); // Max 512MB
            @ini_set('memory_limit', $this->formatBytes($newLimit));

            logMessage('info', 'ProductImageService: Increased memory limit for image processing', [
                'old_limit' => $this->formatBytes($currentLimit),
                'new_limit' => $this->formatBytes($newLimit),
                'image_dimensions' => "{$width}x{$height}",
            ]);
        }
    }

    /**
     * Get the primary image for a product
     *
     * @param int $productId The product ID
     * @return array|null The primary image record or null
     */
    public function getPrimaryImage(int $productId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, product_id, path, is_primary, sort_order, created_at
             FROM product_images
             WHERE product_id = ? AND is_primary = 1
             LIMIT 1"
        );
        $stmt->execute([$productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Get the full URL path for an image
     *
     * @param string $relativePath The relative path stored in database
     * @return string The full URL
     */
    public function getImageUrl(string $relativePath): string
    {
        return url('storage/uploads/' . $relativePath);
    }

    /**
     * Get the full filesystem path for an image
     *
     * @param string $relativePath The relative path stored in database
     * @return string The full filesystem path
     */
    public function getImagePath(string $relativePath): string
    {
        return STORAGE_PATH . '/uploads/' . $relativePath;
    }

    /**
     * Check if an image file exists on disk
     *
     * @param string $relativePath The relative path stored in database
     * @return bool
     */
    public function imageExists(string $relativePath): bool
    {
        return file_exists($this->getImagePath($relativePath));
    }
}
