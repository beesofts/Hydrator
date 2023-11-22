<?php

namespace Beesofts\Hydrator\Tests\assets;

use Beesofts\Hydrator\Attribute\HydratedField;
use Beesofts\Hydrator\Attribute\HydratedObject;

#[HydratedObject]
class ClassWithPaths
{
    #[HydratedField(path: 'simple-path')]
    public string $simplePath;

    #[HydratedField(path: 'array.name')]
    public string $pathThroughtArray;

    #[HydratedField(path: 'object.name')]
    public string $pathThroughtObject;

    #[HydratedField(path: 'array."with.quotes"')]
    public string $arraywithQuotes;

    #[HydratedField(path: 'object."with.quotes"')]
    public string $objectWithQuotes;
}
