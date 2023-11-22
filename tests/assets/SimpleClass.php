<?php

namespace Beesofts\Hydrator\Tests\assets;

use Beesofts\Hydrator\Attribute\HydratedField;
use Beesofts\Hydrator\Attribute\HydratedObject;

#[\Attribute]
class UselessAttribute
{
}

#[HydratedObject]
class SimpleClass
{
    #[HydratedField]
    public string $publicField;

    #[HydratedField]
    protected string $protectedField;

    #[HydratedField]
    private string $privateField;

    #[HydratedField]
    public string $notInData = 'not-in-data-value';

    #[HydratedField]
    public readonly string $readonlyField;

    #[UselessAttribute]
    public string $untouched = 'untouched-value';

    /** @var array<string, string> */
    #[HydratedField]
    public array $userAsArray;

    #[HydratedField]
    public \stdClass $companyAsObject;

    public function __construct()
    {
        $this->readonlyField = 'read-only-value';
    }

    public function getProtectedField(): string
    {
        return $this->protectedField;
    }

    public function getPrivateField(): string
    {
        return $this->privateField;
    }
}
