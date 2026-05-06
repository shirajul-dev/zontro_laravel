<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpTransaction extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_transaction';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public function brand()
    {
        return $this->belongsTo(PpBrand::class, 'brand_id', 'brand_id');
    }

    public function gateway()
    {
        return $this->belongsTo(PpGateway::class, 'gateway_id', 'gateway_id');
    }
}
