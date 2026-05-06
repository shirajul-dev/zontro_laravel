<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpPaymentLink extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_payment_link';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
