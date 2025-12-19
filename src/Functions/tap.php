<?php

declare(strict_types=1);

namespace Majesko\FpKit\Functions;

/**
 * Executes a side effect on a value and returns the original value unchanged.
 *
 * Useful for debugging, logging, or performing operations that don't affect
 * the value itself. The side effect function receives the value but its return
 * value is ignored.
 *
 * @param mixed $value The value to pass to the side effect and return
 * @param callable $sideEffect A function to execute for its side effects
 * @return mixed The original value, unchanged
 */
function tap(mixed $value, callable $sideEffect): mixed
{
    $sideEffect($value);
    return $value;
}
