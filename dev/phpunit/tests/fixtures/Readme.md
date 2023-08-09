# Fixture Tests

## Lipe Sniffs Only

For testing the results against just the sniffs created by this library.

- Fixtures go in the `tests/fixtures/Lipe` directory.
- Sniffs must be added to the `FixtureTest.php` file.

## Full Lipe ruleset.xml

For testing the results when all configurations from the `ruleset.xml`
are parsed, including 3rd party sniffs and exclusions.

- Fixtures go in the `tests/fixtures/ruleset/Lipe` directory.
- Sniffs are automatically loaded from the `Lipe/ruleset.xml` file.
