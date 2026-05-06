<?php

namespace App\Models;

use App\Models\Traits\HandlesLegacyNulls;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PpAdmin extends Authenticatable
{
    use HasFactory, Notifiable, HandlesLegacyNulls;

    protected $table = 'pp_admin';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'temp_password',
        '2fa_secret',
    ];

    public function getAuthPassword(): string
    {
        return (string) ($this->password ?? '');
    }
}
