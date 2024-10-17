<?php

use App\Models\Tag;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Create tool', function () {
    it('can create a new tool', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'title' => 'New Tool',
            'link' => 'https://example.com',
            'description' => 'A great tool for development',
            'tags' => ['PHP', 'Laravel']
        ];

        $response = $this->postJson('/api/tools', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('tools', [
            'title' => 'New Tool',
            'link' => 'https://example.com',
            'description' => 'A great tool for development',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('tags', ['name' => 'php']);
        $this->assertDatabaseHas('tags', ['name' => 'laravel']);
        $tool = Tool::where('title', 'New Tool')->first();
        $this->assertEquals(2, $tool->tags()->count());
        $response
            ->assertJsonStructure([
                'id',
                'title',
                'link',
                'description',
                'tags',
                'created_at',
                'updated_at',
            ])
            ->assertJsonFragment([
                'title' => 'New Tool',
                'link' => 'https://example.com',
            ]);
    });

    it('cannot create a tool with invalid data', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'description' => 'Missing title and link',
            'tags' => ['PHP', 'Laravel']
        ];

        $response = $this->postJson('/api/tools', $data);
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'link']);
    });

    it('cannot create a tool without being authenticated', function () {
        $data = [
            'title' => 'New Tool',
            'link' => 'https://example.com',
            'description' => 'A great tool for development',
            'tags' => ['PHP', 'Laravel']
        ];

        $response = $this->postJson('/api/tools', $data);
        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    });
});

describe('Get tools', function () {
    it('can list tools', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Tool::
            factory()->count(15)
            ->has(Tag::factory()->count(3))
            ->create([
                'user_id' => $user->id
            ]);

        $response = $this->getJson('/api/tools');
        $response
            ->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'link',
                        'description',
                        'tags',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'first_page_url',
                'from',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to'
            ]);
    });

    it('can filter tools by tag', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $tag1 = Tag::factory()->create(['name' => 'tag1']);
        $tag2 = Tag::factory()->create(['name' => 'tag2']);
        Tool::factory()->count(3)->hasAttached($tag1)->create([
            'user_id' => $user->id
        ]);
        Tool::factory()->count(2)->hasAttached($tag2)->create([
            'user_id' => $user->id
        ]);

        $response = $this->getJson('/api/tools?tag=tag1');
        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'link',
                        'description',
                        'tags',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'first_page_url',
                'from',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to'
            ]);
    });

    it('returns validation error when page is not an integer', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tools?page=invalid');
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['page']);
    });

    it('cannot list tools without being authenticated', function () {
        $response = $this->getJson('/api/tools');
        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    });
});

describe('Get a specific tool', function () {
    it('can retrieve a specific tool', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $tool = Tool::
            factory()
            ->has(Tag::factory()->count(3))
            ->create([
                'user_id' => $user->id
            ]);

        $response = $this->getJson("/api/tools/{$tool->id}");
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'link',
                'description',
                'tags',
                'created_at',
                'updated_at'
            ])->assertJsonFragment([
                    'id' => $tool->id,
                    'title' => $tool->title,
                ]);
    });

    it('cannot retrieve a non-existent tool', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $nonExistentId = 9999;

        $response = $this->getJson("/api/tools/{$nonExistentId}");
        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => 'Tool not found.'
            ]);
    });

    it('cannot retrieve an specific tool if you are not the creator', function () {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $tool = Tool::
            factory()
            ->has(Tag::factory()->count(3))
            ->create([
                'user_id' => $anotherUser->id
            ]);
        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/tools/{$tool->id}");
        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'You do not own this tool.',
            ]);
    });

    it('cannot retrieve a specific tool without being authenticated', function () {
        $someId = 9999;
        $response = $this->getJson("/api/tools/{$someId}");

        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    });
});

describe('Update tool', function () {
    it('can update an existing tool', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $tool = Tool::
            factory()
            ->has(Tag::factory()->count(2))
            ->create([
                'user_id' => $user->id
            ]);

        $updatedData = [
            'title' => 'Updated Tool Title',
            'link' => 'https://updated-example.com',
            'description' => 'Updated description',
            'tags' => ['UpdatedTag1', 'UpdatedTag2'],
        ];

        $response = $this->putJson("/api/tools/{$tool->id}", $updatedData);
        $response->assertStatus(200);

        $this->assertDatabaseHas('tools', [
            'id' => $tool->id,
            'title' => 'Updated Tool Title',
            'link' => 'https://updated-example.com',
            'description' => 'Updated description',
        ]);

        $this->assertDatabaseHas('tags', ['name' => 'updatedtag1']);
        $this->assertDatabaseHas('tags', ['name' => 'updatedtag2']);

        $tool->refresh();
        $this->assertEquals(2, $tool->tags()->count());

        $response
            ->assertJsonStructure([
                'id',
                'title',
                'link',
                'description',
                'tags',
                'created_at',
                'updated_at',
            ])
            ->assertJsonFragment([
                'title' => 'Updated Tool Title',
                'link' => 'https://updated-example.com',
            ]);
    });

    it('cannot update a non-existent tool', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $nonExistentToolId = 9999;
        $updatedData = [
            'title' => 'Nonexistent Tool',
            'link' => 'https://nonexistent.com',
            'description' => 'This tool does not exist',
            'tags' => ['NonexistentTag'],
        ];

        $response = $this->putJson("/api/tools/{$nonExistentToolId}", $updatedData);
        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => 'Tool not found.',
            ]);
    });

    it('cannot update a tool with invalid data', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $tool = Tool::factory()->create(['user_id' => $user->id]);
        $invalidData = [
            'description' => 'Updated description without title and link',
            'tags' => 'Not an array',
        ];

        $response = $this->putJson("/api/tools/{$tool->id}", $invalidData);
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'link', 'tags']);
    });

    it('cannot update an existing tool if you are not the creator', function () {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $tool = Tool::
            factory()
            ->has(Tag::factory()->count(3))
            ->create([
                'user_id' => $anotherUser->id
            ]);
        Sanctum::actingAs($owner);
        $updatedData = [
            'title' => 'Updated Tool Title',
            'link' => 'https://updated-example.com',
            'description' => 'Updated description',
            'tags' => ['UpdatedTag1', 'UpdatedTag2'],
        ];

        $response = $this->putJson("/api/tools/{$tool->id}", $updatedData);
        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'You do not own this tool.',
            ]);
    });

    it('cannot update an existing tool without being authenticated', function () {
        $user = User::factory()->create();
        $tool = Tool::
            factory()
            ->has(Tag::factory()->count(3))
            ->create([
                'user_id' => $user->id
            ]);
        $updatedData = [
            'title' => 'Updated Tool Title',
            'link' => 'https://updated-example.com',
            'description' => 'Updated description',
            'tags' => ['UpdatedTag1', 'UpdatedTag2'],
        ];

        $response = $this->putJson("/api/tools/{$tool->id}", $updatedData);
        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    });
});

describe('Delete tool', function () {
    it('can delete an existing tool', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $tool = Tool::
            factory()
            ->has(Tag::factory()->count(3))
            ->create([
                'user_id' => $user->id
            ]);

        $response = $this->deleteJson("/api/tools/{$tool->id}");
        $response->assertStatus(204);

        $this->assertDatabaseMissing('tools', ['id' => $tool->id]);
    });

    it('cannot delete a non-existent tool', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $nonExistentToolId = 9999;

        $response = $this->deleteJson("/api/tools/{$nonExistentToolId}");

        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => 'Tool not found.',
            ]);
    });

    it('cannot delete an existing tool if you are not the creator', function () {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $tool = Tool::
            factory()
            ->has(Tag::factory()->count(3))
            ->create([
                'user_id' => $anotherUser->id
            ]);
        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/tools/{$tool->id}");
        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'You do not own this tool.',
            ]);
    });

    it('cannot delete an existing tool without being authenticated', function () {
        $user = User::factory()->create();
        $tool = Tool::
            factory()
            ->has(Tag::factory()->count(3))
            ->create([
                'user_id' => $user->id
            ]);

        $response = $this->deleteJson("/api/tools/{$tool->id}");
        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    });
});
