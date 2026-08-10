<?php

namespace Tests\Feature\Community;

use App\Models\CommunityChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityChannelApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\SpineSeeder::class);
        $this->seed(\Database\Seeders\CommunityChannelSeeder::class);
    }

    public function test_public_community_links_endpoint_returns_db_backed_channels(): void
    {
        $response = $this->getJson('/api/v1/public/community-links');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['uuid', 'platform', 'channel_type', 'name', 'slug', 'is_official', 'provenance']
                ]
            ]);
    }

    public function test_click_tracking_records_join_link_clicked_and_never_joined(): void
    {
        $channel = CommunityChannel::first();

        $response = $this->postJson('/api/v1/community/click', [
            'channel_uuid' => $channel->uuid,
            'event' => 'join_link_clicked',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.event', 'join_link_clicked');

        $this->assertDatabaseHas('community_channel_clicks', [
            'channel_id' => $channel->id,
            'event' => 'join_link_clicked',
        ]);
    }
}
