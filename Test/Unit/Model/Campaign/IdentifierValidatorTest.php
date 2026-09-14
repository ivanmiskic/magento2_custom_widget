<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Test\Unit\Model\Campaign;

use Null\Blueprint\Model\Campaign\IdentifierValidator;
use PHPUnit\Framework\TestCase;

class IdentifierValidatorTest extends TestCase
{
    private IdentifierValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new IdentifierValidator();
    }

    /**
     * @dataProvider validProvider
     */
    public function testValidIdentifiers(string $identifier): void
    {
        $this->assertTrue($this->validator->isValid($identifier));
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalidIdentifiers(string $identifier): void
    {
        $this->assertFalse($this->validator->isValid($identifier));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function validProvider(): array
    {
        return [
            'simple' => ['summer-sale'],
            'digits' => ['campaign-2026'],
            'single' => ['a'],
        ];
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidProvider(): array
    {
        return [
            'empty' => [''],
            'uppercase' => ['Summer-Sale'],
            'underscore' => ['summer_sale'],
            'leading-hyphen' => ['-summer'],
            'trailing-hyphen' => ['summer-'],
            'space' => ['summer sale'],
            'too-long' => [str_repeat('a', 65)],
        ];
    }
}
