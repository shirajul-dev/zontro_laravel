<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpFaq extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_faq';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
