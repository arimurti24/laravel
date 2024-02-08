<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table = 'transaction';

    protected $fillable = [
        'code', 'date','payment_method','payment_type','status'
    ];

    public function DetailTransaction()
    {
        return $this->hasMany(DetailTransaction::class,'id','id_transaction');
    }
}
