<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpGatewayParameter extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_gateways_parameter';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
