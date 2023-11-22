<?php

namespace Beesofts\Hydrator\Exception;

class InvalidPathException extends HydratorException
{
    public function __construct(public string $key)
    {
        $message = sprintf('Key "%s" is not valid', $key);
        parent::__construct($message);
    }
}
