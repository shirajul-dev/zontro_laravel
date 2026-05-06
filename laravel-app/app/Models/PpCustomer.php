<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpCustomer extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_customer';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
