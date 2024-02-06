<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'product';

    protected $fillable = [
        'code', 'name', 'price','id_category','description'
    ];


    public function category()
    {
        return $this->belongsTo(ProductCategory::class,'id_category','id');
    }
}
