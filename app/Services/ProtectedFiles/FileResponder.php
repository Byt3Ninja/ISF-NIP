<?php

namespace App\Services\ProtectedFiles;

use App\ProtectedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds the HTTP response for a confidential file once access was granted.
 *
 * The content type never comes from the stored bytes or from the client: a file
 * is shown in the browser only if it is a PDF or raster image by extension AND
 * its recorded (sniffed) type agrees; anything else is sent as a download with a
 * generic type, so nothing uploaded can run as a page in this site's origin.
 */
class FileResponder
{
    public function respond(ProtectedFile $file, bool $forceDownload = false): Response
    {
        $disk = Storage::disk($file->disk);
        if (!$disk->exists($file->path)) {
            abort(404);
        }

        $extension = $file->extension();
        $inlineType = config('protected_files.inline_types')[$extension] ?? null;
        $inline = !$forceDownload && $inlineType !== null && $file->mime === $inlineType;

        $headers = [
            'Content-Type' => $inline ? $inlineType : 'application/octet-stream',
            'Content-Disposition' => $this->disposition($file, $inline, $extension),
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'no-referrer',
        ];

        if (config('protected_files.delivery') === 'x-accel') {
            // nginx serves the bytes from an `internal` location that aliases the private root.
            $relative = ltrim(Str::after($disk->path($file->path), rtrim(config('protected_files.root'), '/')), '/');
            $response = response('', 200, $headers + [
                'X-Accel-Redirect' => rtrim(config('protected_files.accel_location'), '/') . '/' . implode('/', array_map('rawurlencode', explode('/', $relative))),
            ]);
        } else {
            // $public = false; BinaryFileResponse also answers Range requests (PDF viewers need them).
            $response = new BinaryFileResponse($disk->path($file->path), 200, $headers, false);
        }

        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    /** Content-Disposition with the original name, forced to the validated extension; non-ASCII names are RFC 5987 encoded. */
    protected function disposition(ProtectedFile $file, bool $inline, string $extension): string
    {
        $name = $file->original_name ?: basename($file->path);
        $base = pathinfo($name, PATHINFO_FILENAME) ?: 'file';
        $name = preg_replace('#[/\\\\%\x00-\x1F\x7F]+#u', '_', $base) . ($extension !== '' ? '.' . $extension : '');

        $fallback = preg_replace('/[^A-Za-z0-9._-]+/', '_', Str::ascii($name));
        if (trim($fallback, '._') === '' || $fallback === '.' . $extension) {
            $fallback = 'file' . ($extension !== '' ? '.' . $extension : '');
        }

        return HeaderUtils::makeDisposition($inline ? HeaderUtils::DISPOSITION_INLINE : HeaderUtils::DISPOSITION_ATTACHMENT, $name, $fallback);
    }
}
