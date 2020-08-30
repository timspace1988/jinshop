<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrdersAddCouponCodeId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('coupon_code_id')->nullable()->after('paid_at');
            $table->foreign('coupon_code_id')->references('id')->on('coupon_codes')->onDelete('set null');
            //when we delete the coupon being used on a particular order, we should not have that order deleted, instead, we can set coupon id to null on that order 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //dropForeign(['coupon_code_id']) will delete the field 'coupon_code_id''s all foreign key setting, 
            //if we use dropForeign('coupon_code_id'), it will delete this field's  foreign key setting on 'coupon_code_id_foreign', which is its defalut foreign key name  
            $table->dropForeign(['coupon_code_id']);
            $table->dropColumn('coupon_code_id');
        });
    }
}
