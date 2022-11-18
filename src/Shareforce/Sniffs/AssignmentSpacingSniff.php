<?php

declare(strict_types = 1);

namespace Shareforce\Sniffs\Testing;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

use const T_OPEN_USE_GROUP;

final class AssignmentSpacingSniff implements Sniff
{
    private const ERROR_BEFORE_CODE = 'AssignmentSpacingBefore';
    private const ERROR_BEFORE = 'Only one space is allowed before assignment.';

    private const ERROR_AFTER = 'Only one space is allowed after assignment.';
    private const ERROR_AFTER_CODE = 'AssignmentSpacingAfter';

    /**
     * @return array<int, (int|string)>
     */
    public function register(): array
    {
        return [
            T_VARIABLE,
        ];
    }

    /**
     * @param int $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $this->processWhitespaceToken($phpcsFile, $stackPtr + 1, self::ERROR_BEFORE, self::ERROR_BEFORE_CODE);

        // The next token should be the assignment operator
        $nextStackPtr = $stackPtr + 2;
        $nextToken = $tokens[$nextStackPtr];

        if ($nextToken['type'] !== 'T_EQUAL') {
            return;
        }

        // The next token should be whitespace
        $this->processWhitespaceToken($phpcsFile, $stackPtr + 3, self::ERROR_AFTER, self::ERROR_AFTER_CODE);
    }

    /**
     * @return bool Returns false if processing should be stopped; true otherwise.
     */
    private function processWhitespaceToken(File $phpcsFile, int $stackPtr, string $error, string $errorCode): bool
    {
        $tokens = $phpcsFile->getTokens();
        $nextToken = $tokens[$stackPtr];

        if ($nextToken['type'] !== 'T_WHITESPACE') {
            return false;
        }

        if ($this->isValidWhitespaceToken($nextToken)) {
            return true;
        }

        $fix = $phpcsFile->addFixableError($error, $stackPtr, $errorCode);

        if (!$fix) {
            return true;
        }

        $phpcsFile->fixer->beginChangeset();
        $phpcsFile->fixer->replaceToken($stackPtr, ' ');
        $phpcsFile->fixer->endChangeset();

        return true;
    }

    private function isValidWhitespaceToken(array $token): bool
    {
        $content = $token['content'];

        if (str_contains($content, "\n")) {
            return true;
        }

        return strlen($content) === 1;
    }

    private function debugToken(array $token): void
    {
        var_dump($token['type'] . ' -> "' . $token['content'] . '"');
    }
}
