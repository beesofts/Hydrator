<?php

namespace Beesofts\Hydrator\Attribute;

#[\Attribute]
readonly class HydratedField
{
    /**
     * @param class-string|null $collectionOf
     * is it possible to type hint $factory to be a method name in the considered object ?
     */
    public function __construct(
        public ?string $path = null,
        public ?string $factory = null, // method name (public or protected)
        public ?string $collectionOf = null,
    ) {
    }
}
