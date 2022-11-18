<?php

declare(strict_types = 1);

namespace Shareforce\Sniffs\Testing;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

use const T_OPEN_USE_GROUP;

final class ArrangeActAssertCasingSniff implements Sniff
{
    private const ERROR = 'AAA Pattern comment should start with a capital and should contain a space.';

    private const INCORRECT_ARRANGE_COMMENT = 'IncorrectAAAPatternArrangeComment';
    private const INCORRECT_ACT_COMMENT = 'IncorrectAAAPatternActComment';
    private const INCORRECT_ASSERT_COMMENT = 'IncorrectAAAPatternAssertComment';

    private $errorCodeMapping = [
        'arrange' => self::INCORRECT_ARRANGE_COMMENT,
        'act' => self::INCORRECT_ACT_COMMENT,
        'assert' => self::INCORRECT_ASSERT_COMMENT,
    ];

    private $tokenMapping = [
        'arrange' => "// Arrange\n",
        'act' => "// Act\n",
        'assert' => "// Assert\n",
    ];

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

        if ($content === "// Arrange\n" || $content === "// Act\n" || $content === "// Assert\n") {
            return;
        }

        if (!preg_match('#//\s*(act|arrange|assert)\s*#i', $content, $matches)) {
            return;
        }

        $section = strtolower($matches[1]);

        $isFixing = $phpcsFile->addFixableError(
            self::ERROR,
            $stackPtr,
            $this->errorCodeMapping[$section]
        );

        if ($isFixing !== true) {
            return;
        }

        $phpcsFile->fixer->beginChangeset();
        $phpcsFile->fixer->replaceToken($stackPtr, $this->tokenMapping[$section]);
        $phpcsFile->fixer->endChangeset();
    }
}
