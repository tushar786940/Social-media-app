<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    /**
     * Maximum file size in kilobytes.
     */
    protected int $maxSize = 2048; // 2MB

    /**
     * Allowed MIME types.
     */
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Allowed file extensions.
     */
    protected array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Storage disk.
     */
    protected string $disk = 'public';

    /**
     * Storage path for profile photos.
     */
    protected string $profilePhotoPath = 'profile-photos';

    /**
     * Storage path for cover photos.
     */
    protected string $coverPhotoPath = 'cover-photos';

    /**
     * Storage path for post images.
     */
    protected string $postImagePath = 'post-images';

    /**
     * Upload and process a profile photo.
     */
    public function uploadProfilePhoto(UploadedFile $file, ?string $oldPath = null): string
    {
        $this->validateFile($file);

        // Delete old photo if exists
        if ($oldPath) {
            $this->deleteFile($oldPath);
        }

        // Generate new filename
        $filename = $this->generateFilename($file, 'profile');
        $path = $this->profilePhotoPath . '/' . $filename;

        // Store the image
        $this->storeImage($file, $path);

        return $path;
    }

    /**
     * Upload and process a cover photo.
     */
    public function uploadCoverPhoto(UploadedFile $file, ?string $oldPath = null): string
    {
        $this->validateFile($file);

        // Delete old photo if exists
        if ($oldPath) {
            $this->deleteFile($oldPath);
        }

        // Generate new filename
        $filename = $this->generateFilename($file, 'cover');
        $path = $this->coverPhotoPath . '/' . $filename;

        // Store the image
        $this->storeImage($file, $path);

        return $path;
    }

    /**
     * Upload and process a post image.
     */
    public function uploadPostImage(UploadedFile $file, ?string $oldPath = null): string
    {
        $this->validateFile($file);

        // Delete old image if exists
        if ($oldPath) {
            $this->deleteFile($oldPath);
        }

        // Generate new filename
        $filename = $this->generateFilename($file, 'post');
        $path = $this->postImagePath . '/' . $filename;

        // Store the image
        $this->storeImage($file, $path);

        return $path;
    }

    /**
     * Delete a file from storage.
     */
    public function deleteFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        // Check if the path is within the allowed directories
        if (! $this->isValidPath($path)) {
            return;
        }

        Storage::disk($this->disk)->delete($path);
    }

    /**
     * Validate the uploaded file.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > $this->maxSize * 1024) {
            throw new \InvalidArgumentException(
                "The file size must not exceed {$this->maxSize}KB."
            );
        }

        // Check MIME type
        if (! in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException(
                "The file must be one of: " . implode(', ', $this->allowedExtensions)
            );
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $this->allowedExtensions)) {
            throw new \InvalidArgumentException(
                "The file extension must be one of: " . implode(', ', $this->allowedExtensions)
            );
        }

        // Check if the file is actually an image
        if (! $file->isValid() || ! getimagesize($file->getRealPath())) {
            throw new \InvalidArgumentException(
                "The file must be a valid image."
            );
        }
    }

    /**
     * Generate a safe filename.
     */
    protected function generateFilename(UploadedFile $file, string $prefix): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $random = Str::random(10);

        return "{$prefix}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Store the image with basic validation.
     */
    protected function storeImage(UploadedFile $file, string $path): void
    {
        // Ensure directory exists
        $directory = dirname($path);
        Storage::disk($this->disk)->makeDirectory($directory);

        // Store the file as-is
        // In production, you'd want to resize and optimize the image
        Storage::disk($this->disk)->putFileAs(
            $directory,
            $file,
            basename($path)
        );
    }

    /**
     * Check if the path is valid and within allowed directories.
     */
    protected function isValidPath(string $path): bool
    {
        $allowedPaths = [
            $this->profilePhotoPath,
            $this->coverPhotoPath,
            $this->postImagePath,
        ];

        foreach ($allowedPaths as $allowedPath) {
            if (str_starts_with($path, $allowedPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the maximum file size in kilobytes.
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * Get allowed MIME types.
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    /**
     * Get allowed file extensions.
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }
}
