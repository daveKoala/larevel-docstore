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
        Schema::create('tenant_email_configs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_slug')->unique();
            $table->string('logo_url')->nullable();
            $table->string('primary_color')->default('#2563eb');
            $table->string('header_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('support_email')->nullable();
            $table->json('cc_emails')->nullable();
            $table->timestamps();

            $table->index('tenant_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_email_configs');
    }
};
