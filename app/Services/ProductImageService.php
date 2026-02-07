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

    // =========================================================================
    // AI IMAGE DOWNLOAD (Search & Download real product images from the web)
    // =========================================================================

    /**
     * Download real product images from the web for a product.
     * Searches for the product by name/SKU and downloads up to 4 images.
     * Falls back to generating branded info-card images via GD if web search fails.
     * Skips products that already have 4+ images.
     *
     * @param int $productId
     * @param array $productData Keys: name, brand, sku, category, short_description, specifications
     * @return array{success: bool, generated: int, message: string}
     */
    public function generateProductImages(int $productId, array $productData): array
    {
        // Validate product exists
        $stmt = $this->db->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'generated' => 0, 'message' => 'Product not found'];
        }

        // Check existing image count
        $existingImages = $this->getProductImages($productId);
        if (count($existingImages) >= 4) {
            return ['success' => true, 'generated' => 0, 'message' => 'Product already has 4+ images'];
        }

        $needed = 4 - count($existingImages);
        $name = $productData['name'] ?? '';
        $brand = $productData['brand'] ?? '';
        $sku = $productData['sku'] ?? '';

        if (empty($name) && empty($sku)) {
            return ['success' => false, 'generated' => 0, 'message' => 'No product name or SKU to search for'];
        }

        // Build search queries - try different variations to get diverse images
        $searchQueries = $this->buildImageSearchQueries($name, $brand, $sku);

        // Phase 1: Try web search for real product images
        $downloaded = 0;
        $hasPrimary = !empty($existingImages);
        $usedUrls = [];

        foreach ($searchQueries as $query) {
            if ($downloaded >= $needed) break;

            $imageUrls = $this->searchGoogleImages($query, $needed - $downloaded + 2);

            foreach ($imageUrls as $url) {
                if ($downloaded >= $needed) break;
                if (in_array($url, $usedUrls)) continue;
                $usedUrls[] = $url;

                $isPrimary = !$hasPrimary && $downloaded === 0;
                $altText = $name ?: $sku;

                $result = $this->downloadAndSaveImage($productId, $url, $altText, $isPrimary);
                if ($result['success']) {
                    $downloaded++;
                }
            }
        }

        // Phase 2: If web search didn't find enough, generate branded info cards via GD
        if ($downloaded < $needed) {
            $remaining = $needed - $downloaded;
            $generated = $this->generateInfoCards($productId, $productData, $remaining, !$hasPrimary && $downloaded === 0);
            $downloaded += $generated;
        }

        return [
            'success' => $downloaded > 0,
            'generated' => $downloaded,
            'message' => $downloaded > 0
                ? "Generated {$downloaded} product image(s)"
                : 'Could not generate product images',
        ];
    }

    /**
     * Build varied search queries for finding product images
     */
    private function buildImageSearchQueries(string $name, string $brand, string $sku): array
    {
        $queries = [];

        // Primary: full product name + "product image"
        if ($name) {
            $queries[] = $name . ' product image';
            $queries[] = $name . ' box';
        }

        // Brand + SKU
        if ($brand && $sku) {
            $queries[] = $brand . ' ' . $sku . ' product';
        }

        // Just the name if different queries needed
        if ($name) {
            $queries[] = $name;
        }

        return array_slice($queries, 0, 4);
    }

    /**
     * Search for product images using multiple strategies.
     * Tries Bing Image Search (more reliable HTML scraping than Google),
     * then DuckDuckGo as fallback.
     */
    private function searchGoogleImages(string $query, int $count = 5): array
    {
        $urls = [];

        // Strategy 1: Bing Images (most reliable for scraping)
        $urls = $this->searchBingImages($query, $count);

        // Strategy 2: DuckDuckGo vqd-based image search
        if (count($urls) < $count) {
            $ddgUrls = $this->searchDuckDuckGoImages($query, $count - count($urls));
            $urls = array_merge($urls, $ddgUrls);
        }

        // Strategy 3: Google Images as last resort
        if (count($urls) < $count) {
            $googleUrls = $this->searchGoogleImagesHtml($query, $count - count($urls));
            $urls = array_merge($urls, $googleUrls);
        }

        return array_slice(array_unique($urls), 0, $count);
    }

    /**
     * Search Bing Images - more reliable than Google for HTML scraping
     */
    private function searchBingImages(string $query, int $count = 5): array
    {
        $urls = [];
        $searchUrl = 'https://www.bing.com/images/search?q=' . urlencode($query) . '&qft=+filterui:photo-photo&form=IRFLTR&first=1';

        $html = $this->fetchUrl($searchUrl);
        if (!$html) return [];

        // Bing embeds image URLs in data attributes: murl="https://..."
        if (preg_match_all('/murl[&"]:\s*[&"]?(https?:\/\/[^"&]+\.(?:jpg|jpeg|png|webp)(?:\?[^"&]*)?)/i', $html, $matches)) {
            foreach ($matches[1] as $url) {
                $url = html_entity_decode($url, ENT_QUOTES);
                $url = str_replace('&amp;', '&', $url);
                if ($this->isValidImageUrl($url) && !in_array($url, $urls)) {
                    $urls[] = $url;
                    if (count($urls) >= $count) break;
                }
            }
        }

        // Fallback: look for imgurl in href parameters
        if (count($urls) < $count) {
            if (preg_match_all('/imgurl:(https?[^&"]+)/i', $html, $matches)) {
                foreach ($matches[1] as $url) {
                    $url = urldecode($url);
                    if ($this->isValidImageUrl($url) && !in_array($url, $urls)) {
                        $urls[] = $url;
                        if (count($urls) >= $count) break;
                    }
                }
            }
        }

        // Fallback: data-src or src with real image URLs
        if (count($urls) < $count) {
            if (preg_match_all('/(?:data-src|src)="(https?:\/\/(?!www\.bing|tse\d)[^"]+\.(?:jpg|jpeg|png|webp)[^"]*)"/i', $html, $matches)) {
                foreach ($matches[1] as $url) {
                    if ($this->isValidImageUrl($url) && !in_array($url, $urls)) {
                        $urls[] = $url;
                        if (count($urls) >= $count) break;
                    }
                }
            }
        }

        if (!empty($urls)) {
            error_log("ProductImageService: Bing found " . count($urls) . " images for '{$query}'");
        }

        return $urls;
    }

    /**
     * Search DuckDuckGo Images via their API-like endpoint
     */
    private function searchDuckDuckGoImages(string $query, int $count = 5): array
    {
        $urls = [];

        // First get a vqd token from DDG
        $tokenUrl = 'https://duckduckgo.com/?q=' . urlencode($query) . '&iar=images&iax=images&ia=images';
        $html = $this->fetchUrl($tokenUrl);
        if (!$html) return [];

        // Extract vqd token
        $vqd = '';
        if (preg_match('/vqd=["\']([^"\']+)/', $html, $m)) {
            $vqd = $m[1];
        } elseif (preg_match('/vqd=(\d+-\d+(?:-\d+)*)/', $html, $m)) {
            $vqd = $m[1];
        }

        if (empty($vqd)) {
            // Fallback: extract image URLs directly from the HTML
            if (preg_match_all('/"(https?:\/\/[^"]+\.(?:jpg|jpeg|png|webp)(?:\?[^"]*)?)"/', $html, $matches)) {
                foreach ($matches[1] as $url) {
                    if ($this->isValidImageUrl($url) && !in_array($url, $urls)) {
                        $urls[] = $url;
                        if (count($urls) >= $count) break;
                    }
                }
            }
            return $urls;
        }

        // Fetch image results from DDG API
        $apiUrl = 'https://duckduckgo.com/i.js?l=us-en&o=json&q=' . urlencode($query) . '&vqd=' . $vqd . '&f=size:Large&p=1';
        $json = $this->fetchUrl($apiUrl);
        if (!$json) return $urls;

        $data = @json_decode($json, true);
        if (!empty($data['results'])) {
            foreach ($data['results'] as $result) {
                $imgUrl = $result['image'] ?? '';
                if ($imgUrl && $this->isValidImageUrl($imgUrl) && !in_array($imgUrl, $urls)) {
                    $urls[] = $imgUrl;
                    if (count($urls) >= $count) break;
                }
            }
        }

        if (!empty($urls)) {
            error_log("ProductImageService: DuckDuckGo found " . count($urls) . " images for '{$query}'");
        }

        return $urls;
    }

    /**
     * Search Google Images HTML (legacy fallback)
     */
    private function searchGoogleImagesHtml(string $query, int $count = 5): array
    {
        $urls = [];
        $searchUrl = 'https://www.google.com/search?q=' . urlencode($query) . '&tbm=isch&safe=active';

        $html = $this->fetchUrl($searchUrl);
        if (!$html) return [];

        // Method 1: Full-size image URLs in Google's JSON data
        if (preg_match_all('/\["(https?:\/\/[^"]+\.(?:jpg|jpeg|png|webp)(?:\?[^"]*)?)",\s*\d+,\s*\d+\]/i', $html, $matches)) {
            foreach ($matches[1] as $url) {
                $url = str_replace(['\u003d', '\u0026', '\\/', '\\/'], ['=', '&', '/', '/'], $url);
                if ($this->isValidImageUrl($url) && !in_array($url, $urls)) {
                    $urls[] = $url;
                    if (count($urls) >= $count) break;
                }
            }
        }

        // Method 2: Generic image URLs in script tags
        if (count($urls) < $count) {
            if (preg_match_all('/"(https?:\/\/[^"]+\.(?:jpg|jpeg|png|webp)[^"]*)"/', $html, $matches)) {
                foreach ($matches[1] as $url) {
                    $url = str_replace(['\\u003d', '\\u0026', '\\/', '\\/'], ['=', '&', '/', '/'], $url);
                    if ($this->isValidImageUrl($url) && !in_array($url, $urls)) {
                        $urls[] = $url;
                        if (count($urls) >= $count) break;
                    }
                }
            }
        }

        return $urls;
    }

    /**
     * Fetch a URL with proper headers. Shared helper for all search methods.
     */
    private function fetchUrl(string $url): ?string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9',
                'Accept-Encoding: identity',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_ENCODING => '',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200 || !$response) {
            error_log("ProductImageService: Fetch failed for '{$url}' (HTTP {$httpCode}): {$error}");
            return null;
        }

        return $response;
    }

    /**
     * Check if a URL is a valid product image (not a thumbnail, icon, or tracking pixel)
     */
    private function isValidImageUrl(string $url): bool
    {
        // Skip search engine thumbnails
        if (str_contains($url, 'encrypted-tbn')) return false;
        if (str_contains($url, 'gstatic.com')) return false;
        if (str_contains($url, 'google.com')) return false;
        if (str_contains($url, 'googleapis.com')) return false;
        if (str_contains($url, 'bing.com')) return false;
        if (str_contains($url, 'bing.net')) return false;
        if (str_contains($url, 'duckduckgo.com')) return false;

        // Skip tiny icons/favicons
        if (str_contains($url, 'favicon')) return false;
        if (str_contains($url, 'logo') && !str_contains($url, 'product')) return false;

        // Skip tracking pixels and ads
        if (str_contains($url, 'pixel')) return false;
        if (str_contains($url, 'tracking')) return false;
        if (str_contains($url, '1x1')) return false;
        if (str_contains($url, 'spacer')) return false;

        // Must be a proper image URL
        if (!preg_match('/\.(jpg|jpeg|png|webp)(\?|$)/i', $url)) return false;

        // Must be a reasonable length (real URLs)
        if (strlen($url) < 30) return false;

        return true;
    }

    // =========================================================================
    // GD-BASED PRODUCT INFO CARD GENERATION (Guaranteed fallback)
    // =========================================================================

    /**
     * Generate branded product info-card images using PHP GD.
     * Creates professional-looking cards with product name, specs, and Pricetag branding.
     * This is 100% reliable as it has no external dependencies.
     *
     * @param int $productId
     * @param array $productData Product details (name, brand, sku, category, short_description, specifications)
     * @param int $count Number of cards to generate
     * @param bool $firstIsPrimary Whether the first card should be set as primary
     * @return int Number of cards successfully generated
     */
    private function generateInfoCards(int $productId, array $productData, int $count, bool $firstIsPrimary): int
    {
        $name = $productData['name'] ?? ($productData['sku'] ?? 'Product');
        $brand = $productData['brand'] ?? '';
        $sku = $productData['sku'] ?? '';
        $category = $productData['category'] ?? '';
        $shortDesc = $productData['short_description'] ?? '';
        $specs = $productData['specifications'] ?? [];

        // Define card themes (each card has a different style)
        $cardStyles = [
            [
                'type' => 'hero',
                'bg' => [45, 15, 15],       // Dark burgundy
                'accent' => [180, 60, 60],   // Lighter burgundy
                'text' => [255, 255, 255],
                'subtitle' => [220, 180, 180],
            ],
            [
                'type' => 'specs',
                'bg' => [20, 20, 30],        // Dark navy
                'accent' => [60, 100, 180],  // Blue accent
                'text' => [255, 255, 255],
                'subtitle' => [180, 200, 230],
            ],
            [
                'type' => 'minimal',
                'bg' => [250, 250, 250],     // White
                'accent' => [139, 43, 43],   // Pricetag burgundy
                'text' => [30, 30, 30],
                'subtitle' => [100, 100, 100],
            ],
            [
                'type' => 'gradient',
                'bg' => [25, 25, 35],        // Dark
                'accent' => [139, 43, 43],   // Pricetag burgundy
                'text' => [255, 255, 255],
                'subtitle' => [200, 200, 200],
            ],
        ];

        $generated = 0;

        for ($i = 0; $i < $count && $i < 4; $i++) {
            $style = $cardStyles[$i % count($cardStyles)];
            $isPrimary = $firstIsPrimary && $i === 0;

            $result = $this->createInfoCardImage($productId, $name, $brand, $sku, $category, $shortDesc, $specs, $style, $isPrimary);
            if ($result) {
                $generated++;
            }
        }

        if ($generated > 0) {
            error_log("ProductImageService: Generated {$generated} info card(s) for product {$productId}");
        }

        return $generated;
    }

    /**
     * Create a single info card image using GD
     */
    private function createInfoCardImage(
        int $productId,
        string $name,
        string $brand,
        string $sku,
        string $category,
        string $shortDesc,
        array $specs,
        array $style,
        bool $isPrimary
    ): bool {
        $size = 1024;
        $img = imagecreatetruecolor($size, $size);
        if (!$img) return false;

        try {
            // Enable anti-aliasing
            imageantialias($img, true);

            // Colors
            $bgColor = imagecolorallocate($img, $style['bg'][0], $style['bg'][1], $style['bg'][2]);
            $accentColor = imagecolorallocate($img, $style['accent'][0], $style['accent'][1], $style['accent'][2]);
            $textColor = imagecolorallocate($img, $style['text'][0], $style['text'][1], $style['text'][2]);
            $subtitleColor = imagecolorallocate($img, $style['subtitle'][0], $style['subtitle'][1], $style['subtitle'][2]);
            $dimColor = imagecolorallocate($img,
                (int)(($style['text'][0] + $style['bg'][0]) / 2),
                (int)(($style['text'][1] + $style['bg'][1]) / 2),
                (int)(($style['text'][2] + $style['bg'][2]) / 2)
            );

            // Fill background
            imagefilledrectangle($img, 0, 0, $size - 1, $size - 1, $bgColor);

            // Try to use a TTF font, fall back to built-in GD fonts
            $fontFile = $this->findSystemFont();

            if ($style['type'] === 'hero') {
                $this->drawHeroCard($img, $size, $name, $brand, $sku, $category, $accentColor, $textColor, $subtitleColor, $dimColor, $fontFile);
            } elseif ($style['type'] === 'specs') {
                $this->drawSpecsCard($img, $size, $name, $brand, $specs, $accentColor, $textColor, $subtitleColor, $dimColor, $fontFile);
            } elseif ($style['type'] === 'minimal') {
                $this->drawMinimalCard($img, $size, $name, $brand, $sku, $shortDesc, $accentColor, $textColor, $subtitleColor, $dimColor, $fontFile);
            } else {
                $this->drawGradientCard($img, $size, $name, $brand, $category, $specs, $accentColor, $textColor, $subtitleColor, $dimColor, $bgColor, $fontFile);
            }

            // Draw Pricetag.co.za branding at bottom
            $this->drawBranding($img, $size, $accentColor, $textColor, $subtitleColor, $fontFile);

            // Save to WebP file
            $filename = $this->generateFilename($productId);
            $relativePath = self::UPLOAD_DIR . '/' . $filename;
            $fullPath = STORAGE_PATH . '/uploads/' . $relativePath;

            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            imagewebp($img, $fullPath, self::DEFAULT_WEBP_QUALITY);
            imagedestroy($img);

            if (!file_exists($fullPath) || filesize($fullPath) < 100) {
                return false;
            }

            // Save to database
            if ($isPrimary) {
                $stmt = $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
                $stmt->execute([$productId]);
            }

            $stmt = $this->db->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM product_images WHERE product_id = ?");
            $stmt->execute([$productId]);
            $sortOrder = (int) $stmt->fetchColumn();

            $stmt = $this->db->prepare(
                "INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$productId, $relativePath, $name, $isPrimary ? 1 : 0, $sortOrder]);

            return true;

        } catch (\Throwable $e) {
            error_log("ProductImageService: Info card generation failed: " . $e->getMessage());
            if (isset($img) && $img) {
                imagedestroy($img);
            }
            return false;
        }
    }

    /**
     * Draw hero-style card (large product name, prominent)
     */
    private function drawHeroCard(\GdImage $img, int $size, string $name, string $brand, string $sku, string $category, int $accent, int $text, int $subtitle, int $dim, ?string $font): void
    {
        // Accent bar at top
        imagefilledrectangle($img, 0, 0, $size, 6, $accent);

        // Decorative corner accent
        $this->drawCornerAccent($img, $size, $accent);

        $y = 120;

        // Category tag
        if ($category) {
            $this->drawText($img, $font, 14, $size / 2, $y, strtoupper($category), $subtitle, true);
            $y += 50;
        }

        // Brand
        if ($brand) {
            $this->drawText($img, $font, 18, $size / 2, $y, strtoupper($brand), $accent, true);
            $y += 55;
        }

        // Product name (large, word-wrapped)
        $nameLines = $this->wordWrap($name, $font ? 28 : 5, $size - 140, $font);
        foreach ($nameLines as $line) {
            $this->drawText($img, $font, 28, $size / 2, $y, $line, $text, true);
            $y += $font ? 42 : 28;
        }

        $y += 30;

        // Decorative line
        $lineWidth = 100;
        imagefilledrectangle($img, ($size - $lineWidth) / 2, $y, ($size + $lineWidth) / 2, $y + 3, $accent);
        $y += 40;

        // SKU
        if ($sku) {
            $this->drawText($img, $font, 13, $size / 2, $y, 'SKU: ' . $sku, $dim, true);
        }
    }

    /**
     * Draw specs-focused card (product name + specifications list)
     */
    private function drawSpecsCard(\GdImage $img, int $size, string $name, string $brand, array $specs, int $accent, int $text, int $subtitle, int $dim, ?string $font): void
    {
        // Left accent bar
        imagefilledrectangle($img, 0, 0, 5, $size, $accent);

        $y = 80;

        // Brand
        if ($brand) {
            $this->drawText($img, $font, 14, 60, $y, strtoupper($brand), $accent, false);
            $y += 45;
        }

        // Product name
        $nameLines = $this->wordWrap($name, $font ? 22 : 5, $size - 120, $font);
        foreach ($nameLines as $line) {
            $this->drawText($img, $font, 22, 60, $y, $line, $text, false);
            $y += $font ? 34 : 22;
        }

        $y += 30;

        // Divider
        imagefilledrectangle($img, 60, $y, $size - 60, $y + 1, $dim);
        $y += 30;

        // Specifications header
        $this->drawText($img, $font, 13, 60, $y, 'SPECIFICATIONS', $accent, false);
        $y += 35;

        // Specs list
        $specCount = 0;
        foreach ($specs as $spec) {
            if ($specCount >= 8) break;
            $specName = $spec['name'] ?? ($spec['spec_name'] ?? '');
            $specValue = $spec['value'] ?? ($spec['spec_value'] ?? '');
            if (!$specName || !$specValue) continue;

            // Spec name
            $this->drawText($img, $font, 12, 60, $y, $specName, $subtitle, false);
            // Spec value (right-aligned area)
            $this->drawText($img, $font, 12, $size - 60, $y, $specValue, $text, false, true);

            $y += $font ? 32 : 22;
            $specCount++;

            // Subtle separator
            if ($specCount < count($specs) && $specCount < 8) {
                $sepColor = imagecolorallocatealpha($img, $subtitle >> 16 & 0xFF, $subtitle >> 8 & 0xFF, $subtitle & 0xFF, 110);
                imageline($img, 60, $y - 8, $size - 60, $y - 8, $dim);
            }
        }

        // If no specs, show a note
        if ($specCount === 0) {
            $this->drawText($img, $font, 14, 60, $y, 'Detailed specifications available on product page', $dim, false);
        }
    }

    /**
     * Draw minimal-style card (clean white with accent color)
     */
    private function drawMinimalCard(\GdImage $img, int $size, string $name, string $brand, string $sku, string $shortDesc, int $accent, int $text, int $subtitle, int $dim, ?string $font): void
    {
        // Bottom accent bar
        imagefilledrectangle($img, 0, $size - 6, $size, $size, $accent);

        $y = 160;

        // Brand
        if ($brand) {
            $this->drawText($img, $font, 16, $size / 2, $y, strtoupper($brand), $accent, true);
            $y += 55;
        }

        // Product name (centered, word-wrapped)
        $nameLines = $this->wordWrap($name, $font ? 24 : 5, $size - 160, $font);
        foreach ($nameLines as $line) {
            $this->drawText($img, $font, 24, $size / 2, $y, $line, $text, true);
            $y += $font ? 38 : 26;
        }

        $y += 25;

        // Short description (first 2 lines)
        if ($shortDesc) {
            $descLines = $this->wordWrap($shortDesc, $font ? 13 : 2, $size - 200, $font);
            $lineCount = 0;
            foreach ($descLines as $line) {
                if ($lineCount >= 3) break;
                $this->drawText($img, $font, 13, $size / 2, $y, $line, $subtitle, true);
                $y += $font ? 24 : 18;
                $lineCount++;
            }
        }

        $y += 20;

        // SKU at bottom
        if ($sku) {
            $this->drawText($img, $font, 11, $size / 2, $y, $sku, $dim, true);
        }
    }

    /**
     * Draw gradient-style card with specs sidebar
     */
    private function drawGradientCard(\GdImage $img, int $size, string $name, string $brand, string $category, array $specs, int $accent, int $text, int $subtitle, int $dim, int $bg, ?string $font): void
    {
        // Gradient effect (simulated with horizontal bars)
        for ($row = 0; $row < $size; $row++) {
            $factor = $row / $size;
            $r = (int)($this->colorComponent($bg, 'r') * (1 - $factor * 0.3) + $this->colorComponent($accent, 'r') * $factor * 0.3);
            $g = (int)($this->colorComponent($bg, 'g') * (1 - $factor * 0.3) + $this->colorComponent($accent, 'g') * $factor * 0.3);
            $b = (int)($this->colorComponent($bg, 'b') * (1 - $factor * 0.3) + $this->colorComponent($accent, 'b') * $factor * 0.3);
            $gradColor = imagecolorallocate($img, max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
            imageline($img, 0, $row, $size, $row, $gradColor);
        }

        // Top accent line
        imagefilledrectangle($img, 0, 0, $size, 4, $accent);

        $y = 100;

        // Category
        if ($category) {
            $this->drawText($img, $font, 12, $size / 2, $y, strtoupper($category), $subtitle, true);
            $y += 40;
        }

        // Brand
        if ($brand) {
            $this->drawText($img, $font, 16, $size / 2, $y, strtoupper($brand), $accent, true);
            $y += 50;
        }

        // Product name
        $nameLines = $this->wordWrap($name, $font ? 26 : 5, $size - 120, $font);
        foreach ($nameLines as $line) {
            $this->drawText($img, $font, 26, $size / 2, $y, $line, $text, true);
            $y += $font ? 40 : 28;
        }

        $y += 40;

        // Key specs (up to 4, centered)
        $specCount = 0;
        foreach ($specs as $spec) {
            if ($specCount >= 4) break;
            $specName = $spec['name'] ?? ($spec['spec_name'] ?? '');
            $specValue = $spec['value'] ?? ($spec['spec_value'] ?? '');
            if (!$specName || !$specValue) continue;

            $specText = $specName . ': ' . $specValue;
            $this->drawText($img, $font, 13, $size / 2, $y, $specText, $subtitle, true);
            $y += $font ? 28 : 20;
            $specCount++;
        }
    }

    /**
     * Draw Pricetag.co.za branding at the bottom of the card
     */
    private function drawBranding(\GdImage $img, int $size, int $accent, int $text, int $subtitle, ?string $font): void
    {
        $brandingY = $size - 50;

        // Small tag icon (diamond shape)
        $tagCx = ($size / 2) - 70;
        $tagCy = $brandingY;
        $tagSize = 8;
        $points = [
            $tagCx, $tagCy - $tagSize,
            $tagCx + $tagSize, $tagCy,
            $tagCx, $tagCy + $tagSize,
            $tagCx - $tagSize, $tagCy,
        ];
        imagefilledpolygon($img, $points, $accent);

        // Brand text
        $this->drawText($img, $font, 12, ($size / 2) + 10, $brandingY, 'Pricetag.co.za', $subtitle, true);
    }

    /**
     * Draw text with TTF font or fallback to GD built-in fonts
     */
    private function drawText(\GdImage $img, ?string $font, int $fontSize, int $x, int $y, string $text, int $color, bool $center, bool $rightAlign = false): void
    {
        if (empty($text)) return;

        if ($font && function_exists('imagettftext')) {
            $bbox = imagettfbbox($fontSize, 0, $font, $text);
            $textWidth = abs($bbox[2] - $bbox[0]);

            if ($center) {
                $x = (int)($x - $textWidth / 2);
            } elseif ($rightAlign) {
                $x = (int)($x - $textWidth);
            }

            imagettftext($img, $fontSize, 0, $x, $y + $fontSize, $color, $font, $text);
        } else {
            // Map TTF font size to GD built-in font (1-5)
            $gdFont = match (true) {
                $fontSize >= 24 => 5,
                $fontSize >= 18 => 4,
                $fontSize >= 14 => 3,
                $fontSize >= 11 => 2,
                default => 1,
            };

            $charWidth = imagefontwidth($gdFont);
            $textWidth = $charWidth * strlen($text);

            if ($center) {
                $x = (int)($x - $textWidth / 2);
            } elseif ($rightAlign) {
                $x = (int)($x - $textWidth);
            }

            imagestring($img, $gdFont, max(0, $x), $y, $text, $color);
        }
    }

    /**
     * Word wrap text to fit within a given width
     */
    private function wordWrap(string $text, int $fontSize, int $maxWidth, ?string $font): array
    {
        if (empty($text)) return [];

        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine ? $currentLine . ' ' . $word : $word;

            if ($font && function_exists('imagettfbbox')) {
                $bbox = imagettfbbox($fontSize, 0, $font, $testLine);
                $lineWidth = abs($bbox[2] - $bbox[0]);
            } else {
                $gdFont = min(5, max(1, $fontSize));
                $lineWidth = imagefontwidth($gdFont) * strlen($testLine);
            }

            if ($lineWidth > $maxWidth && $currentLine) {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }

        if ($currentLine) {
            $lines[] = $currentLine;
        }

        return $lines;
    }

    /**
     * Draw decorative corner accent
     */
    private function drawCornerAccent(\GdImage $img, int $size, int $accent): void
    {
        // Top-right corner lines
        for ($i = 0; $i < 3; $i++) {
            $offset = 30 + ($i * 15);
            imageline($img, $size - $offset, 20, $size - 20, $offset, $accent);
        }

        // Bottom-left corner lines
        for ($i = 0; $i < 3; $i++) {
            $offset = 30 + ($i * 15);
            imageline($img, $offset, $size - 20, 20, $size - $offset, $accent);
        }
    }

    /**
     * Extract color component from a GD color int
     */
    private function colorComponent(int $gdColor, string $component): int
    {
        return match ($component) {
            'r' => ($gdColor >> 16) & 0xFF,
            'g' => ($gdColor >> 8) & 0xFF,
            'b' => $gdColor & 0xFF,
            default => 0,
        };
    }

    /**
     * Find a system TTF font file for text rendering
     */
    private function findSystemFont(): ?string
    {
        $fontPaths = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
            '/usr/share/fonts/TTF/DejaVuSans.ttf',
            '/usr/share/fonts/dejavu-sans-fonts/DejaVuSans.ttf',
            '/usr/share/fonts/noto/NotoSans-Regular.ttf',
            '/usr/share/fonts/truetype/ubuntu/Ubuntu-R.ttf',
            '/usr/share/fonts/truetype/noto/NotoSans-Regular.ttf',
        ];

        foreach ($fontPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Download an image from URL and save it as a product image
     */
    private function downloadAndSaveImage(int $productId, string $url, string $altText = '', bool $isPrimary = false): array
    {
        try {
            // Download the image
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if (!$imageData || $httpCode !== 200 || strlen($imageData) < 1000) {
                return ['success' => false, 'error' => 'Download failed'];
            }

            // Verify it's actually an image
            $tmpFile = tempnam(sys_get_temp_dir(), 'pt_img_');
            file_put_contents($tmpFile, $imageData);

            $imageInfo = @getimagesize($tmpFile);
            if (!$imageInfo) {
                @unlink($tmpFile);
                return ['success' => false, 'error' => 'Not a valid image'];
            }

            // Check minimum dimensions (skip tiny images)
            if ($imageInfo[0] < 200 || $imageInfo[1] < 200) {
                @unlink($tmpFile);
                return ['success' => false, 'error' => 'Image too small'];
            }

            // Process image (resize, crop to square, convert to WebP)
            $filename = $this->generateFilename($productId);
            $relativePath = self::UPLOAD_DIR . '/' . $filename;
            $fullPath = STORAGE_PATH . '/uploads/' . $relativePath;

            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $processed = $this->processImage($tmpFile, $fullPath);
            @unlink($tmpFile);

            if (!$processed) {
                return ['success' => false, 'error' => 'Image processing failed'];
            }

            // Save to database
            if ($isPrimary) {
                $stmt = $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
                $stmt->execute([$productId]);
            }

            $stmt = $this->db->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM product_images WHERE product_id = ?");
            $stmt->execute([$productId]);
            $sortOrder = (int) $stmt->fetchColumn();

            $stmt = $this->db->prepare(
                "INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$productId, $relativePath, $altText, $isPrimary ? 1 : 0, $sortOrder]);

            return ['success' => true, 'path' => $relativePath];

        } catch (\Throwable $e) {
            error_log("ProductImageService: Download failed for {$url}: " . $e->getMessage());
            if (isset($tmpFile) && file_exists($tmpFile)) {
                @unlink($tmpFile);
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
