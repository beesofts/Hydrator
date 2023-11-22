<?php

namespace Beesofts\Hydrator\Tests\assets;

use Beesofts\Hydrator\Attribute\HydratedField;
use Beesofts\Hydrator\Attribute\HydratedObject;
use Beesofts\Hydrator\Tests\assets\Embeds\ReadOnlyPosition;

#[HydratedObject]
readonly class ClassWithConstructor
{
    /**
     * @param ReadOnlyPosition[] $positions
     */
    public function __construct(
        #[HydratedField]
        public string $name,
        #[HydratedField]
        public ReadOnlyPosition $position,
        #[HydratedField(collectionOf: ReadOnlyPosition::class)]
        public array $positions,
    ) {
    }
}
