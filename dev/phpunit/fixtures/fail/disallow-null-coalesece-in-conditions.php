<?php
/** @noinspection ALL */

if ( $foo ?? false ) {
}
if ( $foo ?: false ) {
}
if ( $foo ? true : false ) {
}
if ( $post['post_type'] ?? 'page' === 'page' ) {
}
if ( $post['post_type'] ??= 'page' === 'page' ) {
}
if ( 0 !== $update_id ? 'updated' : 'added' ) {
}
