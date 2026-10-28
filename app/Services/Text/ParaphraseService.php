<?php

namespace App\Services\Text;

use App\Services\Llm\LlmClient;
use Illuminate\Support\Facades\Log;
use Throwable;

class ParaphraseService
{
    public function __construct(
        private readonly TokenKeeper $tokenKeeper,
        private readonly LlmClient $llm,
    ) {}

    /** @return array{rewritten: string, kept_tokens: list<string>, valid: bool, mode: string, notice: ?string} */
    public function paraphrase(string $source): array
    {
        $tokens = $this->tokenKeeper->extract($source);
        $notice = null;

        try {
            $rewritten = $this->llm->rewrite($source, $tokens);
            $mode = $this->llm->name();
        } catch (Throwable) {
            Log::warning('Paraphrase provider unavailable; returning the original reply.');
            $rewritten = trim($source);
            $mode = 'safe fallback';
            $notice = 'The paraphrase provider was unavailable, so the original reply is shown unchanged.';
        }

        $valid = $this->tokenKeeper->keepsAll($rewritten, $tokens);

        if (! $valid) {
            $notice = 'One or more protected tokens changed or went missing. Review the result before sending.';
        }

        return [
            'rewritten' => $rewritten,
            'kept_tokens' => $tokens,
            'valid' => $valid,
            'mode' => $mode,
            'notice' => $notice,
        ];
    }
}
