<?php

declare(strict_types=1);

namespace Majesko\FpKit\Result;

/**
 * Creates a successful Result containing a value.
 *
 * Result representation:
 *   ['ok' => true,  'value' => mixed]
 *   ['ok' => false, 'error' => mixed]
 *
 * @param mixed $value The value to wrap in a successful Result
 * @return array<string, mixed>
 */
function ok(mixed $value): array
{
    return ['ok' => true, 'value' => $value];
}

/**
 * Creates an error Result containing an error value.
 *
 * @param mixed $error The error to wrap in a failed Result
 * @return array<string, mixed>
 */
function err(mixed $error): array
{
    return ['ok' => false, 'error' => $error];
}

/**
 * Checks if a Result is successful (Ok).
 *
 * @param array<string, mixed> $r The Result to check
 * @return bool True if Ok, false if Err
 */
function isOk(array $r): bool
{
    return ($r['ok'] ?? false) === true;
}

/**
 * @param array<string, mixed> $r
 * @return array<string, mixed>
 */
function map(array $r, callable $fn): array
{
    // Short-circuits on error - if Result is err, returns it unchanged
    return isOk($r) ? ok($fn($r['value'])) : $r;
}

/**
 * @param array<string, mixed> $r
 * @return array<string, mixed>
 */
function bind(array $r, callable $fn): array
{
    // $fn: (value) => Result
    return isOk($r) ? $fn($r['value']) : $r;
}

/**
 * Maps a function over an error Result's error value, leaving Ok unchanged.
 *
 * @param array<string, mixed> $r The Result to map over
 * @param callable $fn The transformation function to apply to the error
 * @return array<string, mixed>
 */
function mapError(array $r, callable $fn): array
{
    return isOk($r) ? $r : err($fn($r['error']));
}

/**
 * Unwraps a Result, returning the value if Ok or a default if Err.
 *
 * @param array<string, mixed> $r The Result to unwrap
 * @param mixed $default The default value to return if Result is Err
 * @return mixed The Result's value or the default
 */
function unwrapOr(array $r, mixed $default): mixed
{
    return isOk($r) ? $r['value'] : $default;
}

/**
 * Folds a Result by applying the appropriate callback based on its state.
 *
 * @param array<string, mixed> $r The Result to fold
 * @param callable $onOk Function to call if Ok, receives the value
 * @param callable $onErr Function to call if Err, receives the error
 * @return mixed The result of the executed callback
 */
function fold(array $r, callable $onOk, callable $onErr): mixed
{
    return isOk($r) ? $onOk($r['value']) : $onErr($r['error']);
}

/**
 * Pattern matches on a Result, executing the appropriate callback.
 *
 * Alias for fold().
 *
 * @param array<string, mixed> $r The Result to match on
 * @param callable $onOk Function to call if Ok, receives the value
 * @param callable $onErr Function to call if Err, receives the error
 * @return mixed The result of the executed callback
 */
function matchResult(array $r, callable $onOk, callable $onErr): mixed
{
    return fold($r, $onOk, $onErr);
}
