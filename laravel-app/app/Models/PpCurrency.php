<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpCurrency extends BaseModel
{
    use HasFactory;

    protected $table = 'zp_currencies';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'brand_id',
        'code',
        'symbol',
        'rate',
        'is_default',
    ];

    public function getCreatedDateAttribute()
    {
        return $this->created_at;
    }

    public function getUpdatedDateAttribute()
    {
        return $this->updated_at;
    }
}
