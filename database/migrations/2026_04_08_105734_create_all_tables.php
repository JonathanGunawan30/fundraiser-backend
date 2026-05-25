<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->text('avatar_url')->nullable();
            $table->string('status', 20)->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 255)->unique();
            $table->string('password');
            $table->string('role', 30)->default('admin');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::create('oauth_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 50);
            $table->string('provider_id', 255);
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['provider', 'provider_id']);
        });

        Schema::create('campaign_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('icon_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('category_id');
            $table->foreign('category_id')->references('id')->on('campaign_categories');
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('description');
            $table->text('story');
            $table->text('cover_image_url');
            $table->unsignedBigInteger('goal_amount');
            $table->unsignedBigInteger('collected_amount')->default(0);
            $table->unsignedInteger('donor_count')->default(0);
            $table->date('deadline')->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('verified_status', 20)->default('pending');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->foreign('verified_by')->references('id')->on('admins');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('verified_status');
            $table->index('user_id');
        });

        Schema::create('campaign_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->text('image_url');
            $table->integer('order_index')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('campaign_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('content');
            $table->text('image_url')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('campaign_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('tag_id');
            $table->foreign('tag_id')->references('id')->on('tags');
            $table->unique(['campaign_id', 'tag_id']);
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('donation_number', 50)->unique();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->text('message')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index('campaign_id');
            $table->index('user_id');
            $table->index('status');
        });

        Schema::create('donation_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method', 50);
            $table->string('payment_channel', 50);
            $table->string('external_ref', 255)->unique()->nullable();
            $table->unsignedBigInteger('gross_amount');
            $table->unsignedBigInteger('fee_amount')->default(0);
            $table->unsignedBigInteger('net_amount');
            $table->string('status', 20)->default('pending');
            $table->text('payment_url')->nullable();
            $table->text('raw_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('bank_name', 100);
            $table->string('account_number', 50);
            $table->string('account_name', 150);
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->foreign('processed_by')->references('id')->on('admins');
            $table->text('rejection_reason')->nullable();
            $table->text('transfer_proof_url')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255);
            $table->text('image_url');
            $table->text('link_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        Schema::create('banner_placements', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('banner_id');
            $table->foreign('banner_id')->references('id')->on('banners')->cascadeOnDelete();
            $table->string('placement', 50);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 100)->unique();
            $table->text('value');
            $table->string('type', 20)->default('string');
            $table->timestamp('updated_at')->useCurrent();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('question', 500);
            $table->text('answer');
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('banner_placements');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('donation_payments');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('campaign_tags');
        Schema::dropIfExists('campaign_updates');
        Schema::dropIfExists('campaign_images');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('campaign_categories');
        Schema::dropIfExists('oauth_accounts');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('users');
    }
};