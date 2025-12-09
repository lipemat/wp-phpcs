<?php
$foo = 'bar';
$bar = 'baz';
?>

<div class="<?= esc_attr( $foo ) ?>">
	<p>
		<?= esc_html( $bar ) ?>
	</p>
</div>
