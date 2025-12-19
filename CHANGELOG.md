# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-12-19

### Added

#### Function Composition
- `pipe()` - Left-to-right function composition
- `compose()` - Right-to-left (mathematical) function composition
- `tap()` - Execute side effects without modifying values
- `partial()` - Partial function application

#### Array Functions
- `map()` - Transform array elements with a function
- `filter()` - Filter array elements by predicate
- `reduce()` - Reduce array to single value with accumulator
- `groupBy()` - Group array elements by key function
- `indexBy()` - Create associative array indexed by key function

#### Result Type
- `ok()` / `err()` - Constructors for success and error cases
- `isOk()` / `isErr()` - Type checking predicates
- `map()` - Transform success values (functor)
- `bind()` - Chain Result-returning operations (monad)
- `mapError()` - Transform error values
- `unwrapOr()` - Extract value with default fallback
- `fold()` - Extract value with success/error handlers
- `matchResult()` - Pattern matching for Result values

#### Option Type
- `some()` / `none()` - Constructors for present/absent values
- `isSome()` / `isNone()` - Type checking predicates
- `map()` - Transform present values (functor)
- `bind()` - Chain Option-returning operations (monad)
- `unwrapOr()` - Extract value with default fallback
- `fromNullable()` - Create Option from potentially null value
- `fromArray()` - Create Option from array key access
- `toResult()` - Convert Option to Result
- `matchOption()` - Pattern matching for Option values

#### Validation Type
- `valid()` / `invalid()` - Constructors for valid/invalid cases
- `isValid()` / `isInvalid()` - Type checking predicates
- `errors()` - Extract accumulated errors
- `map()` - Transform valid values (functor)
- `combine()` - Combine multiple validations, accumulating all errors
- `lift()` - Apply function to multiple validations
- `toResult()` - Convert Validation to Result
- `matchValidation()` - Pattern matching for Validation values

#### Development Infrastructure
- PHPUnit test suite with 79 tests and 113 assertions
- PHPStan level 7 static analysis configuration
- PHP CS Fixer with PSR-12 compliance
- GitHub Actions CI pipeline testing PHP 8.2, 8.3, and 8.4
- Composer scripts for QA automation (`composer qa`)

### Technical Details
- **Minimum PHP Version:** 8.2
- **Dependencies:** Zero production dependencies
- **Code Standard:** PSR-12 compliant with strict types
- **License:** MIT
- **Array-Based Implementation:** Lightweight monadic types using plain PHP arrays for zero overhead

[1.0.0]: https://github.com/majesko/fp-kit/releases/tag/1.0.0
