<?php

namespace App\Services\Text;

class TokenKeeper
{
    /** @return list<string> */
    public function extract(string $text): array
    {
        preg_match_all(
            '/HD-\d+|[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}|(?<![\w-])\d+(?![\w-])/u',
            $text,
            $matches,
        );

        return $matches[0];
    }

    /** @param list<string> $expectedTokens */
    public function keepsAll(string $text, array $expectedTokens): bool
    {
        $expected = $expectedTokens;
        $actual = $this->extract($text);
        sort($expected);
        sort($actual);

        return $actual === $expected;
    }
}
