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
    Schema::create('routers', function (Blueprint $table) {
        $table->id();
        $table->string('name');              // friendly name e.g. "Office Router"
        $table->string('ip_address');        // MikroTik IP
        $table->integer('api_port')->default(8728); // MikroTik API port
        $table->string('username');          // MikroTik login
        $table->string('password');          // MikroTik password
        $table->string('group')->nullable(); // optional grouping e.g. "branch-1"
        $table->text('description')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamp('last_seen')->nullable(); // last successful connection
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routers');
    }
};
