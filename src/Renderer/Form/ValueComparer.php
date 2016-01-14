<?php

namespace Dms\Web\Laravel\Renderer\Form;

/**
 * The value comparer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueComparer
{
    /**
     * Returns whether the values are loosely equals
     *
     * @param mixed $a
     * @param mixed $b
     *
     * @return bool
     */
    public static function areLooselyEqual($a, $b)
    {
        if ($a === null && $b === null) {
            return true;
        }

        if ($a === null || $b === null) {
            return false;
        }

        return $a == $b;
    }
    /**
     * Returns whether the values are loosely equals
     *
     * @param mixed $a
     * @param mixed $b
     *
     * @return bool
     */
    public static function loosleyContains($a, $b)
    {
        if ($a === null && $b === null) {
            return true;
        }

        if ($a === null || $b === null) {
            return false;
        }

        return $a == $b;
    }
}