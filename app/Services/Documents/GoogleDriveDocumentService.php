<?php

namespace App\Services\Documents;

use App\Models\DriveDocument;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GoogleDriveDocumentService
{
    public function configured(): bool
    {
        $path = $this->credentialsPath();

        return filled(config('services.google_drive.folder_id'))
            && $path !== null
            && is_readable($path);
    }

    public function sync(): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('Google Drive is not configured. Set GOOGLE_DRIVE_FOLDER_ID and GOOGLE_DRIVE_CREDENTIALS_PATH.');
        }

        $folderId = (string) config('services.google_drive.folder_id');
        $drive = $this->drive();
        $pageToken = null;
        $files = [];

        do {
            $result = $drive->files->listFiles([
                'q' => sprintf("'%s' in parents and trashed = false", str_replace("'", "\\'", $folderId)),
                'fields' => 'nextPageToken,files(id,name,mimeType,size,webViewLink,webContentLink,iconLink,thumbnailLink,modifiedTime,md5Checksum,description,createdTime)',
                'orderBy' => 'modifiedTime desc',
                'pageSize' => 1000,
                'pageToken' => $pageToken,
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ]);
            $files = array_merge($files, $result->getFiles());
            $pageToken = $result->getNextPageToken();
        } while ($pageToken);

        $ids = collect($files)->map(fn (DriveFile $file) => $file->getId())->filter()->values();

        DB::transaction(function () use ($files, $ids): void {
            DriveDocument::query()->when(
                $ids->isNotEmpty(),
                fn ($query) => $query->whereNotIn('google_file_id', $ids),
            )->update(['is_active' => false]);

            foreach ($files as $file) {
                DriveDocument::updateOrCreate(
                    ['google_file_id' => $file->getId()],
                    [
                        'name' => $file->getName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize() !== null ? (int) $file->getSize() : null,
                        'web_view_link' => $file->getWebViewLink(),
                        'web_content_link' => $file->getWebContentLink(),
                        'icon_link' => $file->getIconLink(),
                        'thumbnail_link' => $file->getThumbnailLink(),
                        'drive_modified_at' => $file->getModifiedTime() ? Carbon::parse($file->getModifiedTime()) : null,
                        'synced_at' => now(),
                        'is_active' => true,
                        'metadata' => [
                            'md5_checksum' => $file->getMd5Checksum(),
                            'description' => $file->getDescription(),
                            'created_time' => $file->getCreatedTime(),
                        ],
                    ],
                );
            }
        });

        return [
            'synced' => count($files),
            'active' => DriveDocument::where('is_active', true)->count(),
            'synced_at' => now()->toIso8601String(),
        ];
    }

    private function drive(): Drive
    {
        $client = new Client;
        $client->setApplicationName('MkulimaForum Document Management');
        $client->setAuthConfig($this->credentialsPath());
        $client->setScopes([Drive::DRIVE_READONLY]);

        return new Drive($client);
    }

    private function credentialsPath(): ?string
    {
        $configured = config('services.google_drive.credentials_path');
        if (! is_string($configured) || $configured === '') {
            return null;
        }

        return str_starts_with($configured, DIRECTORY_SEPARATOR)
            ? $configured
            : base_path($configured);
    }
}
