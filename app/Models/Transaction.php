<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table = 'transaction';

    protected $fillable = [
        'code', 'date',
    ];

    public function detailTransaction()
    {
        return $this->hasMany(detailTransaction::class);
    }
}
