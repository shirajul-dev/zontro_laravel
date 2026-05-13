<?php

namespace App\Models;

use App\Models\Traits\HandlesLegacyNulls;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpPlan extends Model
{
    use HasFactory, HandlesLegacyNulls;

    protected $table = 'pp_plans';

    protected $guarded = [];

    protected $casts = [
        'features' => 'json',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Check if the plan has a specific feature enabled
     */
    public function hasFeature(string $feature): bool
    {
        if (!$this->features) {
            return false;
        }

        return isset($this->features[$feature]) && $this->features[$feature] === true;
    }

    /**
     * Get a specific feature value or limit
     */
    public function getFeatureValue(string $feature, $default = null)
    {
        return $this->features[$feature] ?? $default;
    }
}
