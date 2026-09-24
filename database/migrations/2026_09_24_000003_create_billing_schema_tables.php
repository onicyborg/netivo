<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->text('value');
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->unsignedInteger('speed_mbps');
            $table->decimal('price', 12, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique();
            $table->foreignUuid('service_id');
            $table->string('customer_number')->unique();
            $table->string('phone', 30);
            $table->text('address');
            $table->date('registered_at');
            $table->string('status')->default('aktif')->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->restrictOnDelete();
        });

        Schema::create('payment_methods', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type')->index();
            $table->string('name');
            $table->string('account_number');
            $table->string('account_name');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('bills', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('bill_number')->unique();
            $table->foreignUuid('customer_id');
            $table->foreignUuid('service_id');
            $table->char('period', 7);
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->string('status')->default('belum_bayar')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'period']);
            $table->index(['status', 'due_date']);
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->restrictOnDelete();
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('bill_id');
            $table->foreignUuid('payment_method_id');
            $table->decimal('amount', 12, 2);
            $table->date('paid_date');
            $table->string('sender_name')->nullable();
            $table->text('note')->nullable();
            $table->string('proof_path');
            $table->string('status')->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->foreignUuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('bill_id')->references('id')->on('bills')->restrictOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->restrictOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->restrictOnDelete();
            $table->index('verified_at');
        });

        Schema::create('receipts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_id')->unique();
            $table->string('receipt_number')->unique();
            $table->timestamp('issued_at');
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('payments')->restrictOnDelete();
        });

        Schema::create('service_upgrade_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id');
            $table->foreignUuid('from_service_id');
            $table->foreignUuid('to_service_id');
            $table->string('status')->default('pending')->index();
            $table->char('effective_period', 7)->nullable();
            $table->text('note')->nullable();
            $table->foreignUuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
            $table->foreign('from_service_id')->references('id')->on('services')->restrictOnDelete();
            $table->foreign('to_service_id')->references('id')->on('services')->restrictOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('daily_reports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->date('report_date')->unique();
            $table->string('source')->default('manual');
            $table->string('status')->default('dikirim')->index();
            $table->unsignedInteger('total_confirmed_count')->default(0);
            $table->decimal('total_confirmed_amount', 12, 2)->default(0);
            $table->unsignedInteger('rejected_count')->default(0);
            $table->unsignedInteger('pending_count')->default(0);
            $table->foreignUuid('created_by')->nullable();
            $table->foreignUuid('reviewed_by')->nullable();
            $table->text('revision_note')->nullable();
            $table->timestamp('sent_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->text('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('cron_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('job')->index();
            $table->string('status');
            $table->json('summary')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('system_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable();
            $table->string('table_name');
            $table->uuid('record_id')->nullable();
            $table->string('action');
            $table->string('method', 10);
            $table->text('url');
            $table->string('ip_address', 45)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->index(['table_name', 'record_id']);
            $table->index(['user_id', 'action']);
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('cron_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('daily_reports');
        Schema::dropIfExists('service_upgrade_requests');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('services');
        Schema::dropIfExists('settings');
    }
};
