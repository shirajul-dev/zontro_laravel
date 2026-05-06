<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpAddonParameter extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_addon_parameter';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
