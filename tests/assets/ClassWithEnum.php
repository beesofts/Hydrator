<?php

namespace Beesofts\Hydrator\Tests\assets;

use Beesofts\Hydrator\Attribute\HydratedField;
use Beesofts\Hydrator\Attribute\HydratedObject;

#[HydratedObject]
class ClassWithEnum
{
    #[HydratedField]
    public string $name;

    #[HydratedField]
    public GroupEnum $group;
}
