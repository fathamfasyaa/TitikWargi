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
        Schema::table('report_flags', function (Blueprint $table) {
            // "reason" holds a fixed choice (App\Enums\FlagReason); "note" is optional free text.
            $table->string('reason', 30)->change();
            $table->text('note')->nullable()->after('reason');

            // One user can flag a report only once.
            $table->unique(['report_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_flags', function (Blueprint $table) {
            // The foreign key on report_id needs an index; add it back before dropping the unique one.
            $table->index('report_id');
            $table->dropUnique(['report_id', 'user_id']);
            $table->dropColumn('note');
            $table->string('reason')->change();
        });
    }
};
