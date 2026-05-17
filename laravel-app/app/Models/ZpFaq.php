<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZpFaq extends Model
{
    use HasFactory;

    protected $table = 'zp_faqs';

    protected $fillable = [
        'brand_id',
        'title',
        'description',
        'status',
    ];

    /**
     * Get the brand that owns the FAQ.
     */
    public function brand()
    {
        return $this->belongsTo(ZpBrand::class, 'brand_id', 'brand_id');
    }
}
