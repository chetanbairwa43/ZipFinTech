<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->float('wallet_balance',10,2);
            $table->string('name',255);
            $table->string('phone',255)->unique();
            $table->string('email')->unique();
            $table->integer('otp');
            $table->string('profile_image',255);
            $table->bigInteger('default_address');
            $table->string('referal_code',255)->unique();
            $table->text('device_token');
            $table->string('device_id',255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('status')->default(0);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
