<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpInvoice extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_invoice';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public function brand()
    {
        return $this->belongsTo(PpBrand::class, 'brand_id', 'brand_id');
    }

    public function items()
    {
        return $this->hasMany(PpInvoiceItem::class, 'invoice_id', 'ref');
    }
}
