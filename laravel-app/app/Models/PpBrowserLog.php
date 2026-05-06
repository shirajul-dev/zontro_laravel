<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpBrowserLog extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_browser_log';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
