<?php

declare(strict_types=1);

namespace Majesko\FpKit\Validation;

/**
 * Creates a valid Validation containing a value.
 *
 * Validation representation:
 *   ['valid' => true,  'value' => mixed]
 *   ['valid' => false, 'errors' => array<int, mixed>]
 *
 * @param mixed $value The value to wrap in a valid Validation
 * @return array<string, mixed>
 */
function valid(mixed $value): array
{
    return ['valid' => true, 'value' => $value];
}

/**
 * Creates an invalid Validation containing errors.
 *
 * @param array<int, mixed> $errors Array of validation errors
 * @return array<string, mixed>
 */
function invalid(array $errors): array
{
    return ['valid' => false, 'errors' => array_values($errors)];
}

/**
 * Checks if a Validation is valid.
 *
 * @param array<string, mixed> $v The Validation to check
 * @return bool True if valid, false if invalid
 */
function isValid(array $v): bool
{
    return ($v['valid'] ?? false) === true;
}

/**
 * Extracts errors from a Validation, or returns empty array if valid.
 *
 * @param array<string, mixed> $v The Validation to extract errors from
 * @return array<int, mixed>
 */
function errors(array $v): array
{
    return isValid($v) ? [] : ($v['errors'] ?? []);
}

/**
 * Maps a function over a valid Validation's value, leaving invalid unchanged.
 *
 * @param array<string, mixed> $v The Validation to map over
 * @param callable $fn The transformation function to apply to the value
 * @return array<string, mixed>
 */
function map(array $v, callable $fn): array
{
    return isValid($v) ? valid($fn($v['value'])) : $v;
}

/**
 * Combine many Validations:
 * - if all valid -> valid([...values...])
 * - if any invalid -> invalid([...all errors...])
 *
 * @param array<int, array<string, mixed>> $validations
 * @return array<string, mixed>
 */
function combine(array $validations): array
{
    $vals = [];
    $errs = [];

    // Unlike Result's bind which short-circuits on first error,
    // combine accumulates ALL errors from invalid validations
    foreach ($validations as $v) {
        if (isValid($v)) {
            $vals[] = $v['value'];
        } else {
            $errs = array_merge($errs, errors($v));
        }
    }

    return $errs ? invalid($errs) : valid($vals);
}

/**
 * Lift a function into Validation context.
 * lift(fn($a,$b)=>..., [$va,$vb]) => Validation
 *
 * @param array<int, array<string, mixed>> $validations
 * @return array<string, mixed>
 */
function lift(callable $fn, array $validations): array
{
    // Combine all validations, then apply the function to the collected values
    // If any validation is invalid, all errors are collected
    return map(combine($validations), fn (array $values) => $fn(...$values));
}

/**
 * Converts a Validation to a Result.
 *
 * Valid becomes Ok, invalid becomes Err with the errors array.
 *
 * @param array<string, mixed> $v The Validation to convert
 * @return array<string, mixed>
 */
function toResult(array $v): array
{
    return isValid($v)
        ? \Majesko\FpKit\Result\ok($v['value'])
        : \Majesko\FpKit\Result\err(errors($v));
}

/**
 * Pattern matches on a Validation, executing the appropriate callback.
 *
 * @param array<string, mixed> $v The Validation to match on
 * @param callable $onValid Function to call if valid, receives the value
 * @param callable $onInvalid Function to call if invalid, receives the errors array
 * @return mixed The result of the executed callback
 */
function matchValidation(array $v, callable $onValid, callable $onInvalid): mixed
{
    return isValid($v)
        ? $onValid($v['value'])
        : $onInvalid(errors($v));
}
