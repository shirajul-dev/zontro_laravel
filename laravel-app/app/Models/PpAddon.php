<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpAddon extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_addon';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
