<?php

namespace Beesofts\Hydrator\Tests\assets;

use Beesofts\Hydrator\Attribute\HydratedField;
use Beesofts\Hydrator\Attribute\HydratedObject;
use Beesofts\Hydrator\Tests\assets\Embeds\Position;

#[HydratedObject]
class ClassWithTypes
{
    #[HydratedField]
    public $name;

    #[HydratedField]
    public string $zip;

    #[HydratedField]
    public int $age;

    #[HydratedField]
    public bool $isAdult;

    #[HydratedField]
    public bool $isVisible;

    #[HydratedField]
    public float $rate;

    #[HydratedField]
    public \DateTimeImmutable $createdAt;

    #[HydratedField]
    public Position $position;

    #[HydratedField]
    public ?Position $nullablePosition;
}
