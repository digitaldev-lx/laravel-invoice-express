# Contributing

Thanks for contributing to `digitaldev-lx/laravel-invoice-express`.

## Development setup

```bash
git clone git@github.com:digitaldev-lx/laravel-invoice-express.git
cd laravel-invoice-express
composer install
```

## Quality gates

Before opening a pull request, run:

```bash
composer format          # Pint
composer analyse         # PHPStan level 6
composer test            # Pest
```

CI runs the same checks against PHP 8.4 with Laravel 12 and 13.

## Commit messages

Concise, imperative, present tense. Group related changes in a single commit.

## Reporting issues

Open an issue describing:

- What you tried (a minimal reproduction is gold)
- What you expected
- What happened instead
- The package version and Laravel/PHP versions

## License

By contributing, you agree that your contributions will be licensed under the MIT license.
