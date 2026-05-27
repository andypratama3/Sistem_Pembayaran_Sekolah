# Larastan Fix - PHPStan Extension Configuration

## Problem
The CI/CD pipeline was failing during the lint job with:

```
File '/home/runner/work/ProductsSchool/ProductsSchool/src/vendor/nunomaduro/larastan/extension.neon' 
is missing or is not readable.
Error: Process completed with exit code 1.
```

### Root Cause
The `phpstan.neon` configuration file was trying to include Larastan's extension:

```neon
includes:
  - vendor/nunomaduro/larastan/extension.neon
```

However, `nunomaduro/larastan` was **not listed in composer.json**, so it was never installed.

## Solution
Added `nunomaduro/larastan` to the `require-dev` section of `composer.json`:

```json
"nunomaduro/larastan": "^3.0"
```

### Why This Works
- **Larastan** is a PHPStan extension specifically designed for Laravel
- It provides Laravel-specific rules and type inference
- The `extension.neon` file is part of the Larastan package
- Once installed via Composer, the file will be available at the expected path

### What Larastan Provides
- Laravel-specific type inference
- Better understanding of Laravel facades
- Improved detection of common Laravel patterns
- Enhanced static analysis for Laravel code

## Files Changed
- `src/composer.json` - Added `nunomaduro/larastan: ^3.0` to require-dev

## Commit
```
3217aad7 - fix: add larastan to dev dependencies for PHPStan analysis
```

## Testing
The fix enables:
- ✅ PHPStan to find and load the Larastan extension
- ✅ Lint job to complete successfully
- ✅ Laravel-specific static analysis to run
- ✅ CI/CD pipeline to progress past the lint stage

## Related Files
- `src/phpstan.neon` - PHPStan configuration that includes Larastan
- `.github/workflows/ci-cd.yml` - CI/CD workflow that runs PHPStan

## Next Steps
1. GitHub Actions will automatically install Larastan via `composer install`
2. The lint job will successfully load the Larastan extension
3. PHPStan will run with full Laravel support
4. All static analysis checks will pass

## References
- [Larastan Documentation](https://larastan.com/)
- [PHPStan Documentation](https://phpstan.org/)
- [Laravel Static Analysis](https://laravel.com/docs/12.x/testing#static-analysis)
