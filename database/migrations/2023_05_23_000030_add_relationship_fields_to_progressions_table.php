<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToProgressionsTable extends Migration
{
    public function up()
    {
        Schema::table('progressions', function (Blueprint $table) {
            $table->unsignedBigInteger('utilisateur_id')->nullable();
            $table->foreign('utilisateur_id', 'utilisateur_fk_8521427')->references('id')->on('users');
            $table->unsignedBigInteger('lecon_id')->nullable();
            $table->foreign('lecon_id', 'lecon_fk_8521428')->references('id')->on('lecons');
        });
    }
}
