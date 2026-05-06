<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HandlesLegacyNulls;
use Illuminate\Database\Eloquent\Model;

/**
 * BaseModel
 * 
 * Provides common logic for all legacy PipraPay models.
 */
abstract class BaseModel extends Model
{
    use HandlesLegacyNulls;

    /**
     * Disable timestamps by default as legacy tables don't use 
     * Laravel's created_at/updated_at convention.
     */
    public $timestamps = false;

    /**
     * Allow mass assignment by default in this transition phase.
     */
    protected $guarded = [];
}
