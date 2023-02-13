<?php
/**
 * LipePlugin.CodeAnalysis.SelfInClassSniff
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
	 * @var int|string[]
	 */
	private $validOOScopes = [
		\T_CLASS,      // Only if final.
		\T_ANON_CLASS, // Final by nature.
		\T_ENUM,       // Final by design.
	];

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
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

		if ( $tokens[ $stackPtr ]['code'] === \T_STRING
			&& \strtolower( $tokens[ $stackPtr ]['content'] ) !== 'self'
		) {
			return;
		}

		if ( $tokens[ $stackPtr ]['code'] === \T_FUNCTION
			|| $tokens[ $stackPtr ]['code'] === \T_FN
		) {
			/*
			 * Check return types for methods in final classes, anon classes and enums.
			 *
			 * Will return the scope opener of the function to prevent potential duplicate notifications.
			 */
			$scopeOpener = $stackPtr;
			if ( isset( $tokens[ $stackPtr ]['scope_opener'] ) === true ) {
				$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
			}

			if ( $tokens[ $stackPtr ]['code'] === \T_FUNCTION ) {
				$ooPtr = Scopes::validDirectScope( $phpcsFile, $stackPtr, $this->validOOScopes );
				if ( $ooPtr === false ) {
					// Method in a trait (not known where it is used), interface (never final) or not in an OO scope.
					return $scopeOpener;
				}
			} else {
				$ooPtr = Conditions::getLastCondition( $phpcsFile, $stackPtr, $this->validOOScopes );
				if ( $ooPtr === false ) {
					// Arrow function is not OO.
					return $scopeOpener;
				}
			}

			if ( $tokens[ $ooPtr ]['code'] === \T_CLASS ) {
				$classProps = ObjectDeclarations::getClassProperties( $phpcsFile, $ooPtr );
				if ( $classProps['is_final'] === true ) {
					// Method in a final class cannot be static.
					return $scopeOpener;
				}
			}

			$functionProps = FunctionDeclarations::getProperties( $phpcsFile, $stackPtr );
			if ( $functionProps['return_type'] === '' ) {
				return $scopeOpener;
			}

			$staticPtr = $phpcsFile->findNext(
				\T_SELF,
				$functionProps['return_type_token'],
				( $functionProps['return_type_end_token'] + 1 )
			);

			if ( $staticPtr === false ) {
				return $scopeOpener;
			}

			// Found a return type containing the `self` type.
			$this->handleError( $phpcsFile, $staticPtr, 'ReturnType', '"self" return type' );

			return $scopeOpener;
		}

		/*
		 * Check other uses of self.
		 */
		$functionPtr = Conditions::getLastCondition( $phpcsFile, $stackPtr, [ \T_FUNCTION, \T_CLOSURE ] );
		$ooPtr       = Scopes::validDirectScope( $phpcsFile, $functionPtr, $this->validOOScopes );
		if ( $ooPtr === false ) {
			// Not in an OO context.
			return;
		}
		if ( $tokens[ $ooPtr ]['code'] === \T_CLASS ) {
			$classProps = ObjectDeclarations::getClassProperties( $phpcsFile, $ooPtr );
			if ( $classProps['is_final'] === true ) {
				// Constants in a final class cannot be static.
				return;
			}
		}

		$prevNonEmpty = $phpcsFile->findPrevious( Tokens::$emptyTokens, ( $stackPtr - 1 ), null, true );
		if ( $prevNonEmpty !== false ) {
			if ( $tokens[ $prevNonEmpty ]['code'] === \T_INSTANCEOF ) {
				$prevPrevNonEmpty = $phpcsFile->findPrevious( Tokens::$emptyTokens, ( $prevNonEmpty - 1 ), null, true );
				$extraMsg         = GetTokensAsString::compact( $phpcsFile, $prevPrevNonEmpty, $stackPtr, true );
				$this->handleError( $phpcsFile, $stackPtr, 'InstanceOf', '"' . $extraMsg . '"' );
				return;
			}

			if ( $tokens[ $prevNonEmpty ]['code'] === \T_NEW ) {
				$this->handleError( $phpcsFile, $stackPtr, 'NewInstance', '"new self"' );
				return;
			}
		}

		$nextNonEmpty = $phpcsFile->findNext( Tokens::$emptyTokens, ( $stackPtr + 1 ), null, true );
		if ( $nextNonEmpty !== false && $tokens[ $nextNonEmpty ]['code'] === \T_DOUBLE_COLON ) {
			$nextNextNonEmpty = $phpcsFile->findNext( Tokens::$emptyTokens, ( $nextNonEmpty + 1 ), null, true );
			$extraMsg         = GetTokensAsString::compact( $phpcsFile, $stackPtr, $nextNextNonEmpty, true );
			$this->handleError( $phpcsFile, $stackPtr, 'ScopeResolution', '"' . $extraMsg . '"' );
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
		$fix = $phpcsFile->addFixableError(
			'Use "static" instead of "self" when using late static binding in an OO construct. Found: %s',
			$stackPtr,
			$errorCode,
			[ $extraMsg ]
		);

		if ( $fix === true ) {
			$phpcsFile->fixer->replaceToken( $stackPtr, 'static' );
		}
	}
}
