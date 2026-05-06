<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpGateway extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_gateways';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public function parameters()
    {
        return $this->hasMany(PpGatewayParameter::class, 'gateway_id', 'gateway_id');
    }

    public function brand()
    {
        return $this->belongsTo(PpBrand::class, 'brand_id', 'brand_id');
    }
}
