<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpPaymentLinkField extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_payment_link_field';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
