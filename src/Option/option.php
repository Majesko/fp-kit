<?php

declare(strict_types=1);

namespace Majesko\FpKit\Option;

/**
 * Creates an Option containing a value.
 *
 * Option representation:
 *   ['some' => true,  'value' => mixed]
 *   ['some' => false]
 *
 * @param mixed $value The value to wrap in a Some Option
 * @return array<string, mixed>
 */
function some(mixed $value): array
{
    return ['some' => true, 'value' => $value];
}

/**
 * Creates an empty Option representing no value.
 *
 * @return array<string, mixed>
 */
function none(): array
{
    return ['some' => false];
}

/**
 * Checks if an Option contains a value (is Some).
 *
 * @param array<string, mixed> $o The Option to check
 * @return bool True if Some, false if None
 */
function isSome(array $o): bool
{
    return ($o['some'] ?? false) === true;
}

/**
 * @param array<string, mixed> $o
 * @return array<string, mixed>
 */
function map(array $o, callable $fn): array
{
    // Short-circuits on None - if Option is none, returns it unchanged
    return isSome($o) ? some($fn($o['value'])) : $o;
}

/**
 * @param array<string, mixed> $o
 * @return array<string, mixed>
 */
function bind(array $o, callable $fn): array
{
    // $fn: (value) => Option
    return isSome($o) ? $fn($o['value']) : $o;
}

/**
 * Unwraps an Option, returning the value if Some or a default if None.
 *
 * @param array<string, mixed> $o The Option to unwrap
 * @param mixed $default The default value to return if Option is None
 * @return mixed The Option's value or the default
 */
function unwrapOr(array $o, mixed $default): mixed
{
    return isSome($o) ? $o['value'] : $default;
}

/**
 * Creates an Option from an array key lookup.
 *
 * Returns Some if the key exists in the array, None otherwise.
 *
 * @param array<mixed> $a The array to look up in
 * @param string|int $key The key to look up
 * @return array<string, mixed>
 */
function fromArray(array $a, string|int $key): array
{
    return array_key_exists($key, $a) ? some($a[$key]) : none();
}

/**
 * Creates an Option from a potentially null value.
 *
 * Returns None if the value is null, Some otherwise.
 *
 * @param mixed $v The value to convert to an Option
 * @return array<string, mixed>
 */
function fromNullable(mixed $v): array
{
    return $v === null ? none() : some($v);
}

/**
 * Converts an Option to a Result.
 *
 * Some becomes Ok, None becomes Err with the provided error value.
 *
 * @param array<string, mixed> $o The Option to convert
 * @param mixed $error The error value to use if Option is None
 * @return array<string, mixed>
 */
function toResult(array $o, mixed $error): array
{
    return isSome($o)
        ? \Majesko\FpKit\Result\ok($o['value'])
        : \Majesko\FpKit\Result\err($error);
}

/**
 * Pattern matches on an Option, executing the appropriate callback.
 *
 * @param array<string, mixed> $o The Option to match on
 * @param callable $onSome Function to call if Some, receives the value
 * @param callable $onNone Function to call if None, receives no arguments
 * @return mixed The result of the executed callback
 */
function matchOption(array $o, callable $onSome, callable $onNone): mixed
{
    return isSome($o)
        ? $onSome($o['value'])
        : $onNone();
}
