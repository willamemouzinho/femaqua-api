<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Registration', function () {
    it('can register a new user', function () {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
        ]);
        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
                'access_token',
            ]);
    });

    it('cannot register a user with an existing email', function () {
        User::factory()->create(['email' => 'john.doe@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The email provided already exists in our records.',
            ]);
    });

    it('cannot register a user with invalid data', function () {
        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson('/api/auth/register', $invalidData);
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    });
});

describe('Login', function () {
    it('can login with correct credentials', function () {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(['user', 'access_token']);
    });

    it('cannot login with incorrect credentials', function () {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);
        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'The provided credentials do not match our records.',
            ]);
    });
});


describe('Logout', function () {
    it('can logout a user', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');
        $response
            ->assertStatus(204);
    });

    it('cannot logout a user without being authenticated', function () {
        $response = $this->postJson('/api/auth/logout');
        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    });
});
