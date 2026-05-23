# Alternating comparator: stash optimizations -> bench, pop -> bench, repeat.
# Aggregates min times across runs to reduce noise from system jitter.

param(
	[int]$Rounds = 2,
	[int]$Iter = 3,
	[int]$Warmup = 1
)

$root = 'E:\SVN\wp-phpcs'
$sniffFiles = @(
	'src/Lipe/Sniffs/PHP/DisallowNullCoalesceInConditionSniff.php',
	'src/Lipe/Sniffs/PHP/DisallowNullCoalesceInForLoopsSniff.php',
	'src/Lipe/Sniffs/Performance/SlowMetaQuerySniff.php',
	'src/Lipe/Sniffs/Security/NonceVerificationSniff.php',
	'src/LipePlugin/Sniffs/CodeAnalysis/SelfInClassSniff.php',
	'src/LipePlugin/Sniffs/TypeHints/PreventStrictTypesSniff.php'
)

function Run-Bench {
	param([int]$Iter, [int]$Warmup)
	$out = & D:\xampp\php-8.4\php.exe $root\.github\skills\benchmark-sniff\tools\benchmark.php $Iter $Warmup 2>&1
	$result = @{}
	foreach ($line in $out) {
		if ($line -match '^(Lipe\S+|LipePlugin\S+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)') {
			$result[$matches[1]] = [double]($matches[2] -replace ',', '')
		}
	}
	return $result
}

Push-Location $root
try {
	# Snapshot the current ("after") versions to a temp dir so we can swap
	# back deterministically without `git stash pop` conflicts.
	$swap = New-Item -ItemType Directory -Path (Join-Path $env:TEMP "bench-swap-$PID") -Force
	$savedAfter = @{}
	foreach ($f in $sniffFiles) {
		$src = Join-Path $root $f
		$dst = Join-Path $swap.FullName ($f -replace '[\\/]', '_')
		Copy-Item -Path $src -Destination $dst -Force
		$savedAfter[$f] = $dst
	}

	# Capture baseline versions (HEAD) into another temp dir.
	$savedBaseline = @{}
	foreach ($f in $sniffFiles) {
		$dst = Join-Path $swap.FullName ('base_' + ($f -replace '[\\/]', '_'))
		git show "HEAD:$f" > $dst 2>$null
		$savedBaseline[$f] = $dst
	}

	function Use-Baseline {
		foreach ($f in $script:sniffFiles) {
			Copy-Item -Path $script:savedBaseline[$f] -Destination (Join-Path $root $f) -Force
		}
	}
	function Use-After {
		foreach ($f in $script:sniffFiles) {
			Copy-Item -Path $script:savedAfter[$f] -Destination (Join-Path $root $f) -Force
		}
	}

	$baselineRuns = @()
	$afterRuns = @()

	for ($r = 0; $r -lt $Rounds; $r++) {
		Use-Baseline
		$baselineRuns += , (Run-Bench -Iter $Iter -Warmup $Warmup)

		Use-After
		$afterRuns += , (Run-Bench -Iter $Iter -Warmup $Warmup)
		Write-Host "Round $($r + 1)/$Rounds complete"
	}

	# Final state: restore After.
	Use-After

	$allSniffs = $baselineRuns[0].Keys | Sort-Object
	$tokenizerBaselineSniff = 'Lipe.Config.WpMinimumVersion'
	$baselineTokenizer = ($baselineRuns | ForEach-Object { $_[$tokenizerBaselineSniff] } | Measure-Object -Minimum).Minimum
	$afterTokenizer = ($afterRuns | ForEach-Object { $_[$tokenizerBaselineSniff] } | Measure-Object -Minimum).Minimum

	Write-Host ''
	Write-Host ('Tokenizer-only floor: baseline={0:N1} ms  after={1:N1} ms' -f $baselineTokenizer, $afterTokenizer)
	Write-Host ''
	Write-Host ('{0,-46} {1,11} {2,11} {3,11} {4,11} {5,11}' -f 'Sniff', 'base-min', 'after-min', 'net-base', 'net-after', 'delta')
	Write-Host ('-' * 100)

	foreach ($s in $allSniffs) {
		if ($s -eq $tokenizerBaselineSniff) { continue }
		$baseMin = ($baselineRuns | ForEach-Object { $_[$s] } | Measure-Object -Minimum).Minimum
		$afterMin = ($afterRuns | ForEach-Object { $_[$s] } | Measure-Object -Minimum).Minimum
		$netBase = $baseMin - $baselineTokenizer
		$netAfter = $afterMin - $afterTokenizer
		$netDelta = $netAfter - $netBase
		$pct = if ($netBase -gt 0.1) { 100.0 * $netDelta / $netBase } else { 0 }
		$marker = if ($netDelta -lt -3) { ' WIN' } elseif ($netDelta -gt 3) { ' LOSS' } else { '' }
		Write-Host ('{0,-46} {1,11:N1} {2,11:N1} {3,11:N1} {4,11:N1} {5,11:N1} ({6,5:N0}%){7}' -f $s, $baseMin, $afterMin, $netBase, $netAfter, $netDelta, $pct, $marker)
	}
} finally {
	if ($swap -and (Test-Path $swap.FullName)) {
		Remove-Item -Recurse -Force $swap.FullName
	}
	Pop-Location
}
