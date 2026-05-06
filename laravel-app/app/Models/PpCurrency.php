<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpCurrency extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_currency';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
