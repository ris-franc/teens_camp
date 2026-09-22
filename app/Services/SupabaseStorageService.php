<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SupabaseStorageService
{
    /**
     * Get the configured Supabase Project URL.
     */
    public static function getUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');
        $supabaseUrl = rtrim(config('services.supabase.url', 'https://mhrcuhiocqkpfljyddyo.supabase.co'), '/');
        $bucket = config('services.supabase.bucket', 'camp-media');

        // If running in local environment and local file exists, serve from local asset
        if (app()->environment('local') && file_exists(public_path('storage/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        // Return Supabase public CDN URL
        return "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$cleanPath}";
    }

    /**
     * Upload a file to Supabase Storage and locally as fallback.
     *
     * @param UploadedFile|string $file UploadedFile instance or raw binary content
     * @param string $destinationPath Relative path in bucket (e.g. 'avatars/pic.jpg')
     * @param string|null $mimeType Optional MIME type
     * @return string Stored relative path
     */
    public static function upload(UploadedFile|string $file, string $destinationPath, ?string $mimeType = null): string
    {
        $cleanPath = ltrim($destinationPath, '/');

        // 1. Save local backup copy on public disk
        try {
            if ($file instanceof UploadedFile) {
                Storage::disk('public')->put($cleanPath, file_get_contents($file->getRealPath()));
                $mimeType = $mimeType ?: $file->getMimeType();
            } else {
                Storage::disk('public')->put($cleanPath, $file);
            }
        } catch (\Throwable $e) {
            Log::warning("Local storage save failed for {$cleanPath}: " . $e->getMessage());
        }

        // 2. Upload directly to Supabase Storage
        $supabaseUrl = rtrim(config('services.supabase.url', 'https://mhrcuhiocqkpfljyddyo.supabase.co'), '/');
        $supabaseKey = config('services.supabase.key', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im1ocmN1aGlvY3FrcGZsanlkZHlvIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODk2NzkyMjYsImV4cCI6MjEwNTI1NTIyNn0.2YGMK3sC9JgrOdelJhFdxng-gwYPjTI8k1Gd9mUHv3s');
        $bucket = config('services.supabase.bucket', 'camp-media');

        if ($supabaseUrl && $supabaseKey) {
            try {
                $body = $file instanceof UploadedFile
                    ? file_get_contents($file->getRealPath())
                    : $file;

                $response = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type' => $mimeType ?: 'image/jpeg',
                    'x-upsert' => 'true',
                ])
                ->withBody($body, $mimeType ?: 'image/jpeg')
                ->timeout(15)
                ->post("{$supabaseUrl}/storage/v1/object/{$bucket}/{$cleanPath}");

                if ($response->successful()) {
                    Log::info("Successfully uploaded {$cleanPath} to Supabase Storage bucket {$bucket}");
                } else {
                    Log::error("Supabase Storage upload error ({$response->status()}): " . $response->body());
                }
            } catch (\Throwable $e) {
                Log::error("Supabase Storage upload exception for {$cleanPath}: " . $e->getMessage());
            }
        }

        return $cleanPath;
    }

    /**
     * Delete a file from both local storage and Supabase Storage.
     */
    public static function delete(string $path): bool
    {
        $cleanPath = ltrim($path, '/');

        // 1. Delete local copy
        try {
            if (Storage::disk('public')->exists($cleanPath)) {
                Storage::disk('public')->delete($cleanPath);
            }
        } catch (\Throwable $e) {}

        // 2. Delete from Supabase Storage
        $supabaseUrl = rtrim(config('services.supabase.url', 'https://mhrcuhiocqkpfljyddyo.supabase.co'), '/');
        $supabaseKey = config('services.supabase.key', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im1ocmN1aGlvY3FrcGZsanlkZHlvIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODk2NzkyMjYsImV4cCI6MjEwNTI1NTIyNn0.2YGMK3sC9JgrOdelJhFdxng-gwYPjTI8k1Gd9mUHv3s');
        $bucket = config('services.supabase.bucket', 'camp-media');

        if ($supabaseUrl && $supabaseKey) {
            try {
                Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                ])
                ->timeout(10)
                ->delete("{$supabaseUrl}/storage/v1/object/{$bucket}", [
                    'prefixes' => [$cleanPath],
                ]);
            } catch (\Throwable $e) {
                Log::error("Supabase Storage delete exception for {$cleanPath}: " . $e->getMessage());
            }
        }

        return true;
    }
}
