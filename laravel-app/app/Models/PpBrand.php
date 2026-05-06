<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpBrand extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_brands';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public function transactions()
    {
        return $this->hasMany(PpTransaction::class, 'brand_id', 'brand_id');
    }

    public function invoices()
    {
        return $this->hasMany(PpInvoice::class, 'brand_id', 'brand_id');
    }

    public function paymentLinks()
    {
        return $this->hasMany(PpPaymentLink::class, 'brand_id', 'brand_id');
    }

    public function gateways()
    {
        return $this->hasMany(PpGateway::class, 'brand_id', 'brand_id');
    }
}
