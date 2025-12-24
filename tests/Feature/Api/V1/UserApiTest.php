<?php

use App\Models\User;

use function Pest\Laravel\getJson;

it('returns a list of users through the API', function (): void {
    User::factory()->count(3)->create();

    getJson('/api/v1/users')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                ['id', 'name', 'email', 'email_verified_at'],
            ],
        ])
        ->assertJsonCount(3, 'data');
});
