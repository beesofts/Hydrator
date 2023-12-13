<?php

namespace Beesofts\Hydrator\Tests;

use Beesofts\Hydrator\DataBag;
use Beesofts\Hydrator\Exception\InvalidPathException;
use Beesofts\Hydrator\Exception\NoDataForPathException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DataBag::class)]
class DataBagTest extends TestCase
{
    public static function invalidKeyCasesProvider(): iterable
    {
        return [
            'empty key' => [''],
            'unclosed quote' => ['"key'],
            'unopened quote' => ['key"'],
            'starting with empty key' => ['.key'],
            'ending with empty key' => ['key.'],
            'starting with empty quoted key' => ['"".key'],
            'ending with empty quoted key' => ['key.""'],
            'empty key in the middle' => ['key1..key2'],
            'empty quoted key in the middle' => ['key1."".key2'],
            'missing dots' => ['key1"key2"key3'],
            'mismatch in quotes' => ['key1."key2."key3'],
        ];
    }

    #[DataProvider('invalidKeyCasesProvider')]
    public function testInvalidKey(string $key): void
    {
        $reflectionMethod = new \ReflectionMethod(DataBag::class, 'validatePath');

        self::expectException(InvalidPathException::class);
        $reflectionMethod->invoke(null, $key);
    }

    public static function resolveKeyCasesProvider(): iterable
    {
        yield 'single key' => [
            ['key'],
            'key',
        ];

        yield 'simpest dual key' => [
            ['key1', 'key2'],
            'key1.key2',
        ];

        yield 'quotes 1' => [
            ['key1.key2'],
            '"key1.key2"',
        ];

        yield 'quotes 2' => [
            ['key1.key2', 'key3'],
            '"key1.key2".key3',
        ];

        yield 'quotes 3' => [
            ['key1', 'key2.key3'],
            'key1."key2.key3"',
        ];

        yield 'quotes 4' => [
            ['key1', 'key2.key3', 'key4'],
            'key1."key2.key3".key4',
        ];

        yield 'quotes 5' => [
            ['key1', '.key2.', 'key3'],
            'key1.".key2.".key3',
        ];

        yield 'quotes 6' => [
            ['key1', 'key.2', 'key.3', 'key4'],
            'key1."key.2"."key.3".key4',
        ];

        yield 'case sensitivity' => [
            ['KEY1', 'KEY2'],
            'KEY1.KEY2',
        ];

        yield 'all characters are allowed' => [
            ['key-1', 'key_2', 'key->3', '@key4', 'key\'5'],
            'key-1.key_2.key->3.@key4.key\'5',
        ];
    }

    /**
     * @param string[] $expected
     */
    #[DataProvider('resolveKeyCasesProvider')]
    public function testResolveKey(array $expected, string $key): void
    {
        $reflectionMethod = new \ReflectionMethod(DataBag::class, 'resolvePath');

        self::assertEquals($expected, $reflectionMethod->invoke(null, $key));
    }

    public static function inexistantDataCasesProvider(): iterable
    {
        $data = [
            'location' => [
                'city' => 'Las Vegas',
            ],
            'company' => (object) [
                'name' => 'Silicium SA',
            ],
        ];

        return [
            [$data, 'dont-exists'],
            [$data, 'dont-exists.nor-exists'],
            [$data, 'location.dont-exists'],
            [$data, 'company.dont-exists'],
        ];
    }

    #[DataProvider('inexistantDataCasesProvider')]
    public function testInexistantData(array $data, string $path): void
    {
        $dataBag = new DataBag($data);

        self::expectException(NoDataForPathException::class);
        $dataBag->get($path);
    }

    public function testWithObject(): void
    {
        $originalData = (object) [
            'name' => 'John',
            'location' => [
                'city' => 'Las Vegas',
            ],
            'company' => (object) [
                'name' => 'Silicium SA',
            ],
            'many-values' => [
                1,
                2,
                3,
            ],
            'key.with.dots' => 'value.with.dots',
        ];

        $dataBag = new DataBag($originalData);

        self::assertEquals('John', $dataBag->get('name'));
        self::assertEquals('Las Vegas', $dataBag->get('location.city'));
        self::assertEquals('Silicium SA', $dataBag->get('company.name'));
        self::assertEquals((object) ['name' => 'Silicium SA'], $dataBag->get('company'));
        self::assertEquals([1, 2, 3], $dataBag->get('many-values'));
        self::assertEquals('value.with.dots', $dataBag->get('"key.with.dots"'));
    }

    public function testWithArray(): void
    {
        $originalData = [
            'name' => 'John',
            'location' => [
                'city' => 'Las Vegas',
            ],
            'company' => (object) [
                'name' => 'Silicium SA',
            ],
            'many-values' => [
                1,
                2,
                3,
            ],
            'key.with.dots' => 'value.with.dots',
        ];

        $dataBag = new DataBag($originalData);

        self::assertEquals('John', $dataBag->get('name'));
        self::assertEquals('Las Vegas', $dataBag->get('location.city'));
        self::assertEquals('Silicium SA', $dataBag->get('company.name'));
        self::assertEquals((object) ['name' => 'Silicium SA'], $dataBag->get('company'));
        self::assertEquals([1, 2, 3], $dataBag->get('many-values'));
        self::assertEquals('value.with.dots', $dataBag->get('"key.with.dots"'));
    }

    public static function rootPathCasesProvider(): iterable
    {
        return [
            [
                [
                    'foo' => 'bar',
                    'bim' => 'bam',
                ],
            ],
            [
                (object) [
                    'foo' => 'bar',
                    'bim' => 'bam',
                ],
            ],
        ];
    }

    #[DataProvider('rootPathCasesProvider')]
    public function testRootPath(object|array $data): void
    {
        $data = [
            'foo' => 'bar',
            'bim' => 'bam',
        ];

        $dataBag = new DataBag($data);

        self::assertEquals($data, $dataBag->get('*'));
    }
}
