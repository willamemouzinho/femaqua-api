<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'link',
        'description',
        'tags',
        'user_id',
    ];

    protected function casts() : array
    {
        return [
            'tags' => 'array',
        ];
    }
}
