<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpSmsData extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_sms_data';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
