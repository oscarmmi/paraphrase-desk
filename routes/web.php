<?php

use App\Http\Controllers\ParaphraseController;
use App\Models\ParaphraseJob;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $samples = ParaphraseJob::query()
        ->where('status', 'sample')
        ->orderBy('id')
        ->get(['id', 'source']);

    return Inertia::render('Welcome', [
        'samples' => $samples,
    ]);
})->name('home');

Route::post('/paraphrases', ParaphraseController::class)->name('paraphrases.store');
