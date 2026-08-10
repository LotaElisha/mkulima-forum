<?php

namespace Database\Seeders;

use App\Models\CommunityChannel;
use Illuminate\Database\Seeder;

class CommunityChannelSeeder extends Seeder
{
    public function run(): void
    {
        $channels = [
            [
                'name' => 'Mkulima Forum Official Channel',
                'slug' => 'mkulima-forum-official-channel',
                'platform' => 'whatsapp_channel',
                'channel_type' => 'WHATSAPP_CHANNEL',
                'url' => 'https://whatsapp.com/channel/0029VaMkulimaForumOfficial',
                'description' => [
                    'sw' => 'Pata tahadhari za kilimo, bei za masoko, na habari rasmi za Mkulima Forum.',
                    'en' => 'Receive agricultural alerts, market prices, and official Mkulima Forum announcements.',
                ],
                'icon' => 'radio',
                'is_official' => true,
                'is_featured' => true,
                'is_active' => true,
                'is_alert_channel' => true,
                'sort_order' => 1,
                'provenance' => 'PLATFORM',
            ],
            [
                'name' => 'Wakulima wa Mahindi Tanzania',
                'slug' => 'wakulima-wa-mahindi-tanzania',
                'platform' => 'whatsapp_group',
                'channel_type' => 'WHATSAPP_GROUP',
                'url' => 'https://chat.whatsapp.com/MkulimaMahindiGroupTZ',
                'description' => [
                    'sw' => 'Kikundi cha kujadili kilimo cha mahindi, mbinu bora, na masoko.',
                    'en' => 'Community group for maize farmers to discuss cultivation, pest control, and sales.',
                ],
                'icon' => 'users',
                'is_official' => false,
                'is_featured' => true,
                'is_active' => true,
                'is_alert_channel' => false,
                'sort_order' => 2,
                'provenance' => 'COMMUNITY',
            ],
            [
                'name' => 'Mkulima Forum Support & Helpline',
                'slug' => 'mkulima-forum-support-helpline',
                'platform' => 'whatsapp',
                'channel_type' => 'WHATSAPP_BUSINESS',
                'phone_number' => '+255700000000',
                'url' => 'https://wa.me/255700000000',
                'default_greeting' => [
                    'sw' => 'Habari Mkulima Forum, nahitaji usaidizi kuhusu...',
                    'en' => 'Hello Mkulima Forum, I need assistance regarding...',
                ],
                'description' => [
                    'sw' => 'Zungumza na timu yetu moja kwa moja kwa msaada wa haraka.',
                    'en' => 'Chat directly with our support team for quick assistance.',
                ],
                'icon' => 'message-square',
                'is_official' => true,
                'is_featured' => true,
                'is_active' => true,
                'is_alert_channel' => false,
                'sort_order' => 3,
                'provenance' => 'PLATFORM',
            ],
            [
                'name' => 'Mkulima Forum YouTube Channel',
                'slug' => 'mkulima-forum-youtube',
                'platform' => 'youtube',
                'channel_type' => 'SOCIAL',
                'url' => 'https://youtube.com/@MkulimaForum',
                'description' => [
                    'sw' => 'Tazama video za mafunzo ya kilimo bora na hadithi za wakulima.',
                    'en' => 'Watch agricultural tutorials, disease guides, and inspiring farmer stories.',
                ],
                'icon' => 'video',
                'is_official' => true,
                'is_featured' => false,
                'is_active' => true,
                'is_alert_channel' => false,
                'sort_order' => 4,
                'provenance' => 'PLATFORM',
            ],
        ];

        foreach ($channels as $c) {
            CommunityChannel::firstOrCreate(['slug' => $c['slug']], $c);
        }
    }
}
