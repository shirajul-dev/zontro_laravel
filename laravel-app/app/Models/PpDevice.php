<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpDevice extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_device';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
