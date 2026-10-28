<?php

namespace App\Services\Llm;

interface LlmClient
{
    /** @param list<string> $protectedTokens */
    public function rewrite(string $source, array $protectedTokens): string;

    public function name(): string;
}
