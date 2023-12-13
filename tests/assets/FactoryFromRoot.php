<?php

namespace Beesofts\Hydrator\Tests\assets;

use Beesofts\Hydrator\Attribute\HydratedField;
use Beesofts\Hydrator\Attribute\HydratedObject;
use Beesofts\Hydrator\Tests\assets\Embeds\ReadOnlyPosition;

#[HydratedObject]
class FactoryFromRoot
{
    #[HydratedField]
    public string $title;

    #[HydratedField(path: '*', factory: 'buildPosition')]
    public ReadOnlyPosition $position;

    public function buildPosition(mixed $values): ReadOnlyPosition
    {
        return new ReadOnlyPosition($values['latitude'], $values['longitude']);
    }
}
