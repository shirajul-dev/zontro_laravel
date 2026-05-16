<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZpBrand extends Model
{
    use HasFactory;

    protected $table = 'zp_brands';

    protected $fillable = [
        'admin_id',
        'is_default',
        'brand_id',
        'identify_name',
        'name',
        'logo',
        'favicon',
        'support_email',
        'support_phone',
        'support_website',
        'theme',
        'currency_code',
        'timezone',
        'language',
        'legacy_brand_id',
    ];

    /**
     * Get the owner of the brand.
     */
    public function admin()
    {
        return $this->belongsTo(ZpAdmin::class, 'admin_id');
    }
}
