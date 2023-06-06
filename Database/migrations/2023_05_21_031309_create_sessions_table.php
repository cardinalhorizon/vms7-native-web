<?php

use App\Contracts\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateSessionsTable
 */
class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('smartCARS3Native_Sessions')) {
            Schema::create('smartCARS3Native_Sessions', function (Blueprint $table) {
                $table->foreignId('user_id')->primary();
                $table->string('session_id')->index()->unique();
                $table->integer('expiry');
                $table->timestamps();
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
