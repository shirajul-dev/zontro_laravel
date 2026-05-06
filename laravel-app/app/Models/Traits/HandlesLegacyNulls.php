<?php
declare(strict_types=1);

namespace App\Models\Traits;

/**
 * Trait HandlesLegacyNulls
 * 
 * Handles the conversion of legacy '--' strings to PHP nulls and vice-versa.
 */
trait HandlesLegacyNulls
{
    /**
     * Intercept attribute access to convert legacy '--' strings to null.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (is_string($value) && $value === '--') {
            return null;
        }

        return $value;
    }

    /**
     * Intercept attribute setting to convert null back to '--'
     */
    public function setAttribute($key, $value)
    {
        if (is_null($value)) {
            $value = '--';
        }

        return parent::setAttribute($key, $value);
    }
}
