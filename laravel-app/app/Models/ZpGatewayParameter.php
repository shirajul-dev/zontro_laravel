<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZpGatewayParameter extends Model
{
    use HasFactory;

    protected $table = 'zp_gateway_parameters';

    protected $fillable = [
        'brand_id',
        'gateway_id',
        'option_name',
        'value',
    ];

    public function gateway()
    {
        return $this->belongsTo(ZpGateway::class, 'gateway_id', 'gateway_id');
    }
}
