<?php
/**
 * LipePlugin.CodeAnalysis.SelfInClass
 *
 * Used to verify a distributed plugin is not using `self` which
 * limits the extendability of a class.
 *
 * Inspired by StaticInFinalClassSniff
 *
 * @see     StaticInFinalClassSniff
 *
 * @package wp-phpcs\Lipe
 */

namespace LipePlugin\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Utils\Conditions;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\GetTokensAsString;
use PHPCSUtils\Utils\ObjectDeclarations;
use PHPCSUtils\Utils\Scopes;

/**
 * Forbid the `self` keyword for any classes.
 *
 * @since 4.1.15 - Allow `self` when accessing constants.
 */
class SelfInClassSniff implements Sniff {
	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var string[]
	 */
	public $supportedTokenizers = [ 'PHP' ];

	/**
	 * OO Scopes in which late static binding is useless.
	 *
	 * @var list<int|string>
	 */
	private $validOOScopes = [
		\T_CLASS,      // Only if final.
		\T_ANON_CLASS, // Final by nature.
		\T_ENUM,       // Final by design.
	];


	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return list<int|string>
	 */
	public function register(): array {
		return [
			// These tokens are used to retrieve return types reliably.
			\T_FUNCTION,
			\T_FN,
			// While this is our "real" target.
			\T_SELF,
			// But we also need this as after "instanceof", `self` is tokenized as `T_STRING in PHPCS < 4.0.0.
			// See: https://github.com/squizlabs/PHP_CodeSniffer/pull/3121.
			\T_STRING,
		];
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile                        The file being scanned.
	 * @param int  $stackPtr                         The position of the current token
	 *                                               in the stack passed in $tokens.
	 *
	 * @return int|void Integer stack pointer to skip forward or void to continue
	 *                  normal file processing.
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( \T_STRING === $tokens[ $stackPtr ]['code'] && 'self' !== \strtolower( $tokens[ $stackPtr ]['content'] )
		) {
			return;
		}

		if ( \T_FUNCTION === $tokens[ $stackPtr ]['code'] || \T_FN === $tokens[ $stackPtr ]['code'] ) {
			/*
			 * Check return types for methods in final classes, anon classes and enums.
			 *
			 * Will return the scope opener of the function to prevent potential duplicate notifications.
			 */
			$scopeOpener = $stackPtr;
			if ( true === isset( $tokens[ $stackPtr ]['scope_opener'] ) ) {
				$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
			}

			if ( \T_FUNCTION === $tokens[ $stackPtr ]['code'] ) {
				$ooPtr = Scopes::validDirectScope( $phpcsFile, $stackPtr, $this->validOOScopes );
				if ( false === $ooPtr ) {
					// Method in a trait (not known where it is used), interface (never final) or not in an OO scope.
					return $scopeOpener;
				}
			} else {
				$ooPtr = Conditions::getLastCondition( $phpcsFile, $stackPtr, $this->validOOScopes );
				if ( false === $ooPtr ) {
					// Arrow function is not OO.
					return $scopeOpener;
				}
			}

			if ( \T_ENUM === $tokens[ $ooPtr ]['code'] ) {
				// Methods in enums are final by design.
				return $scopeOpener;
			}

			if ( \T_CLASS === $tokens[ $ooPtr ]['code'] ) {
				$classProps = ObjectDeclarations::getClassProperties( $phpcsFile, $ooPtr );
				if ( true === $classProps['is_final'] ) {
					// Method in a final class cannot be static.
					return $scopeOpener;
				}
			}

			$functionProps = FunctionDeclarations::getProperties( $phpcsFile, $stackPtr );
			if ( '' === $functionProps['return_type'] ) {
				return $scopeOpener;
			}

			$staticPtr = $phpcsFile->findNext(
				\T_SELF,
				$functionProps['return_type_token'],
				( $functionProps['return_type_end_token'] + 1 )
			);

			if ( false === $staticPtr ) {
				return $scopeOpener;
			}

			// Found a return type containing the `self` type.
			$this->handleError( $phpcsFile, $staticPtr, 'ReturnType', '"self" return type' );

			return $scopeOpener;
		}

		/**
		 * Check other uses of self.
		 */
		$functionPtr = Conditions::getLastCondition( $phpcsFile, $stackPtr, [ \T_FUNCTION, \T_CLOSURE ] );
		if ( false === $functionPtr ) {
			// Not in a function or closure.
			return;
		}
		$ooPtr = Scopes::validDirectScope( $phpcsFile, $functionPtr, $this->validOOScopes );
		if ( false === $ooPtr ) {
			// Not in an OO context.
			return;
		}
		if ( \T_ENUM === $tokens[ $ooPtr ]['code'] ) {
			// Methods in enums are final by design.
			return;
		}

		if ( \T_CLASS === $tokens[ $ooPtr ]['code'] ) {
			$classProps = ObjectDeclarations::getClassProperties( $phpcsFile, $ooPtr );
			if ( true === $classProps['is_final'] ) {
				// Constants in a final class cannot be static.
				return;
			}
		}

		$prevNonEmpty = $phpcsFile->findPrevious( Tokens::$emptyTokens, ( $stackPtr - 1 ), null, true );
		if ( false !== $prevNonEmpty ) {
			if ( \T_INSTANCEOF === $tokens[ $prevNonEmpty ]['code'] ) {
				$prevPrevNonEmpty = $phpcsFile->findPrevious( Tokens::$emptyTokens, ( $prevNonEmpty - 1 ), null, true );
				if ( false === $prevPrevNonEmpty ) {
					return;
				}
				$extraMsg = GetTokensAsString::compact( $phpcsFile, $prevPrevNonEmpty, $stackPtr, true );
				$this->handleError( $phpcsFile, $stackPtr, 'InstanceOf', '"' . $extraMsg . '"' );
				return;
			}

			if ( \T_NEW === $tokens[ $prevNonEmpty ]['code'] ) {
				$this->handleError( $phpcsFile, $stackPtr, 'NewInstance', '"new self"' );
				return;
			}
		}

		$nextNonEmpty = $phpcsFile->findNext( Tokens::$emptyTokens, ( $stackPtr + 1 ), null, true );
		if ( false !== $nextNonEmpty && \T_DOUBLE_COLON === $tokens[ $nextNonEmpty ]['code'] ) {
			$nextNextNonEmpty = $phpcsFile->findNext( Tokens::$emptyTokens, ( $nextNonEmpty + 1 ), null, true );
			if ( false === $nextNextNonEmpty ) {
				return;
			}
			$extraMsg = GetTokensAsString::compact( $phpcsFile, $stackPtr, $nextNextNonEmpty, true );
			if ( \T_VARIABLE === $tokens[ $nextNextNonEmpty ]['code'] ) {
				$this->handleError( $phpcsFile, $stackPtr, 'ClassProperty', '"' . $extraMsg . '"' );
			} else {
				$possibleFunction = $phpcsFile->findNext( Tokens::$emptyTokens, ( $nextNextNonEmpty + 1 ), null, true );
				if ( false !== $possibleFunction && \T_OPEN_PARENTHESIS === $tokens[ $possibleFunction ]['code'] ) {
					$this->handleError( $phpcsFile, $stackPtr, 'ClassMethod', '"' . $extraMsg . '"' );
				}
			}
		}
	}

	/**
	 * Throw and potentially fix the error.
	 *
	 * @since 1.0.0
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of erroneous `T_SELF` token.
	 * @param string $errorCode The error code for the message.
	 * @param string $extraMsg  Addition to the error message.
	 *
	 * @return void
	 */
	private function handleError( $phpcsFile, $stackPtr, $errorCode, $extraMsg ) {
		$fix = $phpcsFile->addFixableWarning(
			'Use "static" instead of "self" when using late static binding in an OO construct. Found: %s',
			$stackPtr,
			$errorCode,
			[ $extraMsg ]
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->replaceToken( $stackPtr, 'static' );
		}
	}
}
