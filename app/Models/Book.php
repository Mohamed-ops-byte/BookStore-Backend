<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'publisher',
        'publish_year',
        'pages',
        'category',
        'price',
        'discount_price',
        'description',
        'cover_image',
        'stock',
        'status',
        'rating',
        'reviews_count'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'rating' => 'decimal:2',
        'stock' => 'integer',
        'publish_year' => 'integer',
        'pages' => 'integer',
        'reviews_count' => 'integer',
    ];
}
