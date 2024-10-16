<?php

use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

test('test get tools endpoint', function () {
    $tools = Tool::factory(3)->create();
    $response = $this->getJson('/api/tools');

    $response->assertStatus(200)->assertJsonCount(3)->assertJson(
        function (AssertableJson $json) use ($tools) {
            $json->hasAll([
                '0.id',
                '0.title',
                '0.author',
                '0.isbn',
            ]);

            $json->whereAllType([
                '0.id' => 'integer',
                '0.title' => 'string',
                '0.author' => 'string',
                '0.isbn' => 'string',
            ]);

            $book = $tools->first();

            $json->whereAll([
                '0.id' => $book->id,
                '0.title' => $book->title,
                '0.author' => $book->author,
                '0.isbn' => $book->isbn,
            ]);
        }

    );
});

test('test get a single book endpoint', function () {
    $book = Tool::factory(1)->createOne();
    $response = $this->getJson('/api/tools/' . $book->id);

    $response->assertStatus(200)->assertJson(
        function (AssertableJson $json) use ($book) {
            $json->hasAll([
                'id',
                'title',
                'author',
                'isbn',
                'created_at',
                'updated_at',
            ]);

            $json->whereAllType([
                'id' => 'integer',
                'title' => 'string',
                'author' => 'string',
                'isbn' => 'string',
            ]);

            $json->whereAll([
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => $book->isbn,
            ]);
        }
    );
});

test('test post a single book endpoint', function () {
    $book = Tool::factory(1)->makeOne()->toArray();
    $response = $this->postJson('/api/tools/', $book);

    $response->assertStatus(201)->assertJson(
        function (AssertableJson $json) use ($book) {
            $json->hasAll([
                'id',
                'title',
                'author',
                'isbn',
                'created_at',
                'updated_at',
            ]);

            $json->whereAll([
                'title' => $book['title'],
                'author' => $book['author'],
                'isbn' => $book['isbn'],
            ])->etc();
        }
    );
});

test('test put a single book endpoint', function () {
    Tool::factory(1)->createOne();
    $book = [
        'title' => 'Título atualizado',
        'author' => 'Autor atualizado',
        'isbn' => 'ISBN atualizado',
    ];
    $response = $this->putJson('/api/tools/1', $book);

    $response->assertStatus(200)->assertJson(
        function (AssertableJson $json) use ($book) {
            $json->hasAll([
                'id',
                'title',
                'author',
                'isbn',
                'created_at',
                'updated_at',
            ]);

            $json->whereAll([
                'title' => $book['title'],
                'author' => $book['author'],
                'isbn' => $book['isbn'],
            ])->etc();
        }
    );
});

test('test delete a single book endpoint', function () {
    Tool::factory(1)->createOne();
    $response = $this->deleteJson('/api/tools/1');

    $response->assertStatus(204);
});

// test('test patch a single book endpoint', function () {
//     Tool::factory(1)->createOne();
//     $book = [
//         'title' => 'Título atualizado com patch',
//     ];
//     $response = $this->patchJson('/api/tools/1', $book);
//     $response->assertStatus(200)->assertJson(
//         function (AssertableJson $json) use ($book) {
//             $json->hasAll([
//                 'id',
//                 'title',
//                 'author',
//                 'isbn',
//                 'created_at',
//                 'updated_at',
//             ]);
//             $json->where(
//                 'title', $book['title'],
//             );
//         }
//     );
// });

test('test post a single book endpoint when invalid data is provided', function () {
    $response = $this->postJson('/api/tools/', []);

    $response->assertStatus(422)->assertJson(
        function (AssertableJson $json) {
            $json->hasAll([
                'message',
                'errors',
            ]);

            $json->whereAll([
                'errors.title.0' => 'The title field is required.',
                'errors.author.0' => 'The author field is required.',
                'errors.isbn.0' => 'The isbn field is required.',
            ])->etc();
        }
    );
});
