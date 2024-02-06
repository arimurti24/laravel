<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaction extends Model
{
    use HasFactory;
    protected $table = 'detail_transaction';

    protected $fillable = [
        'id_transaction', 'id_product', 'price','quantity'
    ];


    public function transaction()
    {
        return $this->belongsTo(Transaction::class,'id_transaction','id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
