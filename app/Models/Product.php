<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'total_bid',
        'expired_at',
        'rating',
        'total_rating',
        'review',
        'short_description',
        'long_description',
        'specification',
        'image_path',
    ];

    public $timestamps = true;


    public function Category(){
        return $this->belongsTo(Product::class);
    }

}
