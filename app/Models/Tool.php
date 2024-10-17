<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'link',
        'description',
        'user_id',
    ];

    public function tags() : BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tag_tool');
    }
}
