<?php

namespace App\Services\Llm;

class FakeLlmClient implements LlmClient
{
    public function rewrite(string $source, array $protectedTokens): string
    {
        $rewritten = str_ireplace(
            [
                'Thank you for contacting us',
                'Thank you for reaching out',
                'We have received',
                'Your order',
                'will arrive',
                'has been shipped',
                'Please let us know',
                'we are happy to help',
            ],
            [
                'Thanks for getting in touch',
                'Thanks for getting in touch',
                "We've received",
                'The order',
                'is expected to arrive',
                'is on its way',
                'Feel free to let us know',
                "we'd be glad to help",
            ],
            trim($source),
        );

        return $rewritten;
    }

    public function name(): string
    {
        return 'local demo';
    }
}
