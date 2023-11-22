<?php

namespace Beesofts\Hydrator\Tests\assets\Embeds;

use Beesofts\Hydrator\Attribute\HydratedField;
use Beesofts\Hydrator\Attribute\HydratedObject;

#[HydratedObject]
class Position
{
    #[HydratedField]
    public float $latitude;

    #[HydratedField]
    public float $longitude;
}
