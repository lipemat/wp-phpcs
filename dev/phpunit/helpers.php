<?php
/**
 * Set the value of a private property on an object.
 *
 * @noinspection PhpUnhandledExceptionInspection
 *
 * @param class-string|object $object   An instantiated object to set property on.
 * @param string              $property Property name to set.
 * @param mixed               $value    Value to set.
 *
 * @return void
 */
function set_private_property( $object, string $property, $value ): void {
	$reflection = new \ReflectionClass( is_string( $object ) ? $object : get_class( $object ) );
	$reflection_property = $reflection->getProperty( $property );
	$reflection_property->setAccessible( true );
	$reflection_property->setValue( is_string( $object ) ? new $object() : $object, $value );
}
