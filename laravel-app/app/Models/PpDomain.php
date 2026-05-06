<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpDomain extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_domain';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
