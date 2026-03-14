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
        Schema::create('script_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('script_id')->constrained()->onDelete('cascade');
            $table->foreignId('router_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('output')->nullable();   // response from router
            $table->text('error')->nullable();
            $table->integer('execution_time')->nullable(); // ms
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('script_executions');
    }
};
