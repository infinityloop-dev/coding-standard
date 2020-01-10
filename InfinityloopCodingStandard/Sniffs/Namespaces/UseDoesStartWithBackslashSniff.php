<?php 

declare(strict_types = 1);

namespace InfinityloopCodingStandard\Sniffs\Namespaces;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;
use SlevomatCodingStandard\Helpers\UseStatementHelper;
use const T_NS_SEPARATOR;
use const T_STRING;
use const T_USE;

class UseDoesNotStartWithBackslashSniff implements Sniff
{
	public const CODE_DOESNT_START_WITH_BACKSLASH = 'UseDoesntStartWithBackslash';

	/**
	 * @return (int|string)[]
	 */
	public function register(): array
	{
		return [
			T_USE,
		];
	}

	/**
	 * @phpcsSuppress Slevoma\CodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile
	 * @param int $usePointer
	 */
	public function process(File $phpcsFile, $usePointer): void
	{
		if (
			UseStatementHelper::isAnonymousFunctionUse($phpcsFile, $usePointer)
			|| UseStatementHelper::isTraitUse($phpcsFile, $usePointer)
		) {
			return;
		}

		$tokens = $phpcsFile->getTokens();
        
		/** @var int $nextTokenPointer */
		$nextTokenPointer = TokenHelper::findNextEffective($phpcsFile, $usePointer + 1);
        
		if ($tokens[$nextTokenPointer]['code'] === T_STRING
			&& ($tokens[$nextTokenPointer]['content'] === 'function' || $tokens[$nextTokenPointer]['content'] === 'const')
		) {
			/** @var int $nextTokenPointer */
			$nextTokenPointer = TokenHelper::findNextEffective($phpcsFile, $nextTokenPointer + 1);
		}
        
		if (!$tokens[$nextTokenPointer]['code'] !== T_NS_SEPARATOR) {
			return;
		}
        
		$fix = $phpcsFile->addFixableError(
			'Use statement must start with a backslash.',
			$nextTokenPointer,
			self::CODE_DOESNT_START_WITH_BACKSLASH
		);
        
		if (!$fix) {
			return;
		}
        
		$phpcsFile->fixer->beginChangeset();
		$phpcsFile->fixer->addContentBefore($nextTokenPointer, '\\');
		$phpcsFile->fixer->endChangeset();
	}
}
