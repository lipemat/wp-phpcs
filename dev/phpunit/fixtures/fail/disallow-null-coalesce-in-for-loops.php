<?php
/** @noinspection ALL */

foreach ( $attributes['properties'] ?? [] as $property => $value ) {
}

foreach ( $attributes['properties'] ??= [] as $property => $value ) {
}

for ( $i = 1; $i < $attributes['properties'] ?? 4; $i ++ ) {
}

for ( $i = 1; $i < $attributes['properties'] ??= 4; $i ++ ) {
}

foreach ( $attributes['properties'] ? [] : [ 1 ] as $property => $value ) {
}

for ( $i = 1; $i < $attributes['properties'] ? 4 : 3; $i ++ ) {
}

for ( $i = 1; ( $i < $attributes['properties'] ? 4 : 3 ); $i ++ ) {
}
