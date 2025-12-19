<?php

declare(strict_types=1);

namespace Majesko\FpKit\Functions;

/**
 * Pipes a value through a series of functions, applying each transformation sequentially.
 *
 * Takes an initial value and passes it through each function in order,
 * where the output of each function becomes the input to the next.
 * This enables left-to-right function composition.
 *
 * @param mixed $value The initial value to pipe through the functions
 * @param callable ...$fns Variable number of callables to apply sequentially
 * @return mixed The final transformed value after applying all functions
 */
function pipe(mixed $value, callable ...$fns): mixed
{
    foreach ($fns as $fn) {
        $value = $fn($value);
    }

    return $value;
}
