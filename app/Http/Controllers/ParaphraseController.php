<?php

namespace App\Http\Controllers;

use App\Models\ParaphraseJob;
use App\Services\Text\ParaphraseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ParaphraseController extends Controller
{
    public function __invoke(Request $request, ParaphraseService $service): JsonResponse
    {
        $validated = $request->validate([
            'source' => ['required', 'string', 'min:12', 'max:5000'],
        ]);

        $result = $service->paraphrase($validated['source']);

        DB::beginTransaction();

        try {
            $job = ParaphraseJob::create([
                'source' => $validated['source'],
                'status' => $result['valid'] ? 'completed' : 'invalid',
            ]);

            $paraphrase = $job->paraphrases()->create([
                'rewritten' => $result['rewritten'],
                'kept_tokens' => $result['kept_tokens'],
                'valid' => $result['valid'],
            ]);

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        return response()->json([
            'id' => $job->id,
            'paraphrase_id' => $paraphrase->id,
            'source' => $job->source,
            'status' => $job->status,
            ...$result,
        ]);
    }
}
