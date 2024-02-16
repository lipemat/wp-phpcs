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

/**
 * Get the value of a private constant or property from an object.
 *
 * @param class-string|object $object   An instantiated object or class name that we will run method on.
 * @param string              $property Property name or constant name to get.
 *
 * @return mixed
 */
function get_private_property( $object, string $property ) {
	$reflection = new \ReflectionClass( is_string( $object ) ? $object : get_class( $object ) );
	if ( $reflection->hasProperty( $property ) ) {
		$reflection_property = $reflection->getProperty( $property );
		$reflection_property->setAccessible( true );
		return $reflection_property->getValue( is_string( $object ) ? new $object() : $object );
	}
	return $reflection->getConstant( $property );
}
