# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**majesko/fp-kit** is a lightweight functional programming toolkit for PHP 8.2+ that provides monadic types (Result, Option, Validation) and function composition utilities. The library uses simple array-based data structures (not classes) for maximum simplicity and composability.

## Development Commands

```bash
# Run all tests
composer test

# Run PHPStan static analysis (level 7)
composer stan

# Check code style (PSR-12)
composer cs:check

# Fix code style issues
composer cs:fix

# Run all quality checks (style, static analysis, tests)
composer qa
```

### Running Specific Tests

```bash
# Run a specific test class
vendor/bin/phpunit tests/ResultTest.php

# Run a specific test method
vendor/bin/phpunit --filter testMapTransformsOkValue tests/ResultTest.php
```

## Code Architecture

### Core Design Principles

1. **No Classes**: All types are represented as associative arrays with specific shapes. This keeps the library simple and avoids OOP complexity.

2. **Namespace per Type**: Each major feature has its own namespace with pure functions:
   - `Majesko\FpKit\Functions\` - Composition utilities and array helpers
   - `Majesko\FpKit\Result\` - Result type for error handling
   - `Majesko\FpKit\Option\` - Option type for nullable values
   - `Majesko\FpKit\Validation\` - Validation type for accumulating errors

3. **Array-Based Type Representations**:
   - **Result**: `['ok' => true, 'value' => mixed]` or `['ok' => false, 'error' => mixed]`
   - **Option**: `['some' => true, 'value' => mixed]` or `['some' => false, 'value' => null]`
   - **Validation**: `['valid' => true, 'value' => mixed]` or `['valid' => false, 'errors' => array]`

### File Organization

```
src/
├── Functions/
│   ├── pipe.php         # Left-to-right composition
│   ├── compose.php      # Right-to-left composition
│   ├── tap.php          # Side-effect function
│   ├── partial.php      # Partial application
│   └── array.php        # map, filter, reduce, groupBy, indexBy
├── Result/
│   └── result.php       # All Result functions (ok, err, map, bind, etc.)
├── Option/
│   └── option.php       # All Option functions (some, none, map, bind, etc.)
└── Validation/
    └── validation.php   # All Validation functions (valid, invalid, combine, lift)
```

All implementation files are registered in `composer.json` under `autoload.files` so functions are automatically available.

### Key Architectural Patterns

**Monadic Operations**:
- `map`: Transforms the wrapped value without changing the wrapper type
- `bind` (flatMap): Chains operations that return the same wrapper type, preventing nesting
- Both operations short-circuit on "failure" states (Err for Result, None for Option)

**Validation vs Result**:
- **Result** short-circuits on first error (like Railway Oriented Programming)
- **Validation** accumulates all errors using `combine()` or `lift()` (applicative style)
- Use Result for sequential operations, Validation for parallel validation (e.g., form validation)

**Function Composition**:
- `pipe()` takes a value and variadic functions: `pipe($value, $fn1, $fn2, ...)`
- `compose()` is mathematical composition (right-to-left): `compose($f, $g)($x)` = `$f($g($x))`
- `tap()` is for side effects without modifying the value (useful in pipes for debugging)

## Code Quality Requirements

- **Strict types**: All files must have `declare(strict_types=1);`
- **PSR-12 standard**: Enforced by PHP-CS-Fixer
- **PHPStan level 7**: All code must pass static analysis
- **Array syntax**: Use short array syntax `[]` not `array()`
- **Quotes**: Use single quotes except when necessary
- **DocBlocks**: Include type information in PHPDoc format for parameters and return types

## Testing Guidelines

- Tests are in `tests/` directory using PHPUnit 11
- Each source file has a corresponding test file (e.g., `ResultTest.php` for `result.php`)
- Focus on testing edge cases and type safety
- Test both success and error paths for monadic operations
- Current coverage: 79 tests with extensive edge case coverage

## Working with This Codebase

When adding new functions:
1. Add to the appropriate namespace file (or create new file if starting new feature area)
2. Register in `composer.json` under `autoload.files` if it's a new file
3. Follow the existing pattern: pure functions with strict types
4. Add comprehensive PHPDoc comments
5. Create corresponding tests
6. Ensure PHPStan level 7 compliance
7. Run `composer qa` before committing

When modifying existing functions:
1. Read the entire function file first to understand the existing API
2. Maintain backward compatibility with the array-based type representations
3. Update tests to cover new behavior
4. Check that changes don't break existing test suite

## Important Notes

- This library has **zero dependencies** (only dev dependencies for testing/quality)
- The array-based approach is intentional for simplicity - don't convert to classes
- Functions are imported with `use function` syntax in user code
- PHPStan is configured to analyze both `src/` and `tests/` directories
