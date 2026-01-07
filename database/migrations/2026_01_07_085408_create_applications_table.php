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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            // path PDF (CV + surat)
            $table->string('cv_file');

            $table->enum('status', [
                'applied',
                'reviewed',
                'interview',
                'hired',
                'rejected'
            ])->default('applied');

            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            // Cegah apply job yang sama 2x
            $table->unique(['user_id', 'vacancy_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
