<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpApi extends BaseModel
{
    use HasFactory;

    protected $table = 'pp_api';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $casts = [
        'api_scopes' => 'array',
    ];

    public function hasScope(string $scope): bool
    {
        return is_array($this->api_scopes) && in_array($scope, $this->api_scopes, true);
    }
}
