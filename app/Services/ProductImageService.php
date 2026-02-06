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
    // AI IMAGE GENERATION (GD Library)
    // =========================================================================

    /**
     * Font paths for image generation
     */
    private const FONT_REGULAR = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
    private const FONT_BOLD = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';

    /**
     * Brand color palettes [primary, dark, accent]
     */
    private const BRAND_COLORS = [
        'Intel'  => ['primary' => [0, 113, 197], 'dark' => [0, 62, 107],  'accent' => [0, 168, 252]],
        'AMD'    => ['primary' => [237, 28, 36],  'dark' => [130, 15, 20], 'accent' => [255, 90, 96]],
        'NVIDIA' => ['primary' => [118, 185, 0],  'dark' => [50, 90, 0],   'accent' => [150, 220, 30]],
    ];
    private const DEFAULT_BRAND_COLORS = ['primary' => [79, 70, 229], 'dark' => [40, 35, 115], 'accent' => [129, 120, 255]];

    /**
     * Generate AI product images using PHP GD library.
     * Creates 4 professional info-card style images with brand colors and product details.
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

        if (!extension_loaded('gd')) {
            return ['success' => false, 'generated' => 0, 'message' => 'GD extension not available'];
        }

        $fontRegular = file_exists(self::FONT_BOLD) ? self::FONT_REGULAR : '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf';
        if (!file_exists($fontRegular)) {
            return ['success' => false, 'generated' => 0, 'message' => 'Required fonts not found'];
        }

        $needed = 4 - count($existingImages);
        $brand = $productData['brand'] ?? '';
        $colors = self::BRAND_COLORS[$brand] ?? self::DEFAULT_BRAND_COLORS;

        $generators = ['generateHeroImage', 'generateFeaturesImage', 'generateSpecsImage', 'generateBrandCard'];
        $altSuffixes = ['Product Overview', 'Key Features', 'Technical Specifications', 'Available at Pricetag.co.za'];

        $generated = 0;
        $hasPrimary = !empty($existingImages);

        for ($i = 0; $i < min($needed, 4); $i++) {
            try {
                $method = $generators[$i];
                $gdImage = $this->$method($productData, $colors);

                if ($gdImage) {
                    $altText = ($productData['name'] ?? 'Product') . ' - ' . $altSuffixes[$i];
                    $isPrimary = !$hasPrimary && $i === 0;
                    $result = $this->saveGeneratedImage($productId, $gdImage, $altText, $isPrimary);
                    imagedestroy($gdImage);

                    if ($result['success']) {
                        $generated++;
                    }
                }
            } catch (\Throwable $e) {
                logMessage('error', 'ProductImageService: Image generation failed', [
                    'product_id' => $productId,
                    'image_index' => $i,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        logMessage('info', 'ProductImageService: AI images generated', [
            'product_id' => $productId,
            'generated' => $generated,
        ]);

        return [
            'success' => $generated > 0,
            'generated' => $generated,
            'message' => $generated > 0
                ? "Generated {$generated} product image(s)"
                : 'Failed to generate images',
        ];
    }

    /**
     * Save a GD-generated image to storage and database
     */
    private function saveGeneratedImage(int $productId, \GdImage $image, string $altText = '', bool $isPrimary = false): array
    {
        try {
            $filename = $this->generateFilename($productId);
            $relativePath = self::UPLOAD_DIR . '/' . $filename;
            $fullPath = STORAGE_PATH . '/uploads/' . $relativePath;

            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new \RuntimeException('Failed to create upload directory');
                }
            }

            if (!imagewebp($image, $fullPath, self::DEFAULT_WEBP_QUALITY)) {
                throw new \RuntimeException('Failed to save WebP image');
            }

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
            $imageId = (int) $this->db->lastInsertId();

            return ['success' => true, 'image_id' => $imageId, 'path' => $relativePath];
        } catch (\Throwable $e) {
            logMessage('error', 'ProductImageService: Failed to save generated image', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            if (isset($fullPath) && file_exists($fullPath)) {
                @unlink($fullPath);
            }

            return ['success' => false, 'image_id' => null, 'path' => null];
        }
    }

    // =========================================================================
    // IMAGE GENERATORS (4 card styles)
    // =========================================================================

    /**
     * Image 1: Hero product card - bold product name on branded gradient
     */
    private function generateHeroImage(array $data, array $colors): ?\GdImage
    {
        $img = imagecreatetruecolor(1024, 1024);
        if (!$img) return null;
        imagealphablending($img, true);

        $name = $data['name'] ?? 'Product';
        $brand = $data['brand'] ?? '';
        $category = $data['category'] ?? '';
        $sku = $data['sku'] ?? '';
        $fontBold = file_exists(self::FONT_BOLD) ? self::FONT_BOLD : self::FONT_REGULAR;

        // Gradient background: dark → brand → dark
        $this->drawGradient($img, 0, 0, 1023, 380, [15, 15, 25], $colors['dark']);
        $this->drawGradient($img, 0, 380, 1023, 650, $colors['dark'], $colors['primary']);
        $this->drawGradient($img, 0, 650, 1023, 1023, $colors['primary'], [15, 15, 25]);

        // Accent bar at top
        $accent = imagecolorallocate($img, $colors['accent'][0], $colors['accent'][1], $colors['accent'][2]);
        imagefilledrectangle($img, 0, 0, 1023, 6, $accent);

        // Decorative side line
        imagefilledrectangle($img, 80, 180, 83, 360, $accent);

        // Decorative circles (subtle)
        $circleColor = imagecolorallocatealpha($img, $colors['accent'][0], $colors['accent'][1], $colors['accent'][2], 100);
        imagefilledellipse($img, 900, 150, 200, 200, $circleColor);
        imagefilledellipse($img, 850, 870, 160, 160, $circleColor);

        // Brand name
        if ($brand) {
            $brandColor = imagecolorallocate($img, $colors['accent'][0], $colors['accent'][1], $colors['accent'][2]);
            $this->drawCenteredText($img, strtoupper($brand), $fontBold, 38, 120, $brandColor);
        }

        // Category
        if ($category) {
            $catColor = imagecolorallocatealpha($img, 255, 255, 255, 50);
            $this->drawCenteredText($img, strtoupper($category), self::FONT_REGULAR, 18, 175, $catColor);
        }

        // Product name (large, centered, wrapped)
        $white = imagecolorallocate($img, 255, 255, 255);
        $nameLines = $this->wrapText($name, $fontBold, 54, 860);
        $startY = 460 - (count($nameLines) * 72 / 2);
        foreach ($nameLines as $i => $line) {
            $this->drawCenteredText($img, $line, $fontBold, 54, $startY + ($i * 72), $white);
        }

        // Separator line
        $sepY = $startY + count($nameLines) * 72 + 40;
        $sepColor = imagecolorallocatealpha($img, 255, 255, 255, 80);
        imagefilledrectangle($img, 380, $sepY, 644, $sepY + 3, $sepColor);

        // "Premium Quality" tagline
        $tagColor = imagecolorallocatealpha($img, 255, 255, 255, 30);
        $this->drawCenteredText($img, 'PREMIUM QUALITY', self::FONT_REGULAR, 20, $sepY + 50, $tagColor);

        // SKU at bottom
        if ($sku) {
            $skuColor = imagecolorallocatealpha($img, 255, 255, 255, 60);
            $this->drawCenteredText($img, 'SKU: ' . $sku, self::FONT_REGULAR, 16, 940, $skuColor);
        }

        // Pricetag.co.za
        $ptColor = imagecolorallocatealpha($img, 255, 255, 255, 50);
        $this->drawCenteredText($img, 'Pricetag.co.za', self::FONT_REGULAR, 15, 980, $ptColor);

        return $img;
    }

    /**
     * Image 2: Key features card with bullet points
     */
    private function generateFeaturesImage(array $data, array $colors): ?\GdImage
    {
        $img = imagecreatetruecolor(1024, 1024);
        if (!$img) return null;
        imagealphablending($img, true);

        $name = $data['name'] ?? 'Product';
        $brand = $data['brand'] ?? '';
        $fontBold = file_exists(self::FONT_BOLD) ? self::FONT_BOLD : self::FONT_REGULAR;

        // Dark background
        $bg = imagecolorallocate($img, 18, 18, 28);
        imagefilledrectangle($img, 0, 0, 1023, 1023, $bg);

        // Brand accent bar at top
        $primary = imagecolorallocate($img, $colors['primary'][0], $colors['primary'][1], $colors['primary'][2]);
        imagefilledrectangle($img, 0, 0, 1023, 8, $primary);

        // Left accent strip
        $dark = imagecolorallocate($img, $colors['dark'][0], $colors['dark'][1], $colors['dark'][2]);
        imagefilledrectangle($img, 0, 0, 5, 1023, $dark);

        $white = imagecolorallocate($img, 255, 255, 255);
        $lightGray = imagecolorallocate($img, 200, 200, 210);
        $accent = imagecolorallocate($img, $colors['accent'][0], $colors['accent'][1], $colors['accent'][2]);

        // Brand + heading
        if ($brand) {
            imagettftext($img, 16, 0, 80, 70, $accent, self::FONT_REGULAR, strtoupper($brand));
        }
        imagettftext($img, 30, 0, 80, 115, $white, $fontBold, 'KEY FEATURES');
        imagefilledrectangle($img, 80, 138, 360, 141, $primary);

        // Parse bullet points from short_description
        $features = $this->parseFeatures($data['short_description'] ?? '', $data);

        // Draw features
        $y = 200;
        $featureSpacing = min(120, (int)((800 - 200) / max(count($features), 1)));

        foreach (array_slice($features, 0, 6) as $feature) {
            // Bullet dot
            imagefilledellipse($img, 100, $y, 14, 14, $primary);

            // Feature text (wrapped)
            $featureLines = $this->wrapText($feature, self::FONT_REGULAR, 22, 830);
            foreach ($featureLines as $j => $fLine) {
                imagettftext($img, 22, 0, 125, $y + 6 + ($j * 32), $lightGray, self::FONT_REGULAR, $fLine);
            }

            $y += $featureSpacing;
        }

        // Product name at bottom
        $nameColor = imagecolorallocatealpha($img, 255, 255, 255, 60);
        $nameLines = $this->wrapText($name, self::FONT_REGULAR, 15, 860);
        $bottomY = 960;
        foreach ($nameLines as $i => $line) {
            $this->drawCenteredText($img, $line, self::FONT_REGULAR, 15, $bottomY + ($i * 22), $nameColor);
        }

        return $img;
    }

    /**
     * Image 3: Technical specifications grid
     */
    private function generateSpecsImage(array $data, array $colors): ?\GdImage
    {
        $img = imagecreatetruecolor(1024, 1024);
        if (!$img) return null;
        imagealphablending($img, true);

        $fontBold = file_exists(self::FONT_BOLD) ? self::FONT_BOLD : self::FONT_REGULAR;

        // Dark gradient background
        $this->drawGradient($img, 0, 0, 1023, 1023, [20, 20, 35], [10, 10, 20]);

        // Top accent bar
        $primary = imagecolorallocate($img, $colors['primary'][0], $colors['primary'][1], $colors['primary'][2]);
        imagefilledrectangle($img, 0, 0, 1023, 6, $primary);

        $white = imagecolorallocate($img, 255, 255, 255);
        $accent = imagecolorallocate($img, $colors['accent'][0], $colors['accent'][1], $colors['accent'][2]);
        $dimGray = imagecolorallocate($img, 120, 120, 140);

        // Heading
        imagettftext($img, 14, 0, 80, 55, $accent, self::FONT_REGULAR, 'TECHNICAL');
        imagettftext($img, 32, 0, 80, 100, $white, $fontBold, 'SPECIFICATIONS');
        imagefilledrectangle($img, 80, 120, 420, 123, $primary);

        // Parse specifications
        $specPairs = $this->parseSpecifications($data);

        // Draw specs in 2-column grid
        $y = 180;
        $colWidth = 440;
        $rowHeight = 85;
        $col = 0;
        $count = 0;

        foreach (array_slice($specPairs, 0, 10) as $spec) {
            $x = 80 + ($col * $colWidth);
            $currentY = $y + (intdiv($count, 2) * $rowHeight);

            // Row separator
            if ($col === 0 && $count > 1) {
                $lineColor = imagecolorallocatealpha($img, 255, 255, 255, 110);
                imagefilledrectangle($img, 80, $currentY - 12, 944, $currentY - 11, $lineColor);
            }

            // Key (dim label)
            $keyText = strtoupper(substr($spec['key'], 0, 30));
            imagettftext($img, 13, 0, $x, $currentY, $dimGray, self::FONT_REGULAR, $keyText);

            // Value (bright)
            $valText = substr($spec['value'], 0, 35);
            imagettftext($img, 19, 0, $x, $currentY + 30, $white, $fontBold, $valText);

            $col = ($col + 1) % 2;
            $count++;
        }

        // Product name at bottom
        $nameColor = imagecolorallocatealpha($img, 255, 255, 255, 60);
        $nameLines = $this->wrapText($data['name'] ?? '', self::FONT_REGULAR, 15, 860);
        $bottomY = 960;
        foreach ($nameLines as $i => $line) {
            $this->drawCenteredText($img, $line, self::FONT_REGULAR, 15, $bottomY + ($i * 22), $nameColor);
        }

        return $img;
    }

    /**
     * Image 4: Brand trust card with store branding and trust badges
     */
    private function generateBrandCard(array $data, array $colors): ?\GdImage
    {
        $img = imagecreatetruecolor(1024, 1024);
        if (!$img) return null;
        imagealphablending($img, true);

        $fontBold = file_exists(self::FONT_BOLD) ? self::FONT_BOLD : self::FONT_REGULAR;

        // Light background
        $bgLight = imagecolorallocate($img, 248, 248, 252);
        imagefilledrectangle($img, 0, 0, 1023, 1023, $bgLight);

        // Top brand color header
        $primary = imagecolorallocate($img, $colors['primary'][0], $colors['primary'][1], $colors['primary'][2]);
        $this->drawGradient($img, 0, 0, 1023, 140, $colors['dark'], $colors['primary']);

        // Bottom accent bar
        imagefilledrectangle($img, 0, 1014, 1023, 1023, $primary);

        $white = imagecolorallocate($img, 255, 255, 255);
        $darkText = imagecolorallocate($img, 30, 30, 40);
        $grayText = imagecolorallocate($img, 90, 90, 105);
        $primaryText = imagecolorallocate($img, $colors['primary'][0], $colors['primary'][1], $colors['primary'][2]);
        $successGreen = imagecolorallocate($img, 22, 163, 74);

        // Store branding on header bar
        $this->drawCenteredText($img, 'PRICETAG.CO.ZA', $fontBold, 36, 70, $white);
        $this->drawCenteredText($img, 'South Africa\'s Online Store', self::FONT_REGULAR, 16, 110, $white);

        // Brand name
        $brand = $data['brand'] ?? '';
        if ($brand) {
            $this->drawCenteredText($img, strtoupper($brand), $fontBold, 28, 220, $primaryText);
        }

        // Product name
        $nameLines = $this->wrapText($data['name'] ?? 'Product', $fontBold, 34, 820);
        $nameY = 290;
        foreach ($nameLines as $i => $line) {
            $this->drawCenteredText($img, $line, $fontBold, 34, $nameY + ($i * 48), $darkText);
        }

        // Separator
        $sepY = $nameY + count($nameLines) * 48 + 30;
        imagefilledrectangle($img, 380, $sepY, 644, $sepY + 3, $primary);

        // Trust badges
        $badges = [
            'Genuine Product',
            'Free Shipping on Orders Over R500',
            'Full Manufacturer Warranty',
            'Secure Online Payment',
            'Nationwide Delivery',
        ];

        $badgeY = $sepY + 60;
        foreach ($badges as $badge) {
            // Green checkmark
            $checkX = 260;
            imagettftext($img, 22, 0, $checkX, $badgeY, $successGreen, $fontBold, "\xE2\x9C\x93");
            // Badge text
            imagettftext($img, 21, 0, $checkX + 35, $badgeY, $grayText, self::FONT_REGULAR, $badge);
            $badgeY += 52;
        }

        // Category tag at bottom
        $category = $data['category'] ?? '';
        if ($category) {
            $catText = strtoupper($category);
            $bbox = imagettfbbox(13, 0, self::FONT_REGULAR, $catText);
            $catWidth = abs($bbox[2] - $bbox[0]);
            $tagX = (int)((1024 - $catWidth - 40) / 2);
            $this->drawRoundedRect($img, $tagX, 900, $tagX + $catWidth + 40, 932, 14, $primary);
            $this->drawCenteredText($img, $catText, self::FONT_REGULAR, 13, 921, $white);
        }

        // SKU
        $sku = $data['sku'] ?? '';
        if ($sku) {
            $this->drawCenteredText($img, 'SKU: ' . $sku, self::FONT_REGULAR, 13, 968, $grayText);
        }

        return $img;
    }

    // =========================================================================
    // GD DRAWING HELPERS
    // =========================================================================

    /**
     * Draw a vertical gradient between two colors
     */
    private function drawGradient(\GdImage $img, int $x1, int $y1, int $x2, int $y2, array $from, array $to): void
    {
        $height = $y2 - $y1;
        if ($height <= 0) return;

        for ($y = $y1; $y <= $y2; $y++) {
            $ratio = ($y - $y1) / $height;
            $r = (int)($from[0] + ($to[0] - $from[0]) * $ratio);
            $g = (int)($from[1] + ($to[1] - $from[1]) * $ratio);
            $b = (int)($from[2] + ($to[2] - $from[2]) * $ratio);
            $color = imagecolorallocate($img, $r, $g, $b);
            imageline($img, $x1, $y, $x2, $y, $color);
        }
    }

    /**
     * Wrap text to fit within a max pixel width
     */
    private function wrapText(string $text, string $font, float $size, int $maxWidth): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine ? $currentLine . ' ' . $word : $word;
            $bbox = imagettfbbox($size, 0, $font, $testLine);
            $lineWidth = abs($bbox[2] - $bbox[0]);

            if ($lineWidth > $maxWidth && $currentLine !== '') {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return $lines ?: [''];
    }

    /**
     * Draw centered text on a GD image
     */
    private function drawCenteredText(\GdImage $img, string $text, string $font, float $size, int $y, int $color, int $canvasWidth = 1024): void
    {
        $bbox = imagettfbbox($size, 0, $font, $text);
        $textWidth = abs($bbox[2] - $bbox[0]);
        $x = (int)(($canvasWidth - $textWidth) / 2);
        imagettftext($img, $size, 0, max(10, $x), $y, $color, $font, $text);
    }

    /**
     * Draw a rounded rectangle
     */
    private function drawRoundedRect(\GdImage $img, int $x1, int $y1, int $x2, int $y2, int $radius, int $color): void
    {
        imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
        imagefilledellipse($img, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }

    /**
     * Parse bullet-point features from short_description
     */
    private function parseFeatures(string $shortDescription, array $data): array
    {
        $features = [];
        if (!empty($shortDescription)) {
            $lines = preg_split('/[\n\r]+/', $shortDescription);
            foreach ($lines as $line) {
                $line = trim($line);
                $line = ltrim($line, "\xE2\x80\xA2\xC2\xB7- ");
                $line = trim($line);
                if (!empty($line) && strlen($line) > 3) {
                    $features[] = $line;
                }
            }
        }

        // Fallback
        if (empty($features)) {
            $brand = $data['brand'] ?? 'Premium';
            $category = $data['category'] ?? 'component';
            $features = [
                "High-performance {$category} by {$brand}",
                'Premium build quality and reliability',
                'Industry-leading technology',
                'Energy efficient design',
                'Full manufacturer warranty included',
            ];
        }

        return $features;
    }

    /**
     * Parse specifications into key-value pairs from various formats
     */
    private function parseSpecifications(array $data): array
    {
        $specPairs = [];
        $specs = $data['specifications'] ?? [];

        if (!empty($specs) && is_array($specs)) {
            foreach ($specs as $key => $value) {
                if (is_array($value)) {
                    $specPairs[] = [
                        'key' => $value['name'] ?? $value['spec_name'] ?? "Spec",
                        'value' => $value['value'] ?? $value['spec_value'] ?? '',
                    ];
                } else {
                    $specPairs[] = ['key' => (string)$key, 'value' => (string)$value];
                }
            }
        }

        // Fallback: basic product info
        if (empty($specPairs)) {
            $specPairs = [
                ['key' => 'Brand', 'value' => $data['brand'] ?? 'N/A'],
                ['key' => 'Category', 'value' => $data['category'] ?? 'N/A'],
                ['key' => 'SKU', 'value' => $data['sku'] ?? 'N/A'],
                ['key' => 'Condition', 'value' => 'Brand New'],
                ['key' => 'Warranty', 'value' => 'Manufacturer Warranty'],
                ['key' => 'Availability', 'value' => 'In Stock'],
            ];
        }

        return $specPairs;
    }
}
