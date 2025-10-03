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
        Schema::table('clients', function (Blueprint $table) {
            // Add new columns for enhanced client management
            $table->string('company_name')->nullable()->after('email');
            $table->string('website')->nullable()->after('phone');
            $table->text('notes')->nullable()->after('address');

            // Enhanced contact information
            $table->string('contact_person')->nullable()->after('name');
            $table->string('secondary_email')->nullable()->after('email');
            $table->string('secondary_phone')->nullable()->after('phone');

            // Business information
            $table->string('business_type')->nullable()->after('tax_number');
            $table->string('industry')->nullable()->after('business_type');
            $table->integer('employee_count')->nullable()->after('industry');

            // Address breakdown for better searching/filtering
            $table->string('street_address')->nullable()->after('address');
            $table->string('city')->nullable()->after('street_address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->string('country')->default('US')->after('postal_code');

            // Financial information
            $table->string('currency')->default('USD')->after('country');
            $table->decimal('credit_limit', 10, 2)->nullable()->after('currency');
            $table->enum('payment_terms', ['net_7', 'net_15', 'net_30', 'net_45', 'net_60', 'due_on_receipt'])->default('net_30')->after('credit_limit');

            // Status and categorization
            $table->enum('status', ['active', 'inactive', 'suspended', 'lead'])->default('active')->after('payment_terms');
            $table->enum('priority', ['low', 'medium', 'high', 'vip'])->default('medium')->after('status');
            $table->string('source')->nullable()->after('priority'); // How client was acquired

            // Timestamps for tracking
            $table->timestamp('last_contacted_at')->nullable()->after('updated_at');
            $table->timestamp('first_contact_at')->nullable()->after('last_contacted_at');

            // Soft deletes
            $table->softDeletes();

            // Indexes for better performance
            $table->index('status');
            $table->index('priority');
            $table->index('city');
            $table->index('country');
            $table->index('industry');
            $table->index('created_at');
            $table->index(['status', 'deleted_at']);
            $table->index('email');
            $table->index('tax_number');
        });

        // Create client contacts table for multiple contacts per client
        Schema::create('client_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('position')->nullable(); // Job title/position
            $table->boolean('is_primary')->default(false);
            $table->boolean('receive_invoices')->default(true);
            $table->boolean('receive_statements')->default(false);
            $table->json('preferences')->nullable(); // Contact preferences
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('client_id');
            $table->index('is_primary');
            $table->index('email');
        });

        // Create client notes table for tracking client interactions
        Schema::create('client_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('note');
            $table->boolean('is_important')->default(false);
            $table->string('type')->default('general'); // general, meeting, call, email, etc.
            $table->json('metadata')->nullable(); // Additional data like meeting duration, etc.
            $table->timestamps();

            $table->index('client_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_notes');
        Schema::dropIfExists('client_contacts');

        Schema::table('clients', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn([
                'company_name',
                'website',
                'notes',
                'contact_person',
                'secondary_email',
                'secondary_phone',
                'business_type',
                'industry',
                'employee_count',
                'street_address',
                'city',
                'state',
                'postal_code',
                'country',
                'currency',
                'credit_limit',
                'payment_terms',
                'status',
                'priority',
                'source',
                'last_contacted_at',
                'first_contact_at'
            ]);

            // Drop indexes
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['city']);
            $table->dropIndex(['country']);
            $table->dropIndex(['industry']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'deleted_at']);
            $table->dropIndex(['email']);
            $table->dropIndex(['tax_number']);

            // Remove soft deletes
            $table->dropSoftDeletes();
        });
    }
};
