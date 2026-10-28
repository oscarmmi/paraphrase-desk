<?php

namespace Database\Seeders;

use App\Models\ParaphraseJob;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $replies = [
            'Thank you for contacting us. We have received your return request for order HD-2044 and will send the prepaid label to alex@example.com within 2 business days.',
            'Your order HD-3187 has been shipped and is expected to arrive in 3 days. You can follow the delivery using the tracking link in your email.',
            'We are happy to help with order HD-5102. Please reply to support@example.com and our team will get back to you within 24 hours.',
        ];

        foreach ($replies as $source) {
            ParaphraseJob::query()->firstOrCreate(
                ['source' => $source],
                ['status' => 'sample'],
            );
        }
    }
}
