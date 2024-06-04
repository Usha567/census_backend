<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requestquote', function (Blueprint $table) {
            $table->id();
            $table->string('quote_ref_id');
            $table->unsignedBigInteger('product');
            $table->integer('qty');
            $table->unsignedBigInteger('customerid');
            $table->timestamp('requiredtime');
            $table->string('category');
            $table->string('subcategory');
            $table->string('companyname');
            $table->unsignedBigInteger('supplierid');
            $table->string('unit_of_measurement');
            $table->string('status');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('product')->references('id')->on('product_list')->onDelete('cascade');
            $table->foreign('customerid')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('supplierid')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requestquote');
    }
};
