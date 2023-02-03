<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookAuthor extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'author_id'];

    protected $hidden = [
        'id',
        'book_id',
        'author_id',
        'created_at',
        'updated_at',
    ];
}
