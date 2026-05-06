<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpBalanceVerification extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_balance_verification';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
