<?php

namespace Beesofts\Hydrator\Tests;

use Beesofts\Hydrator\CaseConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CaseConverter::class)]
class CaseConverterTest extends TestCase
{
    public static function camelToSnakeCasesProvider(): iterable
    {
        return [
            [
                'camel_case',
                'camelCase',
            ],
            [
                'ca_m_el_case',
                'caMElCase',
            ],
            [
                'camel case',
                'camel case',
            ],
        ];
    }

    #[DataProvider('camelToSnakeCasesProvider')]
    public function testCamelToSnake(string $expected, string $input): void
    {
        self::assertEquals($expected, CaseConverter::camelToSnake($input));
    }

    public static function snakeToCamelCasesProvider(): iterable
    {
        return [
            [
                'camelCase',
                'camel_case',
            ],
            [
                'caMElCase',
                'ca_m_el_case',
            ],
            [
                'camelCase',
                'camel case',
            ],
        ];
    }

    #[DataProvider('snakeToCamelCasesProvider')]
    public function testSnakeToCamel(string $expected, string $input): void
    {
        self::assertEquals($expected, CaseConverter::snakeToCamel($input));
    }
}
