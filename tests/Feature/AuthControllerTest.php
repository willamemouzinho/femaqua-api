<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Login', function () {
    it('can login with correct credentials', function () {
        // $tool = Tool::factory(1)->makeOne()->toArray();
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'access_token']);
    });

    it('cannot login with incorrect credentials', function () {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    });
});

describe('Registration', function () {
    it('can register a new user', function () {
        $user = User::factory()->makeOne();

        $response = $this->postJson('/api/auth/register', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'access_token']);
    });

    // it('cannot register a user with an existing email', function () {
    // });
});

describe('Logout', function () {
    it('can logout a user', function () {
    });
});

// ... outros testes ...

it('test guest cannot access protected route', function () {
    $response = $this->getJson('/api/tools');

    $response->assertStatus(401);
});

it('test authenticated user can access protected route', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->getJson('/api/tools');

    $response->assertStatus(200);
});