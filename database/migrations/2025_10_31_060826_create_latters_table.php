<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Cover;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('latters', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Cover::class);
            $table->string('latter_numbers');
            $table->string('latter_matters');
            $table->string('period');
            $table->text('report_content');
            $table->string('signature')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('latters');
    }
};
