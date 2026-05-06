<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpWebhookLog extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_webhook_log';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
