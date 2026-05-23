---
name: benchmark-sniff
description: Benchmark wp-phpcs sniffs to measure per-sniff performance impact of changes before/after a modification.
---

@version 1.0.0

# Instructions for Benchmarking wp-phpcs Sniffs

Use this skill whenever a change in `E:\SVN\wp-phpcs` touches a sniff's `register()` token list, `process()` / `process_token()` body, any helper trait method (`src/Lipe/Traits/ArrayHelpers.php`, `ObjectHelpers.php`, `VariableHelpers.php`), or `src/Lipe/Abstracts/AbstractArrayObjectAssignment.php`.

Skip benchmarking for pure refactors that do not change runtime semantics (rename, extract method without changing call count, doc-only edits).

## Harness Overview

All tooling lives in `E:\SVN\wp-phpcs\.github\skills\benchmark-sniff\tools\`:

- `benchmark.php` — runs each sniff in its `$sniffs` array against `generated-fixture.php` and prints `min / median / max` ms over N iterations with warmup. Builds the `Ruleset` once per sniff outside the timing loop, then strips `Ruleset::$sniffs` and `$tokenListeners` to a single class so the timed `LocalFile::process()` call truly measures one sniff.
- `generate-fixture.php` — defines `generate_benchmark_fixture()`. Emits a synthetic ~2,000-LoC plugin file containing every pattern the sniffs care about (`get_posts` / `WP_Query` / `meta_query` / fluent interfaces / nonces / `private` / `self::` / null-coalesce). `benchmark.php` regenerates `generated-fixture.php` automatically when this file is newer. Bump `$repeat` to amplify signal if the noise floor is too high.
- `generated-fixture.php` — auto-generated alongside the other tools when `benchmark.php` runs; never edit by hand and never commit it (add to `.gitignore` if not already ignored).
- `compare.ps1` — alternating A/B comparator. Snapshots current working-tree versions of a hardcoded `$sniffFiles` list, captures `HEAD` versions via `git show`, then alternates baseline ↔ after for N rounds, aggregating `min` across runs. Subtracts the tokenizer-only floor (measured via `Lipe.Config.WpMinimumVersion`, a near-empty sniff) so reported `net-delta` reflects sniff work alone.
- `diagnose.php` — ad-hoc per-token timing harness for drilling into a single hot sniff; edit in place.

## Pre-Flight

1. Verify the modified sniffs are listed in `benchmark.php`'s `$sniffs` array. If a newly added sniff is not present, add it. Sniff codes follow the form `Lipe.<Category>.<Name>` or `LipePlugin.<Category>.<Name>`.
2. For A/B comparison, edit `compare.ps1`'s `$sniffFiles` array to list the exact relative paths of every sniff file you modified. Always include at least one unmodified sniff in `$sniffFiles` as a noise-floor control (or rely on the unmodified rows of `benchmark.php`'s output for the same purpose).

## Running

Always run from the project root `E:\SVN\wp-phpcs`. Use the explicit PHP interpreter path so the bare `pwsh` agent shell does not collide with the global wrapper scripts:

```pwsh
# Single snapshot of current working tree (5 iterations, 1 warmup):
D:\xampp\php-8.4\php.exe .github\skills\benchmark-sniff\tools\benchmark.php 5 1

# A/B compare working tree vs HEAD (file-copy swap, no git stash):
pwsh -File .github\skills\benchmark-sniff\tools\compare.ps1 -Rounds 3 -Iter 3 -Warmup 1
```

Pipe long output through `| Out-String` (or `| Select-Object -Last N`) in pwsh so results aren't truncated to an object table.

`compare.ps1` restores the "after" (working-tree) versions on exit via a `finally` block. If it dies mid-swap, sniff files listed in `$sniffFiles` may be left at their `HEAD` content. Verify with `git status` before continuing further work.

## Interpreting Results

- **Noise floor is ~±20ms** on the default fixture (`$repeat = 80`) over 3×3 runs. Treat any `net-delta` smaller than **~25ms** (≈3% of an 800ms sniff) as noise, not signal.
- `compare.ps1` prints ` WIN` / ` LOSS` markers using a much tighter ±3ms threshold. **Ignore those markers** for sniffs in the 700ms+ range — they will fire on pure jitter.
- Sniffs that were not modified will drift ±20ms run-to-run. Use them as your in-run noise control.
- The synthetic fixture is uniform. It will NOT reveal wins that depend on file shape (e.g. an O(N²) loop that only matters in HTML-heavy templates, or a fluent-interface fast-path that only matters in args-heavy code). For those, extend `generate-fixture.php` with a targeted pattern (behind a flag if needed) or test against a real consumer project.
- Ruleset construction cost (loading WPCS + every dependency) is excluded from per-sniff timing. The harness will NOT catch regressions in sniff *constructor* or `register()` cost — only `process()` / `process_token()` work is measured.

## Reporting

When reporting benchmark results to the user, always include:

1. The exact command invocation used (rounds, iterations, warmup).
2. A table with `sniff`, `base-min`, `after-min`, `net-delta`, `pct`, mirroring `compare.ps1`'s output.
3. Honest characterization of each delta: **proven win** (delta > noise floor of ±25ms), **neutral / within noise**, or **regression**. Do not claim wins below the noise floor.
4. The noise-floor evidence: cite an unmodified control sniff's drift in the same run so the user can judge signal vs. jitter.
5. Whether the change is algorithmically defensible even if benchmark-neutral (e.g. eliminates an O(N²) on a file shape the synthetic fixture doesn't exercise).
