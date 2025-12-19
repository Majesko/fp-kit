# majesko/fp-kit

A lightweight functional programming toolkit for PHP 8.2+. This library provides immutable data types, function composition utilities, and array helpers that enable a clean functional programming style in PHP.

## Features

- **Monadic Types**: `Result`, `Option`, and `Validation` for safe, composable error handling
- **Function Composition**: `pipe`, `compose`, `tap`, and `partial` for building complex operations
- **Array Utilities**: Functional helpers like `map`, `filter`, `reduce`, `groupBy`, and `indexBy`
- **Zero Dependencies**: Pure PHP with no external dependencies
- **Fully Typed**: Strict types throughout with PHPStan level 7 compliance
- **Comprehensive Tests**: 79 tests with extensive edge case coverage

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Function Composition](#function-composition)
  - [pipe](#pipe)
  - [compose](#compose)
  - [tap](#tap)
  - [partial](#partial)
- [Array Functions](#array-functions)
- [Result Type](#result-type)
- [Option Type](#option-type)
- [Validation Type](#validation-type)
- [API Reference](#api-reference)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Installation

```bash
composer require majesko/fp-kit
```

Requires PHP 8.2 or higher.

## Quick Start

```php
<?php

declare(strict_types=1);

use function Majesko\FpKit\Functions\pipe;
use function Majesko\FpKit\Result\{ok, err, map as rmap, bind as rbind};
use function Majesko\FpKit\Option\{some, none, map as omap};

// Function composition with pipe
$result = pipe(
    fn($x) => $x * 2,
    fn($x) => $x + 10,
    fn($x) => $x / 2
)(5); // Result: 10

// Safe error handling with Result
function divide(float $a, float $b): array {
    return $b === 0.0 ? err('Division by zero') : ok($a / $b);
}

$result = divide(10, 2);
$doubled = rmap($result, fn($x) => $x * 2);
// ['ok' => true, 'value' => 10]

// Working with Option for nullable values
$user = ['name' => 'John', 'email' => 'john@example.com'];
$email = omap(some($user['email'] ?? null), 'strtoupper');
// ['some' => true, 'value' => 'JOHN@EXAMPLE.COM']
```

## Function Composition

### pipe

Chains functions left-to-right, passing the result of each function to the next.

```php
use function Majesko\FpKit\Functions\pipe;

$transform = pipe(
    fn($x) => $x * 2,
    fn($x) => $x + 5,
    fn($x) => "Result: $x"
);

echo $transform(10); // "Result: 25"
```

### compose

Chains functions right-to-left (mathematical composition).

```php
use function Majesko\FpKit\Functions\compose;

$f = fn($x) => $x + 1;
$g = fn($x) => $x * 2;

$composed = compose($f, $g); // Equivalent to: f(g(x))

echo $composed(5); // 11 (5 * 2 + 1)
```

### tap

Executes a side effect without changing the value (useful for debugging).

```php
use function Majesko\FpKit\Functions\{pipe, tap};

$result = pipe(
    fn($x) => $x * 2,
    tap(fn($x) => error_log("Debug: $x")), // Logs but doesn't modify
    fn($x) => $x + 10
)(5); // Result: 20 (and logs "Debug: 10")
```

### partial

Creates a new function with some arguments pre-filled.

```php
use function Majesko\FpKit\Functions\partial;

$multiply = fn($a, $b) => $a * $b;
$double = partial($multiply, 2);

echo $double(5); // 10
echo $double(8); // 16
```

## Array Functions

Functional array manipulation utilities.

```php
use function Majesko\FpKit\Functions\{map, filter, reduce, groupBy, indexBy};

$numbers = [1, 2, 3, 4, 5];

// map: Transform each element
$doubled = map($numbers, fn($x) => $x * 2);
// [2, 4, 6, 8, 10]

// filter: Keep elements that match predicate
$evens = filter($numbers, fn($x) => $x % 2 === 0);
// [2, 4]

// reduce: Accumulate to a single value
$sum = reduce($numbers, fn($acc, $x) => $acc + $x, 0);
// 15

// groupBy: Group by key
$users = [
    ['name' => 'Alice', 'role' => 'admin'],
    ['name' => 'Bob', 'role' => 'user'],
    ['name' => 'Carol', 'role' => 'admin']
];
$byRole = groupBy($users, fn($u) => $u['role']);
// ['admin' => [['name' => 'Alice', ...], ['name' => 'Carol', ...]], 'user' => [...]]

// indexBy: Create associative array by key
$byName = indexBy($users, fn($u) => $u['name']);
// ['Alice' => ['name' => 'Alice', ...], 'Bob' => [...], ...]
```

## Result Type

`Result` represents an operation that can succeed (`ok`) or fail (`err`). It's perfect for error handling without exceptions.

### Creating Results

```php
use function Majesko\FpKit\Result\{ok, err};

$success = ok(42);           // ['ok' => true, 'value' => 42]
$failure = err('Not found'); // ['ok' => false, 'error' => 'Not found']
```

### Checking Results

```php
use function Majesko\FpKit\Result\{isOk, isErr};

if (isOk($result)) {
    // Handle success
}

if (isErr($result)) {
    // Handle error
}
```

### Transforming Results

```php
use function Majesko\FpKit\Result\{map, bind, mapError};

// map: Transform the success value
$result = ok(10);
$doubled = map($result, fn($x) => $x * 2);
// ['ok' => true, 'value' => 20]

// bind (flatMap): Chain operations that return Results
function parseNumber(string $s): array {
    return is_numeric($s) ? ok((float) $s) : err('Not a number');
}

function squareRoot(float $n): array {
    return $n >= 0 ? ok(sqrt($n)) : err('Negative number');
}

$result = bind(parseNumber('16'), fn($n) => squareRoot($n));
// ['ok' => true, 'value' => 4.0]

// mapError: Transform the error value
$result = err('user_not_found');
$mapped = mapError($result, fn($e) => "Error: $e");
// ['ok' => false, 'error' => 'Error: user_not_found']
```

### Pattern Matching

```php
use function Majesko\FpKit\Result\{matchResult, fold};

// matchResult: Pattern match on success/error
$message = matchResult(
    $result,
    ok: fn($value) => "Success: $value",
    err: fn($error) => "Error: $error"
);

// fold: Extract value with fallback
$value = fold($result,
    ok: fn($v) => $v,
    err: fn($e) => 0
);

// unwrapOr: Get value or default
use function Majesko\FpKit\Result\unwrapOr;
$value = unwrapOr($result, 'default');
```

### Real-World Example

```php
use function Majesko\FpKit\Result\{ok, err, bind};
use function Majesko\FpKit\Functions\pipe;

function findUser(int $id): array {
    $user = getUserFromDb($id);
    return $user ? ok($user) : err('User not found');
}

function validateUser(array $user): array {
    return $user['active'] ?? false
        ? ok($user)
        : err('User is inactive');
}

function getPermissions(array $user): array {
    return ok($user['permissions'] ?? []);
}

$result = pipe(
    fn() => findUser(123),
    fn($r) => bind($r, fn($u) => validateUser($u)),
    fn($r) => bind($r, fn($u) => getPermissions($u))
)();

// Result is either ok(['read', 'write']) or err('User not found')
```

## Option Type

`Option` represents a value that may or may not exist, similar to `null` but composable.

### Creating Options

```php
use function Majesko\FpKit\Option\{some, none, fromNullable};

$present = some(42);      // ['some' => true, 'value' => 42]
$absent = none();         // ['some' => false, 'value' => null]

// Create from potentially null value
$option = fromNullable($maybeNull);
```

### Transforming Options

```php
use function Majesko\FpKit\Option\{map, bind};

// map: Transform the value if present
$option = some(10);
$doubled = map($option, fn($x) => $x * 2);
// ['some' => true, 'value' => 20]

$empty = none();
$result = map($empty, fn($x) => $x * 2);
// ['some' => false, 'value' => null] (no transformation)

// bind: Chain operations that return Options
$result = bind(some('john@example.com'), fn($email) =>
    str_contains($email, '@') ? some(strtoupper($email)) : none()
);
```

### Pattern Matching

```php
use function Majesko\FpKit\Option\{matchOption, unwrapOr};

// matchOption: Handle both cases
$message = matchOption(
    $option,
    some: fn($value) => "Found: $value",
    none: fn() => "Not found"
);

// unwrapOr: Get value or default
$value = unwrapOr($option, 'default');
```

### Interop with Result

```php
use function Majesko\FpKit\Option\toResult;

$option = some(42);
$result = toResult($option, 'Value was None');
// ['ok' => true, 'value' => 42]

$option = none();
$result = toResult($option, 'Value was None');
// ['ok' => false, 'error' => 'Value was None']
```

## Validation Type

`Validation` is similar to `Result` but accumulates multiple errors instead of short-circuiting on the first error. Perfect for form validation.

### Creating Validations

```php
use function Majesko\FpKit\Validation\{valid, invalid};

$success = valid(42);              // ['valid' => true, 'value' => 42]
$failure = invalid(['Required']);  // ['valid' => false, 'errors' => ['Required']]
```

### Combining Validations

```php
use function Majesko\FpKit\Validation\{valid, invalid, combine};

function validateName(string $name): array {
    $errors = [];
    if (strlen($name) < 2) $errors[] = 'Name too short';
    if (strlen($name) > 50) $errors[] = 'Name too long';
    return empty($errors) ? valid($name) : invalid($errors);
}

function validateEmail(string $email): array {
    return str_contains($email, '@')
        ? valid($email)
        : invalid(['Invalid email']);
}

function validateAge(int $age): array {
    return $age >= 18
        ? valid($age)
        : invalid(['Must be 18 or older']);
}

// combine: Collect all errors
$result = combine([
    validateName('A'),           // Error: Name too short
    validateEmail('invalid'),     // Error: Invalid email
    validateAge(15)              // Error: Must be 18 or older
]);
// ['valid' => false, 'errors' => ['Name too short', 'Invalid email', 'Must be 18 or older']]

// All valid
$result = combine([
    validateName('John Doe'),
    validateEmail('john@example.com'),
    validateAge(25)
]);
// ['valid' => true, 'value' => ['John Doe', 'john@example.com', 25]]
```

### Lifting Functions

```php
use function Majesko\FpKit\Validation\{lift, valid};

// lift: Apply a function to multiple Validations
$createUser = fn($name, $email, $age) => [
    'name' => $name,
    'email' => $email,
    'age' => $age
];

$result = lift(
    $createUser,
    validateName('John'),
    validateEmail('john@example.com'),
    validateAge(25)
);
// ['valid' => true, 'value' => ['name' => 'John', 'email' => 'john@example.com', 'age' => 25]]
```

### Pattern Matching

```php
use function Majesko\FpKit\Validation\{matchValidation, errors};

$message = matchValidation(
    $validation,
    valid: fn($value) => "Valid: " . json_encode($value),
    invalid: fn($errors) => "Errors: " . implode(', ', $errors)
);

// Get errors array
$errorList = errors($validation); // [] if valid, ['error1', 'error2'] if invalid
```

## API Reference

### Function Composition

| Function | Signature | Description |
|----------|-----------|-------------|
| `pipe` | `(callable ...$fns): callable` | Left-to-right function composition |
| `compose` | `(callable ...$fns): callable` | Right-to-left function composition |
| `tap` | `(callable $fn): callable` | Execute side effect without changing value |
| `partial` | `(callable $fn, mixed ...$args): callable` | Partial application |

### Array Functions

| Function | Signature | Description |
|----------|-----------|-------------|
| `map` | `(array $xs, callable $fn): array` | Transform each element |
| `filter` | `(array $xs, callable $fn): array` | Keep elements matching predicate |
| `reduce` | `(array $xs, callable $fn, mixed $init): mixed` | Reduce to single value |
| `groupBy` | `(array $xs, callable $fn): array` | Group by key function |
| `indexBy` | `(array $xs, callable $fn): array` | Create associative array by key |

### Result Type

| Function | Signature | Description |
|----------|-----------|-------------|
| `ok` | `(mixed $value): array` | Create success Result |
| `err` | `(mixed $error): array` | Create error Result |
| `isOk` | `(array $r): bool` | Check if Result is success |
| `isErr` | `(array $r): bool` | Check if Result is error |
| `map` | `(array $r, callable $fn): array` | Transform success value |
| `bind` | `(array $r, callable $fn): array` | Chain Result-returning operations |
| `mapError` | `(array $r, callable $fn): array` | Transform error value |
| `unwrapOr` | `(array $r, mixed $default): mixed` | Get value or default |
| `fold` | `(array $r, callable $ok, callable $err): mixed` | Extract value with handlers |
| `matchResult` | `(array $r, callable $ok, callable $err): mixed` | Pattern match |

### Option Type

| Function | Signature | Description |
|----------|-----------|-------------|
| `some` | `(mixed $value): array` | Create present Option |
| `none` | `(): array` | Create absent Option |
| `isSome` | `(array $o): bool` | Check if Option has value |
| `isNone` | `(array $o): bool` | Check if Option is empty |
| `map` | `(array $o, callable $fn): array` | Transform value if present |
| `bind` | `(array $o, callable $fn): array` | Chain Option-returning operations |
| `unwrapOr` | `(array $o, mixed $default): mixed` | Get value or default |
| `fromNullable` | `(?mixed $value): array` | Create from nullable value |
| `fromArray` | `(array $arr, int\|string $key): array` | Create from array key |
| `toResult` | `(array $o, mixed $error): array` | Convert to Result |
| `matchOption` | `(array $o, callable $some, callable $none): mixed` | Pattern match |

### Validation Type

| Function | Signature | Description |
|----------|-----------|-------------|
| `valid` | `(mixed $value): array` | Create valid Validation |
| `invalid` | `(array $errors): array` | Create invalid Validation |
| `isValid` | `(array $v): bool` | Check if valid |
| `isInvalid` | `(array $v): bool` | Check if invalid |
| `errors` | `(array $v): array` | Get error list |
| `map` | `(array $v, callable $fn): array` | Transform value if valid |
| `combine` | `(array $validations): array` | Combine, accumulating errors |
| `lift` | `(callable $fn, array ...$validations): array` | Apply function to validations |
| `toResult` | `(array $v): array` | Convert to Result |
| `matchValidation` | `(array $v, callable $valid, callable $invalid): mixed` | Pattern match |

## Testing

Run the test suite:

```bash
# Run all tests
composer test

# Run PHPStan static analysis
composer stan

# Run code style checks
composer cs:check

# Fix code style issues
composer cs:fix

# Run all quality checks
composer qa
```

## Contributing

Contributions are welcome! This project follows:

- **PSR-12** coding standard
- **PHPStan level 7** static analysis
- **Strict types** throughout
- Comprehensive test coverage for all features

Please ensure all tests pass and code style checks succeed before submitting a PR.

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Why fp-kit?

Modern PHP has powerful features like arrow functions, named arguments, and union types that make functional programming more ergonomic. This library provides:

- **Type Safety**: Strict types and PHPStan ensure correctness
- **Composability**: Functions designed to work together seamlessly
- **No Magic**: Simple array-based types, no complex OOP hierarchies
- **Zero Overhead**: No dependencies, minimal abstraction cost
- **Practical**: Focused on real-world use cases, not academic purity

Inspired by functional programming patterns from Rust, Haskell, and Scala, adapted for PHP's strengths.
