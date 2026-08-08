<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {

            $table->id();

            $table->string('title');

            $table->text('message');

            $table->enum('type',[
                'birthday',
                'bnn',
                'mcu',
                'announcement'
            ]);

            $table->enum('target_role',[
                'all',
                'Admin',
                'Operator',
                'Manpower'
            ])->default('all');


            $table->unsignedBigInteger('reference_id')
                  ->nullable();

            $table->boolean('is_read')
                  ->default(false);


            $table->date('notification_date');

            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};