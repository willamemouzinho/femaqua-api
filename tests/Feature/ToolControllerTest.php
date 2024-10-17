<?php

use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

describe('Create tool', function () {
    it('can create a new tool', function () {
        // ...
    });

    it('cannot create a tool with invalid data', function () {
        // ...
    });
});

describe('Get tools', function () {
    it('can list all tools', function () {
        // ...
    });

    it('can filter tools by tag', function () {
        // ...
    });
});

describe('Get single tool', function () {
    it('can retrieve a specific tool', function () {
        // ...
    });

    it('returns a 404 for non-existent tools', function () {
        // ...
    });
});

describe('Update tool', function () {
    it('can update an existing tool', function () {
        // ...
    });

    it('cannot update a non-existent tool', function () {
        // ...
    });

    it('cannot update a tool with invalid data', function () {
        // ...
    });
});

describe('Delete tool', function () {
    it('can delete an existing tool', function () {
        // ...
    });

    it('cannot delete a non-existent tool', function () {
        // ...
    });
});

// test('test get tools endpoint', function () {
//     $tools = Tool::factory(3)->create();
//     $response = $this->getJson('/api/tools');

//     $response->assertStatus(200)->assertJsonCount(3)->assertJson(
//         function (AssertableJson $json) use ($tools) {
//             $json->hasAll([
//                 '0.id',
//                 '0.title',
//                 '0.author',
//                 '0.isbn',
//             ]);

//             $json->whereAllType([
//                 '0.id' => 'integer',
//                 '0.title' => 'string',
//                 '0.author' => 'string',
//                 '0.isbn' => 'string',
//             ]);

//             $tool = $tools->first();

//             $json->whereAll([
//                 '0.id' => $tool->id,
//                 '0.title' => $tool->title,
//                 '0.author' => $tool->author,
//                 '0.isbn' => $tool->isbn,
//             ]);
//         }

//     );
// });

// test('test get a single tool endpoint', function () {
//     $tool = Tool::factory(1)->createOne();
//     $response = $this->getJson('/api/tools/' . $tool->id);

//     $response->assertStatus(200)->assertJson(
//         function (AssertableJson $json) use ($tool) {
//             $json->hasAll([
//                 'id',
//                 'title',
//                 'author',
//                 'isbn',
//                 'created_at',
//                 'updated_at',
//             ]);

//             $json->whereAllType([
//                 'id' => 'integer',
//                 'title' => 'string',
//                 'author' => 'string',
//                 'isbn' => 'string',
//             ]);

//             $json->whereAll([
//                 'id' => $tool->id,
//                 'title' => $tool->title,
//                 'author' => $tool->author,
//                 'isbn' => $tool->isbn,
//             ]);
//         }
//     );
// });

// test('test post a single tool endpoint', function () {
//     $tool = Tool::factory(1)->makeOne()->toArray();
//     $response = $this->postJson('/api/tools/', $tool);

//     $response->assertStatus(201)->assertJson(
//         function (AssertableJson $json) use ($tool) {
//             $json->hasAll([
//                 'id',
//                 'title',
//                 'author',
//                 'isbn',
//                 'created_at',
//                 'updated_at',
//             ]);

//             $json->whereAll([
//                 'title' => $tool['title'],
//                 'author' => $tool['author'],
//                 'isbn' => $tool['isbn'],
//             ])->etc();
//         }
//     );
// });

// test('test put a single tool endpoint', function () {
//     Tool::factory(1)->createOne();
//     $tool = [
//         'title' => 'Título atualizado',
//         'author' => 'Autor atualizado',
//         'isbn' => 'ISBN atualizado',
//     ];
//     $response = $this->putJson('/api/tools/1', $tool);

//     $response->assertStatus(200)->assertJson(
//         function (AssertableJson $json) use ($tool) {
//             $json->hasAll([
//                 'id',
//                 'title',
//                 'author',
//                 'isbn',
//                 'created_at',
//                 'updated_at',
//             ]);

//             $json->whereAll([
//                 'title' => $tool['title'],
//                 'author' => $tool['author'],
//                 'isbn' => $tool['isbn'],
//             ])->etc();
//         }
//     );
// });

// test('test delete a single tool endpoint', function () {
//     Tool::factory(1)->createOne();
//     $response = $this->deleteJson('/api/tools/1');

//     $response->assertStatus(204);
// });

// test('test patch a single tool endpoint', function () {
//     Tool::factory(1)->createOne();
//     $tool = [
//         'title' => 'Título atualizado com patch',
//     ];
//     $response = $this->patchJson('/api/tools/1', $tool);
//     $response->assertStatus(200)->assertJson(
//         function (AssertableJson $json) use ($tool) {
//             $json->hasAll([
//                 'id',
//                 'title',
//                 'author',
//                 'isbn',
//                 'created_at',
//                 'updated_at',
//             ]);
//             $json->where(
//                 'title', $tool['title'],
//             );
//         }
//     );
// });

// test('test post a single tool endpoint when invalid data is provided', function () {
//     $response = $this->postJson('/api/tools/', []);

//     $response->assertStatus(422)->assertJson(
//         function (AssertableJson $json) {
//             $json->hasAll([
//                 'message',
//                 'errors',
//             ]);

//             $json->whereAll([
//                 'errors.title.0' => 'The title field is required.',
//                 'errors.author.0' => 'The author field is required.',
//                 'errors.isbn.0' => 'The isbn field is required.',
//             ])->etc();
//         }
//     );
// });
