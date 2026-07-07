<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalDocumentDownloadController extends Controller
{
    public function __invoke(Request $request, Document $document): StreamedResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);
        abort_unless($user->can('download', $document), 403);

        $disk = Storage::disk($document->disk ?? Document::STORAGE_DISK);

        abort_unless($disk->exists($document->file_path), 404);

        return $disk->download(
            $document->file_path,
            $document->original_filename ?? $document->name,
        );
    }
}
