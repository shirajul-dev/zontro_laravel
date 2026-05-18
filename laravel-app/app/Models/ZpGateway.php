<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZpGateway extends Model
{
    use HasFactory;

    protected $table = 'zp_gateways';

    protected $fillable = [
        'gateway_id',
        'brand_id',
        'slug',
        'name',
        'display',
        'logo',
        'currency',
        'min_allow',
        'max_allow',
        'fixed_discount',
        'percentage_discount',
        'fixed_charge',
        'percentage_charge',
        'primary_color',
        'text_color',
        'btn_color',
        'btn_text_color',
        'tab',
        'status',
    ];

    public function parameters()
    {
        return $this->hasMany(ZpGatewayParameter::class, 'gateway_id', 'gateway_id');
    }

    public function brand()
    {
        return $this->belongsTo(ZpBrand::class, 'brand_id', 'brand_id');
    }
}
