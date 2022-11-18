<?php

declare(strict_types = 1);

namespace Shareforce\Sniffs\Testing;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

use const T_OPEN_USE_GROUP;

final class ArrangeActAssertBlankLinesSniff implements Sniff
{
    private const ERROR = 'AAA Pattern comment should not contain blank lines after comment.';
    private const INCORRECT_LINES = 'IncorrectAAAPatternBlankLines';

    /**
     * The directory to scan files for.
     *
     * @var string
     */
    public $baseDirectory = 'tests/';

    /**
     * @return array<int, (int|string)>
     */
    public function register(): array
    {
        return [
            T_COMMENT,
        ];
    }

    /**
     * @param int $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        if (!str_starts_with($phpcsFile->getFilename(), $this->baseDirectory)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $content = $tokens[$stackPtr]['content'];

        if ($content !== "// Arrange\n" && $content !== "// Act\n" && $content !== "// Assert\n") {
            return;
        }

        $nextStackPtr = $stackPtr + 1;

        while (isset($tokens[$nextStackPtr])) {
            $nextToken = $tokens[$nextStackPtr];

            if ($nextToken['type'] !== 'T_WHITESPACE') {
                break;
            }

            if ($nextToken['content'] !== "\n") {
                break;
            }

            $nextStackPtr++;
        }

        $foundLines = $nextStackPtr - $stackPtr - 1;

        if ($foundLines === 0) {
            return;
        }

        $isFixing = $phpcsFile->addFixableError(
            self::ERROR,
            $stackPtr + 1,
            self::INCORRECT_LINES
        );

        if (!$isFixing) {
            return;
        }

        $phpcsFile->fixer->beginChangeset();

        for ($i = $stackPtr + 1; $i < $nextStackPtr; ++$i) {
            $phpcsFile->fixer->replaceToken($i, '');
        }

        $phpcsFile->fixer->endChangeset();
    }
}
