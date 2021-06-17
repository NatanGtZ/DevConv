<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Createalltables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('cpf')->unique();
            $table->string('senha');
        });

        Schema::create('units', function (Blueprint $table){
            $table->id();
            $table->string('name');
            $table->integer('id_owner');
        });

        Schema::create('unitpeoples', function (Blueprint $table){
            $table->id();
            $table->integer('id_unit');
            $table->string('name');
            $table->date('birthdate');
        });

        Schema::create('unitvehicles', function (Blueprint $table){
            $table->id();
            $table->integer('id_unit');
            $table->string('title');
            $table->string('color');
            $table->string('plate');
        });

        Schema::create('unitpets', function (Blueprint $table){
            $table->id();
            $table->integer('id_unit');
            $table->string('name');
            $table->date('race');
        });

        Schema::create('walls', function (Blueprint $table){
            $table->id();
            $table->string('title');
            $table->string('body');
            $table->datetime('datecreated');
        });

        Schema::create('walllikes', function (Blueprint $table){
            $table->id();
            $table->integer('id_wall');
            $table->integer('id_user');
        });

        Schema::create('docs', function (Blueprint $table){
            $table->id();
            $table->string('title');
            $table->string('fileurl');
        });
        
        Schema::create('billets', function (Blueprint $table){
            $table->id();
            $table->integer('id_unit');
            $table->string('title ');
            $table->string('fileurl');
        });

        Schema::create('warnings', function (Blueprint $table){
            $table->id();
            $table->integer('id_unit');
            $table->string('title ');
            $table->string('status')->default('IN_REVIEW'); // IN_REVIEW RESOLVED
            $table-date('datecreated');
        });

      


        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
