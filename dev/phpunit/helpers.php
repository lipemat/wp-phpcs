<?php
/**
 * Set the value of a private property on an object.
 *
 * @noinspection PhpUnhandledExceptionInspection
 *
 * @param object $object   An instantiated object to set property on.
 * @param string $property Property name to set.
 * @param mixed  $value    Value to set.
 *
 * @throws ReflectionException
 * @return void
 */
function set_private_property( $object, string $property, $value ) {
	$reflection = new \ReflectionClass( get_class( $object ) );
	$reflection_property = $reflection->getProperty( $property );
	$reflection_property->setAccessible( true );
	$reflection_property->setValue( $object, $value );
}
