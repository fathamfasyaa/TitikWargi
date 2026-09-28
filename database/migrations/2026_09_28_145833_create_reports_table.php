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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('category', 20);
            $table->string('severity', 20);
            $table->text('description')->nullable();

            // POINT SRID 4326. In MySQL 8 the axis order for SRID 4326 is
            // latitude first, then longitude. See App\Models\Report.
            $table->geography('location', subtype: 'point', srid: 4326);

            $table->string('address')->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('status', 30)->default('unrepaired');
            $table->timestamp('hidden_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->spatialIndex('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
