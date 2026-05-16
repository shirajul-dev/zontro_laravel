<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZpCurrency extends Model
{
    use HasFactory;

    protected $table = 'zp_currencies';

    protected $fillable = [
        'brand_id',
        'code',
        'symbol',
        'rate',
        'is_default',
    ];

    /**
     * Get the brand that owns the currency.
     */
    public function brand()
    {
        return $this->belongsTo(ZpBrand::class, 'brand_id', 'brand_id');
    }
}
