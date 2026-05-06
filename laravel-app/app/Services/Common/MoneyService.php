<?php
declare(strict_types=1);

namespace App\Services\Common;

/**
 * MoneyService
 * 
 * High-precision currency operations using bcmath.
 * Replaces legacy procedural functions from pp-functions.php.
 */
class MoneyService
{
    private int $defaultScale = 8;
    private int $displayScale = 2;

    /**
     * Sanitize value to numeric string for bcmath.
     */
    public function sanitize(string|int|float|null $value): string
    {
        if (is_numeric($value)) {
            return (string) $value;
        }
        return "0";
    }

    /**
     * Add two values.
     */
    public function add($a, $b, ?int $scale = null): string
    {
        return bcadd($this->sanitize($a), $this->sanitize($b), $scale ?? $this->defaultScale);
    }

    /**
     * Subtract two values.
     */
    public function sub($a, $b, ?int $scale = null): string
    {
        return bcsub($this->sanitize($a), $this->sanitize($b), $scale ?? $this->defaultScale);
    }

    /**
     * Multiply two values.
     */
    public function mul($a, $b, ?int $scale = null): string
    {
        return bcmul($this->sanitize($a), $this->sanitize($b), $scale ?? $this->defaultScale);
    }

    /**
     * Divide two values.
     */
    public function div($a, $b, ?int $scale = null): string
    {
        $a = $this->sanitize($a);
        $b = $this->sanitize($b);

        if (bccomp($b, '0', $scale ?? $this->defaultScale) === 0) {
            return '0';
        }

        return bcdiv($a, $b, $scale ?? $this->defaultScale);
    }

    /**
     * Round value to specified decimals.
     */
    public function round($amount, int $decimals = 2): string
    {
        $amount = $this->sanitize($amount);
        $factor = bcpow('10', (string) ($decimals + 1));
        $tmp = bcmul($amount, $factor, 0);
        $tmp = bcdiv($tmp, '10', 0);
        return bcdiv($tmp, bcpow('10', (string) $decimals), $decimals);
    }

    /**
     * Convert money string to integer (cents/satoshi).
     */
    public function toInt(string $amount, int $decimals = 2): int
    {
        $multiplier = bcpow("10", (string) $decimals);
        return (int) bcmul($this->sanitize($amount), $multiplier, 0);
    }

    /**
     * Convert integer to money string.
     */
    public function fromInt(int $amount, int $decimals = 2): string
    {
        $divisor = bcpow("10", (string) $decimals);
        return bcdiv((string) $amount, $divisor, $decimals);
    }
    
    /**
     * Compare two values.
     * Returns 0 if equal, 1 if a > b, -1 if a < b.
     */
    public function compare($a, $b, ?int $scale = null): int
    {
        return bccomp($this->sanitize($a), $this->sanitize($b), $scale ?? $this->defaultScale);
    }
}
