# AGENTS.md — wp-phpcs

PHP_CodeSniffer standards package (`type: phpcodesniffer-standard`) shipping two custom standards (`Lipe`, `LipePlugin`) on top of WPCS, VIPCS, PHPCompatibilityWP, and PHPCSExtra. Target runtime: PHP `>=7.4`.

## Architecture

- `src/Lipe/` and `src/LipePlugin/` are two independent PHPCS standards. Each has its own `ruleset.xml` (declares included/excluded vendor rules) and a `Sniffs/<Category>/<Name>Sniff.php` tree. Category folders (e.g. `Performance/`, `DB/`, `Security/`) map directly to the sniff codes consumers reference (e.g. `Lipe.Performance.SuppressFilters`).
- Custom sniffs typically extend `WordPressCS\WordPress\AbstractFunctionRestrictionsSniff` or `AbstractArrayAssignmentRestrictionsSniff` and compose shared logic via traits in `src/Lipe/Traits/` (`ArrayHelpers`, `ObjectHelpers`, `VariableHelpers`). See `src/Lipe/Sniffs/Performance/SuppressFiltersSniff.php` as the canonical example — it uses all three traits plus `PHPCSUtils\Utils\MessageHelper` for error reporting.
- `src/Lipe/Abstracts/AbstractArrayObjectAssignment.php` is the shared base for sniffs that need to inspect both array literals and object property assignments.
- `LipePlugin` ruleset intentionally re-references whole `Lipe.*` categories (`<rule ref="Lipe.Performance" />`) — keep new `Lipe` sniffs categorized so they auto-flow into `LipePlugin`.
- `phpcs-sample.xml` is the consumer-facing starter ruleset; `phpcs.xml.dist` lints THIS repo's own source.

## Sniff conventions

- Namespace `Lipe\Sniffs\<Category>` / `LipePlugin\Sniffs\<Category>`; filename ends in `Sniff.php`; class name ends in `Sniff`.
- Error codes are produced via `MessageHelper::stringToErrorcode( $matched_content )` so the sniff name + matched function becomes the code (e.g. `Lipe.Performance.SuppressFilters.get_posts`).
- Prefer reusing the trait helpers (`find_key_in_array`, `is_variable_an_array`, `get_assigned_keys_from_variable`, `get_assigned_properties`) over re-implementing token walking.
- Do NOT add `declare( strict_types=1 )` in `LipePlugin` source — `LipePlugin.TypeHints.PreventStrictTypes` forbids it for distributed code. `dev/` test files may use it.
- Avoid `private` visibility in code intended to be extended; `LipePlugin.TypeHints.PrivateInClass` flags it. Use `self` only where `static` would break extensibility (`Lipe.CodeAnalysis.SelfInClassSniff`).

## Testing (`dev/phpunit/`)

Two complementary suites, both required:

1. **Sniff unit tests** — `tests/Lipe/Tests/<Category>/<Name>UnitTest.php` extending `PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest`. Sibling `*.fail.inc` / `*.pass.inc` / `*.success.inc` fixture files are auto-discovered by filename suffix. `getErrorList()` / `getWarningList()` switch on `func_get_arg(0)` (the fixture filename). Discovery is driven by `SniffSuiteAbstract` which loads any `*UnitTest.php` under `tests/<STANDARD>/Tests/`.
2. **Fixture tests** — `tests/Fixtures.php` runs the full `Lipe` ruleset against every file in `dev/phpunit/fixtures/{pass,fail}/`. Each `fail/<name>.php` needs a matching `<name>.php.json` mapping `line => [{source, type}]` (see `fixtures/fail/suppress-filters.php.json`). Add the sniff code to the `$this->config->sniffs` array in `Fixtures::setUp()` when introducing a new sniff.
3. Ruleset smoke tests (`RulesetFixtureLipe.php`, `RulesetFixtureLipePlugin.php`) run files in `fixtures/ruleset/{Lipe,LipePlugin}/{fail,pass}` against the entire shipped ruleset.

Bootstrap (`dev/phpunit/bootstrap.php`) wires PHPCS autoload search paths and loads abstract WPCS sniffs in dependency order — add new vendor sniff namespaces there if extending from them. Helper functions `set_private_property` / `get_private_property` (in `helpers.php`) are available globally in tests.

## Developer workflows

Always run from project root unless noted. Use globally-installed binaries:

```pwsh
phpcs                                  # lints src/ per phpcs.xml.dist (no warnings allowed)
phpstan analyse --memory-limit=2G      # level 8, scans src/
cd dev/phpunit; phpunit                # runs all three suites
```

The `dev/git-hooks/pre-commit` script runs phpunit, php lint (both PHP 7.4 and 8.4), phpcs, and phpstan in parallel — mirror this locally before reporting work complete. Install via `composer git-hooks`.

### Running tools from automation / agent shells

The global `phpcs` / `phpstan` / `phpunit` wrappers in `E:\scripts\*\*.bat|.cmd` expect to be launched from an interactive shell that has `passed-php-version.cmd` on PATH and PHP shims aliased as `php 8.4`, `php 8.5`, etc. From a bare `pwsh` agent session those aliases are missing and the wrappers fail with `Could not open input file: 8.5` (the version arg leaks through). When that happens, invoke the underlying tool directly with an explicit interpreter — this matches what the wrappers ultimately call:

```pwsh
# PHP interpreters (XAMPP, side-by-side):
#   D:\xampp\php-7.4\php.exe  …  D:\xampp\php-8.5\php.exe
# Use 8.4 by default; the project's min runtime is PHP 7.4 (lint with 7.4 too before commit).

# PHPCS (use the vendored binary, NOT the global wrapper):
D:\xampp\php-8.4\php.exe vendor\squizlabs\php_codesniffer\bin\phpcs

# PHPStan (global install lives outside the repo):
D:\xampp\php-8.4\php.exe E:\SVN\phpstan\vendor\bin\phpstan analyse --memory-limit=2G

# PHPUnit (phars in E:\scripts\phpunit\phpunit-{9,10,11}\). PHPUnit 11 requires PHP >= 8.2:
cd dev\phpunit
D:\xampp\php-8.4\php.exe E:\scripts\phpunit\phpunit-11\phpunit-11.phar
```

Pipe long output through `| Out-String` (or `Select-Object -Last N`) in pwsh so it isn't truncated to an object table. The PHPUnit suite has two pre-existing warnings about `LipeSniffs` / `LipePluginSniffs` not extending `TestCase` — these are data-provider holders and are expected; only treat new failures as regressions.

When adding a sniff: (1) create `Sniffs/<Cat>/<Name>Sniff.php`, (2) add unit test + fixtures, (3) add the code to `Fixtures::setUp()` sniffs list, (4) optionally add `fixtures/fail|pass` files with `.json` expectations, (5) document in `README.md`.

## Performance benchmarking

A per-sniff micro-benchmark harness lives in `.github/skills/benchmark-sniff/tools/` (`benchmark.php`, `compare.ps1`, `generate-fixture.php`, `diagnose.php`). Use it whenever a change touches a sniff's `register()` set, `process()` / `process_token()` body, any trait in `src/Lipe/Traits/`, or `src/Lipe/Abstracts/AbstractArrayObjectAssignment.php`. Skip for pure refactors.

Full usage, noise-floor calibration (~±20ms), and reporting requirements are documented in the **`benchmark-sniff` skill** at `.github/skills/benchmark-sniff/SKILL.md`. Read that skill before running benchmarks.
