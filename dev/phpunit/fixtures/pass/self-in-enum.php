<?php

enum E {
	case A;
	case B;
	case C;


	public function test(): void {
		self::C;
		self::B;

		match ( $this ) {
			self::A => 'A',
			self::B => 'B',
			self::C => 'C',
		};
	}
}
