<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('domain_checks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bulk_check_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('input');
            $table->string('domain')->nullable();

            $table->enum('status', [
                'queued',
                'checking',
                'completed',
                'failed',
            ])->default('queued');

            $table->enum('blacklist_status', [
                'clean',
                'listed',
                'unknown',
            ])->nullable();

            $table->enum('mail_provider', [
                'google',
                'microsoft',
                'other',
                'not_detected',
            ])->nullable();

            $table->json('blacklists')->nullable();
            $table->json('dns_records')->nullable();

            $table->json('mx_records')->nullable();
            $table->json('spf')->nullable();
            $table->json('dmarc')->nullable();
            $table->json('dkim')->nullable();
            $table->json('nameservers')->nullable();

            $table->json('detection_evidence')->nullable();
            $table->text('error')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['bulk_check_id', 'status']);
            $table->index('domain');
            $table->index('mail_provider');
            $table->index('blacklist_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_checks');
    }
};
