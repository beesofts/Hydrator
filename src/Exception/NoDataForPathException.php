<?php

namespace Beesofts\Hydrator\Exception;

class NoDataForPathException extends HydratorException
{
    /**
     * @param string[] $keys
     */
    public function __construct(
        public readonly string $path,
        public readonly array $keys,
    ) {
        $message = sprintf(
            'Unable to follow key "%s" in path "%s"',
            $keys[0],
            $path,
        );
        parent::__construct($message);
    }
}
