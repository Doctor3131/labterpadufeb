<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Serves files from storage through authenticated routes
 * instead of exposing them via public symlink.
 * 
 * This prevents unauthenticated users from accessing uploaded
 * documents (booking PDFs, KTM images, statement letters, etc.)
 * by guessing the URL.
 */
class SecureFileController extends Controller
{
    /**
     * Serve a file from the public storage disk through an authenticated route.
     * Supports both old files (public disk) and future files (local/private disk).
     */
    public function show(string $path)
    {
        // Prevent directory traversal attacks
        $path = str_replace(['..', "\0"], '', $path);

        // Try public disk first (where existing files are stored)
        if (Storage::disk('public')->exists($path)) {
            $fullPath = Storage::disk('public')->path($path);
            $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'private, no-cache, no-store',
            ]);
        }

        // Try local/private disk (for future uploads)
        if (Storage::disk('local')->exists($path)) {
            $fullPath = Storage::disk('local')->path($path);
            $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'private, no-cache, no-store',
            ]);
        }

        Log::warning('Secure file not found', ['path' => $path]);
        abort(404, 'File tidak ditemukan.');
    }
}
