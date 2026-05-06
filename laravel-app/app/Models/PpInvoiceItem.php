<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpInvoiceItem extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_invoice_items';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public function invoice()
    {
        return $this->belongsTo(PpInvoice::class, 'invoice_id', 'ref');
    }
}
