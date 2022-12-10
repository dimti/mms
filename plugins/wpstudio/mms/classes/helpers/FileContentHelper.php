<?php namespace Wpstudio\Mms\Classes\Helpers;

use Collective\Remote\Connection;
use Wpstudio\Mms\Classes\Exceptions\MmsFileContentException;

final class FileContentHelper
{
    private function __construct()
    {

    }

    /**
     * @param string $content
     * @param string $searchQuery
     * @return string
     * @throws MmsFileContentException
     */
    public static function getLineBySearchQuery(string $content, string $searchQuery): string
    {
        $startPositionOfSearchQuery = strpos($content, $searchQuery);

        if ($startPositionOfSearchQuery === false) {
            throw new MmsFileContentException(
                sprintf('Line not found: %s', $searchQuery)
            );
        }

        $searchQueryReversePosition = strlen($content) - $startPositionOfSearchQuery;

        /**
         * This value greater than $startPositionOfSearchQuery
         * You mast be need calculate diff between this and reverse search query pos and return x2 diff from that
         */
        $previousNewLinePositionOnReverseContent = strpos(strrev($content), PHP_EOL, $searchQueryReversePosition);

        if ($previousNewLinePositionOnReverseContent !== false) {
            $diffBetweenPreviousNewLineOnReverseContentAndSearchQueryPosition = $previousNewLinePositionOnReverseContent - $searchQueryReversePosition;

            $previousNewLinePosition = $startPositionOfSearchQuery - $diffBetweenPreviousNewLineOnReverseContentAndSearchQueryPosition;

            $needlessLineStartPosition = $previousNewLinePosition;
        } else {
            $needlessLineStartPosition = 0;
        }

        $needlessLineEndPosition = strpos($content, PHP_EOL, $startPositionOfSearchQuery) - 1;

        return trim(substr($content, $needlessLineStartPosition, $needlessLineEndPosition - $needlessLineStartPosition), "\r\n");
    }

    public static function getFileContent(Connection $sshConnection, string $filePath): string
    {
        return SshHelper::getOutput(
            $sshConnection,
            sprintf('cat %s', $filePath)
        );
    }

    /**
     * @param string $content
     * @param int $lineNumber
     * @param string $lineContentToReplace
     * @return void
     * @throws MmsFileContentException
     */
    public static function replaceLine(string &$content, int $lineNumber, string $lineContentToReplace): void
    {
        $startPosition = 0;

        if ($lineNumber > 1) {
            $currentLineNumber = 2;

            $currentNextLinePosition = 0;

            while ($startPosition == 0) {
                $currentNextLinePosition = strpos($content, PHP_EOL, $currentNextLinePosition + 1);

                if ($currentNextLinePosition === false) {
                    throw new MmsFileContentException(sprintf(
                        'Not found line number %d for replacing content: %s',
                        $lineNumber,
                        $lineContentToReplace
                    ));
                }

                if ($currentLineNumber == $lineNumber) {
                    $startPosition = $currentNextLinePosition + 1;
                } else {
                    $currentLineNumber++;
                }
            }
        }

        $endPosition = strpos($content, PHP_EOL, $startPosition);

        $content = substr_replace(
            $content,
            $lineContentToReplace,
            $startPosition,
            ($endPosition - $startPosition) ? : null
        );
    }

    public static function getLineNumberBySearchQuery(string $content, string $searchQuery): int
    {
        $symbolPositionOfFirstOccurrenceBySearchQuery = strpos($content, $searchQuery);

        $lineNumberWithOccurrence = 0;

        $currentLine = 1;
        $currentNextLineSymbolPosition = 0;

        while ($lineNumberWithOccurrence == 0) {
            $currentNextLineSymbolPosition = strpos($content, PHP_EOL, $currentNextLineSymbolPosition + 1);

            if ($currentNextLineSymbolPosition > $symbolPositionOfFirstOccurrenceBySearchQuery) {
                $lineNumberWithOccurrence = $currentLine;
            }

            $currentLine++;
        }

        return $lineNumberWithOccurrence;
    }

    public static function hasExistsFile(Connection $sshConnection, string $filePath): bool
    {
        return SshHelper::getOutput(
            $sshConnection,
            [sprintf('test -f %s', $filePath), 'echo $?']
        ) == 0;
    }

    public static function hasExistsDir(Connection $sshConnection, string $filePath): bool
    {
        return SshHelper::getOutput(
            $sshConnection,
            [sprintf('test -d %s', $filePath), 'echo $?']
        ) == 0;
    }
}
