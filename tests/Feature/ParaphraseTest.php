<?php

use App\Models\Paraphrase;
use App\Models\ParaphraseJob;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;
use App\Services\Text\TokenKeeper;
use Database\Seeders\DatabaseSeeder;

it('extracts order ids, email addresses, and standalone integers', function () {
    $tokens = app(TokenKeeper::class)->extract('Order HD-2044 ships in 2 days; write to alex@example.com.');

    expect($tokens)->toBe(['HD-2044', '2', 'alex@example.com']);
});

it('keeps HD order ids in the paraphrase and persists the token audit', function () {
    $fake = new FakeLlmClient;
    app()->instance(LlmClient::class, $fake);

    $response = $this->postJson(route('paraphrases.store'), [
        'source' => 'Thank you for contacting us. We have received order HD-2044 and will reply in 2 days to alex@example.com.',
    ]);

    $response->assertOk()
        ->assertJsonPath('valid', true)
        ->assertJsonPath('kept_tokens', ['HD-2044', '2', 'alex@example.com'])
        ->assertJsonPath('mode', 'local demo');

    expect(ParaphraseJob::query()->count())->toBe(1)
        ->and(Paraphrase::query()->first()->rewritten)->toContain('HD-2044');
});

it('accepts a guest post without an API key', function () {
    expect(app(LlmClient::class))->toBeInstanceOf(FakeLlmClient::class);

    $this->postJson(route('paraphrases.store'), [
        'source' => 'Your order HD-2044 will arrive in 2 days. Contact alex@example.com for help.',
    ])->assertOk();
});

it('flags an llm response that drops a protected token', function () {
    $client = new class implements LlmClient
    {
        public function rewrite(string $source, array $protectedTokens): string
        {
            return 'Your order will arrive soon.';
        }

        public function name(): string
        {
            return 'broken test provider';
        }
    };
    app()->instance(LlmClient::class, $client);

    $this->postJson(route('paraphrases.store'), [
        'source' => 'Your order HD-2044 will arrive in 2 days.',
    ])->assertOk()
        ->assertJsonPath('valid', false)
        ->assertJsonPath('status', 'invalid');
});

it('returns the unchanged reply when the provider throws', function () {
    $client = new class implements LlmClient
    {
        public function rewrite(string $source, array $protectedTokens): string
        {
            throw new RuntimeException('Provider timeout');
        }

        public function name(): string
        {
            return 'unavailable provider';
        }
    };
    app()->instance(LlmClient::class, $client);

    $source = 'Your order HD-2044 will arrive in 2 days.';
    $this->postJson(route('paraphrases.store'), ['source' => $source])
        ->assertOk()
        ->assertJsonPath('rewritten', $source)
        ->assertJsonPath('valid', true)
        ->assertJsonPath('mode', 'safe fallback');
});

it('seeds three fictional support replies with order ids', function () {
    $this->seed(DatabaseSeeder::class);

    $samples = ParaphraseJob::query()->where('status', 'sample')->get();

    expect($samples)->toHaveCount(3)
        ->and($samples->every(fn (ParaphraseJob $sample): bool => str_contains($sample->source, 'HD-')))->toBeTrue();
});

it('renders the desk for guests', function () {
    $this->get(route('home'))->assertOk();
});
