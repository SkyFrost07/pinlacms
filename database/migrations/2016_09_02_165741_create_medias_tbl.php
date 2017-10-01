<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediasTbl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('medias')) {
            return;
        }
        Schema::create('medias', function(Blueprint $table){
            $table->increments('id');
            $table->integer('thumb_id')->unsigned()->nullable();
            $table->string('thumb_type', 30)->default('image');
            $table->integer('author_id')->unsigned()->nullable();
            $table->integer('slider_id')->unsigned()->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('media_type', 30)->default('inherit');
            $table->string('media_type_id')->nullable();
            $table->string('target')->nullable();
            $table->integer('views')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('slider_id')->references('id')->on('taxs')->onDelete('set null');
        });
        
        Schema::create('media_desc', function(Blueprint $table){
            $table->integer('media_id')->unsigned();
            $table->string('lang_code', 2);
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->primary(['media_id', 'lang_code']);
            $table->foreign('media_id')->references('id')->on('medias')->onDelete('cascade');
            $table->foreign('lang_code')->references('code')->on('langs')->onDelete('cascade');
        });
        
        Schema::create('media_tax', function(Blueprint $table){
            $table->integer('media_id')->unsigned();
            $table->integer('tax_id')->unsigned();
            $table->primary(['media_id', 'tax_id']);
            $table->foreign('media_id')->references('id')->on('medias')->onDelete('cascade');
            $table->foreign('tax_id')->references('id')->on('taxs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medias');
        Schema::dropIfExists('media_desc');
        Schema::dropIfExists('media_tax');
    }
}
