<?php

namespace Beesofts\Hydrator\Tests\assets;

use Beesofts\Hydrator\Attribute\HydratedField;
use Beesofts\Hydrator\Attribute\HydratedObject;
use Beesofts\Hydrator\Tests\assets\Embeds\Position;

#[HydratedObject]
class ClassWithCollections
{
    /** @var \DateTimeImmutable[] */
    #[HydratedField(collectionOf: \DateTimeImmutable::class)]
    public array $dates = [];

    /** @var Position[] */
    #[HydratedField(collectionOf: Position::class)]
    public array $positions = [];
}
