<?php

namespace Beesofts\Hydrator\Tests;

use Beesofts\Hydrator\CaseConverter;
use Beesofts\Hydrator\Hydrator;
use Beesofts\Hydrator\Tests\assets\ClassWithCollections;
use Beesofts\Hydrator\Tests\assets\ClassWithConstructor;
use Beesofts\Hydrator\Tests\assets\ClassWithHydration;
use Beesofts\Hydrator\Tests\assets\ClassWithPaths;
use Beesofts\Hydrator\Tests\assets\ClassWithTypes;
use Beesofts\Hydrator\Tests\assets\Embeds\Position;
use Beesofts\Hydrator\Tests\assets\Embeds\ReadOnlyPosition;
use Beesofts\Hydrator\Tests\assets\FactoryFromRoot;
use Beesofts\Hydrator\Tests\assets\SimpleClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Hydrator::class)]
class HydratorTest extends TestCase
{
    public function testBuildSimpleObject(): void
    {
        $classname = \DateTimeImmutable::class;
        $data = '2015-10-27 10:00:00';

        self::assertInstanceOf(\DateTimeImmutable::class, Hydrator::build($classname, $data));
        self::assertEquals(new \DateTimeImmutable($data), Hydrator::build($classname, $data));
    }

    public function testHydrateSimpleClassWithArray(): void
    {
        $data = [
            'publicField' => 'aa',
            'protectedField' => 'bb',
            'privateField' => 'cc',
            'readonlyField' => 'php disallow to write readonly fields even with reflection',
            'untouched' => 'should not be used',
            'userAsArray' => [
                'name' => 'John',
                'lastname' => 'Doe',
            ],
            'companyAsObject' => (object) [
                'name' => 'Company SA',
                'size' => 25,
            ],
            'unusedData' => 'unused-value',
        ];

        $object = new SimpleClass();
        Hydrator::hydrate($object, $data);

        self::assertEquals('aa', $object->publicField);
        self::assertEquals('bb', $object->getProtectedField());
        self::assertEquals('cc', $object->getPrivateField());
        self::assertEquals('not-in-data-value', $object->notInData);
        self::assertEquals('read-only-value', $object->readonlyField);
        self::assertEquals('untouched-value', $object->untouched);
        self::assertEquals($data['userAsArray'], $object->userAsArray);
        self::assertEquals($data['companyAsObject'], $object->companyAsObject);
    }

    public function testHydrateSimpleClassWithObject(): void
    {
        $data = (object) [
            'publicField' => 'aa',
            'protectedField' => 'bb',
            'privateField' => 'cc',
            'readonlyField' => 'php disallow to write readonly fields even with reflection',
            'untouched' => 'should not be used',
            'userAsArray' => [
                'name' => 'John',
                'lastname' => 'Doe',
            ],
            'companyAsObject' => (object) [
                'name' => 'Company SA',
                'size' => 25,
            ],
        ];

        $object = new SimpleClass();
        Hydrator::hydrate($object, $data);

        self::assertEquals('aa', $object->publicField);
        self::assertEquals('bb', $object->getProtectedField());
        self::assertEquals('cc', $object->getPrivateField());
        self::assertEquals('not-in-data-value', $object->notInData);
        self::assertEquals('read-only-value', $object->readonlyField);
        self::assertEquals('untouched-value', $object->untouched);
        self::assertEquals($data->userAsArray, $object->userAsArray);
        self::assertEquals($data->companyAsObject, $object->companyAsObject);
    }

    public function testBuildAllowToSetReadonlyfield(): void
    {
        $data = [
            'readonlyField' => 'Hydrator::build() can set read only fields',
        ];

        $object = Hydrator::build(SimpleClass::class, $data);

        self::assertInstanceOf(SimpleClass::class, $object);
        self::assertEquals('Hydrator::build() can set read only fields', $object->readonlyField);
    }

    public function testPathArgument(): void
    {
        $data = (object) [
            'simple-path' => 'simple',
            'array' => [
                'name' => 'John',
                'with.quotes' => 'array quotes',
            ],
            'object' => (object) [
                'name' => 'Company SA',
                'with.quotes' => 'object quotes',
            ],
        ];

        $object = new ClassWithPaths();
        Hydrator::hydrate($object, $data);

        self::assertEquals('simple', $object->simplePath);
        self::assertEquals('John', $object->pathThroughtArray);
        self::assertEquals('Company SA', $object->pathThroughtObject);
        self::assertEquals('array quotes', $object->arraywithQuotes);
        self::assertEquals('object quotes', $object->objectWithQuotes);
    }

    public function testTypedFields(): void
    {
        $data = (object) [
            'name' => 'John',
            'zip' => 13220,
            'age' => '33',
            'isAdult' => 1,
            'isVisible' => 0,
            'rate' => '1.23',
            'createdAt' => '2015/10/27 10:00:00',
            'position' => [
                'latitude' => '36.169941',
                'longitude' => '-115.139830',
            ],
        ];

        $object = new ClassWithTypes();
        Hydrator::hydrate($object, $data);

        self::assertEquals('John', $object->name);
        self::assertEquals('13220', $object->zip);
        self::assertEquals(33, $object->age);
        self::assertTrue($object->isAdult);
        self::assertFalse($object->isVisible);
        self::assertEquals(1.23, $object->rate);
        self::assertEquals(new \DateTimeImmutable('2015/10/27 10:00:00'), $object->createdAt);
        self::assertEquals(36.169941, $object->position->latitude);
        self::assertEquals(-115.139830, $object->position->longitude);
    }

    public function testCollections(): void
    {
        $data = (object) [
            'dates' => [
                '2015-10-27 10:00:00',
                '2015-10-28 10:00:00',
                '2015-12-01 10:00:00',
            ],
            'positions' => [
                [
                    'latitude' => 1,
                    'longitude' => 2,
                ],
                [
                    'latitude' => 3,
                    'longitude' => 4,
                ],
            ],
        ];

        $object = new ClassWithCollections();
        Hydrator::hydrate($object, $data);

        self::assertContainsOnlyInstancesOf(\DateTimeImmutable::class, $object->dates);
        self::assertContainsOnlyInstancesOf(Position::class, $object->positions);
        self::assertCount(3, $object->dates);
        self::assertEquals('27', $object->dates[0]->format('d'));
        self::assertEquals('28', $object->dates[1]->format('d'));
        self::assertEquals('01', $object->dates[2]->format('d'));
        self::assertCount(2, $object->positions);
        self::assertEquals(1, $object->positions[0]->latitude);
        self::assertEquals(2, $object->positions[0]->longitude);
        self::assertEquals(3, $object->positions[1]->latitude);
        self::assertEquals(4, $object->positions[1]->longitude);
    }

    public function testHydration(): void
    {
        $data = (object) [
            'country' => 'FR',
            'travels' => [
                (object) [
                    'length' => 55,
                    'dateOnly' => '2015-10-27',
                    'timeOnly' => '10:00:00',
                    'destination' => 'Las Vegas',
                    'travelers' => [
                        'Bob',
                        'Cathy',
                    ],
                ],
                (object) [
                    'length' => 11,
                    'dateOnly' => '2015-10-28',
                    'timeOnly' => '15:00:00',
                    'destination' => 'Toronto',
                    'travelers' => [
                        'Bob',
                        'Nancy',
                    ],
                ],
            ],
        ];

        $object = new ClassWithHydration();
        Hydrator::hydrate($object, $data);

        self::assertEquals('France', $object->country);
        self::assertEquals(66, $object->sumOfTravelLengths);
        self::assertContainsOnlyInstancesOf(\DateTimeImmutable::class, $object->dates);
        self::assertCount(2, $object->dates);
        self::assertEquals(new \DateTimeImmutable('2015-10-27 10:00:00'), $object->dates[0]);
        self::assertEquals(new \DateTimeImmutable('2015-10-28 15:00:00'), $object->dates[1]);
        self::assertContainsOnly('array', $object->travelers);
        self::assertCount(3, $object->travelers);
        self::assertCount(2, $object->travelers['Bob']);
        self::assertCount(1, $object->travelers['Cathy']);
        self::assertCount(1, $object->travelers['Nancy']);
    }

    public function testConstructorWithReadonlyPromotedProperties(): void
    {
        $data = (object) [
            'name' => 'Doe',
            'position' => [
                'latitude' => 55,
                'longitude' => 66,
            ],
            'positions' => [
                [
                    'latitude' => 51,
                    'longitude' => 61,
                ],
                [
                    'latitude' => 52,
                    'longitude' => 62,
                ],
                [
                    'latitude' => 53,
                    'longitude' => 63,
                ],
            ],
        ];

        $object = Hydrator::build(ClassWithConstructor::class, $data);

        self::assertInstanceOf(ClassWithConstructor::class, $object);
        self::assertEquals('Doe', $object->name);
        self::assertEquals(new ReadOnlyPosition(55, 66), $object->position);
        self::assertCount(3, $object->positions);
        self::assertEquals(new ReadOnlyPosition(51, 61), $object->positions[0]);
        self::assertEquals(new ReadOnlyPosition(52, 62), $object->positions[1]);
        self::assertEquals(new ReadOnlyPosition(53, 63), $object->positions[2]);
    }

    public function testBuildSimpleClassArray(): void
    {
        $data = [
            [
                'publicField' => 'value1',
            ],
            [
                'publicField' => 'value2',
            ],
        ];

        $values = Hydrator::buildArrayOf(SimpleClass::class, $data);

        self::assertCount(2, $values);
        self::assertContainsOnlyInstancesOf(SimpleClass::class, $values);
        self::assertEquals('value1', $values[0]->publicField);
        self::assertEquals('value2', $values[1]->publicField);
    }

    public function testNullValueForEmbedClassWontFail(): void
    {
        $data = [
            'nullablePosition' => null,
        ];

        $object = Hydrator::build(ClassWithTypes::class, $data);

        self::assertInstanceOf(ClassWithTypes::class, $object);
        self::assertNull($object->nullablePosition);
    }

    public function testDefaultPathfinder(): void
    {
        $data = [
            'public_field' => 'Public value',
        ];

        $object = Hydrator::build(SimpleClass::class, $data, CaseConverter::camelToSnake(...));

        self::assertInstanceOf(SimpleClass::class, $object);
        self::assertEquals('Public value', $object->publicField);
    }

    public function testHydratedFieldAttributeIsPriorToDefaultPathfinder(): void
    {
        $data = [
            'simple_path' => 'Simple_value',
            'simple-path' => 'Simple-value',
        ];

        $object = Hydrator::build(ClassWithPaths::class, $data, CaseConverter::camelToSnake(...));

        self::assertInstanceOf(ClassWithPaths::class, $object);
        self::assertEquals('Simple-value', $object->simplePath);
    }

    public function testFactoryFromRoot(): void
    {
        $data = [
           'title' => 'Title',
           'latitude' => '36.169941',
           'longitude' => '-115.139830',
        ];

        $object = Hydrator::build(FactoryFromRoot::class, $data);

        self::assertInstanceOf(FactoryFromRoot::class, $object);
        self::assertEquals('Title', $object->title);
        self::assertEquals(36.169941, $object->position->latitude);
        self::assertEquals(-115.139830, $object->position->longitude);
    }
}
