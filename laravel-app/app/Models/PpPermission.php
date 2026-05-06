<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpPermission extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_permission';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
