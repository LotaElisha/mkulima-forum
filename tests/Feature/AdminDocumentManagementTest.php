<?php

namespace Tests\Feature;

use App\Models\DriveDocument;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Documents\GoogleDriveDocumentService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

class AdminDocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Tenant::create(['name' => 'Tanzania', 'country_code' => 'tz', 'currency' => 'TZS']);
        Sanctum::actingAs(User::factory()->role('admin')->create());
    }

    public function test_admin_can_list_synchronized_drive_documents(): void
    {
        DriveDocument::create([
            'google_file_id' => 'drive-file-1',
            'name' => 'Mwongozo wa Mahindi.pdf',
            'mime_type' => 'application/pdf',
            'size' => 2048,
            'web_view_link' => 'https://drive.google.com/file/d/drive-file-1/view',
            'drive_modified_at' => now(),
            'synced_at' => now(),
            'is_active' => true,
        ]);

        $this->mock(GoogleDriveDocumentService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('configured')->once()->andReturn(true);
        });

        $this->getJson('/api/admin/documents')
            ->assertOk()
            ->assertJsonPath('documents.data.0.name', 'Mwongozo wa Mahindi.pdf')
            ->assertJsonPath('integration.configured', true);
    }

    public function test_admin_can_trigger_google_drive_synchronization(): void
    {
        $this->mock(GoogleDriveDocumentService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('sync')->once()->andReturn([
                'synced' => 3,
                'active' => 3,
                'synced_at' => now()->toIso8601String(),
            ]);
        });

        $this->postJson('/api/admin/documents/sync')
            ->assertOk()
            ->assertJsonPath('synced', 3)
            ->assertJsonPath('active', 3);
    }

    public function test_non_admin_cannot_access_document_management(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/documents')->assertForbidden();
    }

    public function test_admin_can_upload_and_remove_the_website_logo(): void
    {
        Storage::fake('public');

        $upload = $this->postJson('/api/admin/settings/landing/logo', [
            'logo' => UploadedFile::fake()->image('mkulima-logo.png', 400, 120),
        ])->assertOk()->assertJsonStructure(['logo_url']);

        $path = str_replace('/storage/', '', parse_url($upload->json('logo_url'), PHP_URL_PATH));
        Storage::disk('public')->assertExists($path);

        $this->deleteJson('/api/admin/settings/landing/logo')->assertOk();
        Storage::disk('public')->assertMissing($path);
    }
}
