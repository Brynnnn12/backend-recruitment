<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    // Konfigurasi di sini agar mudah diubah
    protected const DISK = 'public';
    protected const DEFAULT_DIR = 'uploads';
    protected const MAX_SIZE_MB = 5; // Sesuai dengan request validation

    /**
     * Upload file generic.
     */
    public function upload(UploadedFile $file, ?string $directory = null): string
    {
        try {
            $this->validateSize($file);

            $directory = $directory ?? self::DEFAULT_DIR;
            $filename  = $this->generateFileName($file);

            $path = $file->storeAs($directory, $filename, self::DISK);

            if (!$path) {
                throw new Exception('Gagal mengupload file.');
            }

            Log::info('File uploaded successfully', [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);

            return $path;
        } catch (Exception $e) {
            Log::error('Failed to upload file', [
                'original_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Upload khusus PDF (contoh: CV).
     */
    public function uploadPdf(UploadedFile $file, ?string $directory = 'cv'): string
    {
        if ($file->getMimeType() !== 'application/pdf') {
            throw new Exception('File harus berupa PDF.');
        }

        return $this->upload($file, $directory);
    }

    /**
     * Hapus file berdasarkan path.
     */
    public function delete(string $path): bool
    {
        try {
            if (empty($path)) {
                return false;
            }

            // Opsional: Trim slash di depan agar aman
            $path = ltrim($path, '/');

            if (Storage::disk(self::DISK)->exists($path)) {
                $deleted = Storage::disk(self::DISK)->delete($path);

                if ($deleted) {
                    Log::info('File deleted successfully', ['path' => $path]);
                }

                return $deleted;
            }

            return true; // Anggap sukses jika file memang tidak ada
        } catch (Exception $e) {
            Log::error('Failed to delete file', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Update file: hapus yang lama, upload yang baru.
     */
    public function updateFile(?string $oldPath, UploadedFile $newFile, ?string $directory = null): string
    {
        // Hapus file lama jika ada
        if ($oldPath) {
            $this->delete($oldPath);
        }

        // Upload file baru
        return $this->upload($newFile, $directory);
    }

    /**
     * Update PDF file.
     */
    public function updatePdf(?string $oldPath, UploadedFile $newFile, ?string $directory = 'cv'): string
    {
        if ($newFile->getMimeType() !== 'application/pdf') {
            throw new Exception('File harus berupa PDF.');
        }

        return $this->updateFile($oldPath, $newFile, $directory);
    }

    /**
     * Generate nama file yang unik dan bersih.
     */
    protected function generateFileName(UploadedFile $file): string
    {
        // Menggunakan timestamp + uuid agar benar-benar unik dan tidak cache
        return time() . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
    }

    /**
     * Validasi ukuran file (Safety check).
     */
    protected function validateSize(UploadedFile $file): void
    {
        // Konversi MB ke Bytes: 3 * 1024 * 1024
        $maxBytes = self::MAX_SIZE_MB * 1024 * 1024;

        if ($file->getSize() > $maxBytes) {
            throw new Exception("Ukuran file tidak boleh lebih dari " . self::MAX_SIZE_MB . "MB.");
        }
    }
}
