<?php

declare(strict_types=1);

namespace Majesko\FpKit\Functions;

/**
 * Creates a partially applied function with some arguments pre-bound.
 *
 * Returns a new function that, when called, will invoke the original function
 * with the bound arguments followed by any additional arguments provided at
 * call time. This is useful for creating specialized versions of general functions.
 *
 * @param callable $fn The function to partially apply
 * @param mixed ...$boundArgs Arguments to bind to the function
 * @return callable A new function with the bound arguments pre-applied
 */
function partial(callable $fn, mixed ...$boundArgs): callable
{
    return function (mixed ...$rest) use ($fn, $boundArgs) {
        return $fn(...$boundArgs, ...$rest);
    };
}
