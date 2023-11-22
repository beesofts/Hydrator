<?php

namespace Beesofts\Hydrator\Tests\assets;

use Beesofts\Hydrator\Attribute\HydratedField;
use Beesofts\Hydrator\Attribute\HydratedObject;

#[HydratedObject]
class ClassWithHydration
{
    #[HydratedField(factory: 'validateCountry')]
    public string $country;

    #[HydratedField(path: 'travels', factory: 'computeSumOfTravelLengths')]
    public int $sumOfTravelLengths;

    /** @var \DateTimeImmutable[] */
    #[HydratedField(path: 'travels', factory: 'meltDateAndTime', collectionOf: \DateTimeImmutable::class)]
    public array $dates = [];

    /** @var array<string, string[]> */
    #[HydratedField(path: 'travels', factory: 'computeTravelsIndexedByTravelers')]
    public array $travelers = [];

    public function validateCountry(string $country): string
    {
        return match (strtolower($country)) {
            'fr' => 'France',
            'en' => 'England',
            default => ucfirst($country),
        };
    }

    /** @param \stdClass[] $travels */
    public function computeSumOfTravelLengths(array $travels): int
    {
        $sum = 0;
        foreach ($travels as $travel) {
            $sum += $travel->length;
        }

        return $sum;
    }

    /**
     * @param \stdClass[] $travels
     *
     * @return string[]
     */
    public function meltDateAndTime(array $travels): array
    {
        $melted = [];

        foreach ($travels as $travel) {
            $melted[] = $travel->dateOnly . ' ' . $travel->timeOnly;
        }

        return $melted;
    }

    /**
     * @param \stdClass[] $travels
     *
     * @return array<string, string[]>
     */
    public function computeTravelsIndexedByTravelers(array $travels): array
    {
        $output = [];

        foreach ($travels as $travel) {
            foreach ($travel->travelers as $traveler) {
                $output[(string) $traveler][] = (string) $travel->destination;
            }
        }

        return $output;
    }
}
