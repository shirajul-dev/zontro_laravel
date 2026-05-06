<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpEnv extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_env';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
