<?php

namespace Beesofts\Hydrator;

use Beesofts\Hydrator\Exception\InvalidPathException;
use Beesofts\Hydrator\Exception\NoDataForPathException;

class DataBag
{
    private string $currentPath = '';

    public function __construct(
        private readonly object|array $data
    ) {
    }

    public function get(string $path): mixed
    {
        $this->currentPath = $path;

        return $this->getIn(self::resolvePath($path), $this->data);
    }

    /**
     * @param string[] $keys
     */
    private function getIn(array $keys, mixed $data): mixed
    {
        if (0 === count($keys)) {
            return $data;
        }

        if (
            is_array($data) &&
            array_key_exists($keys[0], $data)
        ) {
            return $this->getIn(
                array_slice($keys, 1),
                $data[$keys[0]],
            );
        } elseif (
            is_object($data) &&
            property_exists($data, $keys[0])
        ) {
            return $this->getIn(
                array_slice($keys, 1),
                $data->{$keys[0]},
            );
        }

        throw new NoDataForPathException($this->currentPath, $keys);
    }

    /**
     * @return string[]
     */
    private static function resolvePath(string $path): array
    {
        self::validatePath($path);

        $pattern = '/"([^"\r\n]+)"|([^"\r\n.]+)/';
        preg_match_all($pattern, $path, $matches, PREG_SET_ORDER);

        $keys = [];
        foreach ($matches as $match) {
            $captured = array_pop($match);

            if (is_null($captured)) {
                throw new InvalidPathException($path);
            }

            $keys[] = $captured;
        }

        return $keys;
    }

    private static function validatePath(string $path): void
    {
        if (0 === mb_strlen($path)) {
            throw new InvalidPathException($path);
        }

        // dots can't be at the start, the end, or followed by another dot
        if (
            str_starts_with($path, '.') ||
            str_ends_with($path, '.') ||
            (substr_count($path, '..') > 0) ||
            (substr_count($path, '""') > 0)
        ) {
            throw new InvalidPathException($path);
        }

        // any opened double quote must be closed
        if (0 !== (substr_count($path, '"') % 2)) {
            throw new InvalidPathException($path);
        }

        // ensure odd quotes are preceded by a dot or nothing
        // and even quotes are followed by a dot or nothing
        preg_match_all('/"/', $path, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        $counter = 1;
        foreach ($matches as $match) {
            $firstParenthesis = $match[0];
            $index = $firstParenthesis[1];
            if (
                (1 === ($counter % 2)) &&
                (0 !== $index) &&
                ('.' !== $path[$index - 1])
            ) {
                throw new InvalidPathException($path);
            }
            if (
                (0 === ($counter % 2)) &&
                ($index !== mb_strlen($path) - 1) &&
                ('.' !== $path[$index + 1])
            ) {
                throw new InvalidPathException($path);
            }

            ++$counter;
        }
    }
}
