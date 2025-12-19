<?php

declare(strict_types=1);

namespace Majesko\FpKit\Functions;

/**
 * Composes multiple functions into a single function with right-to-left evaluation.
 *
 * Returns a new function that, when called with a value, applies the functions
 * from right to left. For example, compose(f, g, h) creates a function that
 * computes f(g(h(x))).
 *
 * @param callable ...$fns Variable number of callables to compose
 * @return callable A new function that applies all the given functions right-to-left
 */
function compose(callable ...$fns): callable
{
    // compose(f, g, h) => f(g(h(x)))
    return function (mixed $value) use ($fns): mixed {
        for ($i = count($fns) - 1; $i >= 0; $i--) {
            $value = ($fns[$i])($value);
        }

        return $value;
    };
}
