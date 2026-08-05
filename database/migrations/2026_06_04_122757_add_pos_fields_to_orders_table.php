<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source')->default('online')->after('notes');
            $table->string('payment_method')->nullable()->after('source');
            $table->string('payment_status')->default('pending')->after('payment_method');
            $table->decimal('vat_amount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('vat_rate', 5, 2)->default(18)->after('vat_amount');
            $table->decimal('discount', 12, 2)->default(0)->after('vat_rate');
            $table->string('location')->nullable()->after('discount');
            $table->foreignId('processed_by')->nullable()->constrained('users')->after('location');
            $table->timestamp('suspended_at')->nullable()->after('updated_at');
            $table->text('suspension_reason')->nullable()->after('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'source', 'payment_method', 'payment_status',
                'vat_amount', 'vat_rate', 'discount',
                'location', 'processed_by',
                'suspended_at', 'suspension_reason'
            ]);
        });
    }
};
