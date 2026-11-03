<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Usafirishaji: what is being moved and what vehicle the farmer wants,
     * so transporters can quote without a phone call.
     *
     * Pembejeo: the input categories farmers ask for by name. Existing
     * categories (seeds, fertilizers, pesticides, tools, animal feed,
     * irrigation) are left untouched; only missing slugs are added, and only
     * when a tenant exists to own them.
     */
    public function up(): void
    {
        Schema::table('freight_requests', function (Blueprint $table) {
            $table->string('cargo_type', 32)->nullable()->after('cargo_description');
            $table->string('vehicle_type', 32)->nullable()->after('cargo_type');
        });

        $tenantId = DB::table('tenants')->where('country_code', 'tz')->value('id')
            ?? DB::table('tenants')->orderBy('id')->value('id');
        if (! $tenantId) {
            return;
        }

        $categories = [
            ['seeds', 'Mbegu', 'seed', 10],
            ['fertilizers', 'Mbolea', 'flask', 20],
            ['pesticides', 'Viuatilifu', 'spray', 30],
            ['herbicides', 'Viuatilifu vya magugu', 'leaf', 31],
            ['fungicides', 'Dawa za fangasi', 'spray', 32],
            ['insecticides', 'Dawa za wadudu', 'bug', 33],
            ['plant-nutrients', 'Virutubisho vya mimea', 'droplet', 40],
            ['other-inputs', 'Pembejeo nyingine', 'package', 90],
        ];

        foreach ($categories as [$slug, $name, $icon, $order]) {
            if (DB::table('categories')->where('slug', $slug)->exists()) {
                continue;
            }
            DB::table('categories')->insert([
                'tenant_id' => $tenantId,
                'name' => $name,
                'slug' => $slug,
                'icon' => $icon,
                'sort_order' => $order,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('freight_requests', function (Blueprint $table) {
            $table->dropColumn(['cargo_type', 'vehicle_type']);
        });
        // Categories may already carry products; they are not removed.
    }
};
