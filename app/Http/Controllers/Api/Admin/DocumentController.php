<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriveDocument;
use App\Services\Documents\GoogleDriveDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class DocumentController extends Controller
{
    public function __construct(private readonly GoogleDriveDocumentService $driveDocuments) {}

    public function index(Request $request): JsonResponse
    {
        $query = DriveDocument::query()->where('is_active', true)->latest('drive_modified_at');

        if ($search = trim((string) $request->query('search'))) {
            $query->where('name', 'like', '%'.$search.'%');
        }
        if ($mimeType = trim((string) $request->query('mime_type'))) {
            $query->where('mime_type', $mimeType);
        }

        return response()->json([
            'documents' => $query->paginate(30),
            'integration' => [
                'configured' => $this->driveDocuments->configured(),
                'folder_id' => config('services.google_drive.folder_id') ? 'configured' : null,
                'last_synced_at' => DriveDocument::max('synced_at'),
            ],
        ]);
    }

    public function sync(): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Google Drive documents synchronized successfully.',
                ...$this->driveDocuments->sync(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => app()->isProduction()
                    ? 'Unable to synchronize Google Drive. Check the integration configuration and folder permissions.'
                    : $exception->getMessage(),
            ], 422);
        }
    }

    public function open(DriveDocument $document): RedirectResponse
    {
        abort_unless($document->is_active && filled($document->web_view_link), 404);

        return redirect()->away($document->web_view_link);
    }
}
