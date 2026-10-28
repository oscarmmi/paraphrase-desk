<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paraphrase_jobs', function (Blueprint $table): void {
            $table->id();
            $table->text('source');
            $table->string('status')->default('pending')->index();
            $table->timestamps();
        });

        Schema::create('paraphrases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paraphrase_job_id')->constrained()->cascadeOnDelete();
            $table->text('rewritten');
            $table->json('kept_tokens');
            $table->boolean('valid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paraphrases');
        Schema::dropIfExists('paraphrase_jobs');
    }
};
