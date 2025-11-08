<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_events', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('mortgage_application_id')
                ->constrained()
                ->onDelete('cascade');
            
            $table->string('event_type', 50);
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('description');
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes for audit queries
            $table->index(['mortgage_application_id', 'created_at']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_events');
    }
};
