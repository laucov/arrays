<?php

/**
 * This file is part of Laucov's Array Library project.
 * 
 * Copyright 2024 Laucov Serviços de Tecnologia da Informação Ltda.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @package arrays
 * 
 * @author Rafael Covaleski Pereira <rafael.covaleski@laucov.com>
 * 
 * @license <http://www.apache.org/licenses/LICENSE-2.0> Apache License 2.0
 * 
 * @copyright © 2024 Laucov Serviços de Tecnologia da Informação Ltda.
 */

declare(strict_types=1);

namespace Tests\Http;

use Laucov\Arrays\ArrayBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Arrays\ArrayBuilder
 */
class ArrayBuilderTest extends TestCase
{
    protected ArrayBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new ArrayBuilder([
            'user' => [
                'name' => 'John Doe',
                'age' => 42,
                'email' => 'john.doe@example.com',
            ],
            'message' => 'Hello, World!',
            'date' => new \DateTime('1970-01-01 12:00:00'),
        ]);
    }

    /**
     * @coversNothing
     * @uses Laucov\Arrays\ArrayBuilder::__construct
     * @uses Laucov\Arrays\ArrayBuilder::getValue
     * @uses Laucov\Arrays\ArrayBuilder::removeValue
     * @uses Laucov\Arrays\ArrayBuilder::setValue
     * @uses Laucov\Arrays\ArrayBuilder::validateKeys
     */
    public function testCanChainMethods(): void
    {
        $this->assertSame(
            $this->builder,
            $this->builder->setValue('ip_address', '192.168.0.2')
        );
        $this->assertSame(
            $this->builder,
            $this->builder->setValue(['user', 'id'], 123)
        );
        $this->assertSame(
            $this->builder,
            $this->builder->removeValue('message'),
        );
        $this->assertSame(
            $this->builder,
            $this->builder->removeValue(['user', 'email']),
        );
    }

    /**
     * @covers ::removeValue
     * @uses Laucov\Arrays\ArrayBuilder::__construct
     * @uses Laucov\Arrays\ArrayBuilder::getValue
     * @uses Laucov\Arrays\ArrayBuilder::validateKeys
     */
    public function testCanRemoveValue(): void
    {
        // Test with single key.
        $this->builder->removeValue('message');
        $actual = $this->builder->getValue('message', 'undefined');
        $this->assertSame('undefined', $actual);

        // Test with nested keys.
        $this->builder->removeValue(['user', 'age']);
        $actual = $this->builder->getValue(['user', 'age'], 'undefined');
        $this->assertSame('undefined', $actual);

        // Test with keys that don't exist.
        $this->builder->removeValue(['user', 'roles', 0]);
        $actual = $this->builder->getValue(['user', 'roles', 0], 'undefined');
        $this->assertSame('undefined', $actual);
        // Test if is not creating keys when referencing inexistent ones.
        $actual = $this->builder->getValue(['user', 'roles'], 'undefined');
        $this->assertSame('undefined', $actual);

        // Test with intermediary keys that are not arrays.
        $this->builder->removeValue(['user', 'name', 'first']);
        $actual = $this->builder->getValue(['user', 'name', 'first'], '-');
        $this->assertSame('-', $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::setValue
     * @uses Laucov\Arrays\ArrayBuilder::getValue
     * @uses Laucov\Arrays\ArrayBuilder::validateKeys
     */
    public function testCanSetValue(): void
    {
        // Test with single key.
        $this->builder->setValue('message', 'Hello, Earth!');
        $actual = $this->builder->getValue('message');
        $this->assertSame('Hello, Earth!', $actual);

        // Test with nested keys.
        $this->builder->setValue(['user', 'age'], 58);
        $actual = $this->builder->getValue(['user', 'age']);
        $this->assertSame(58, $actual);

        // Test with keys that don't exist.
        // Will succeed as long as `::setValue` uses references.
        $this->builder->setValue(['user', 'websites', 0], 'john-doe.com');
        $actual = $this->builder->getValue(['user', 'websites', 0]);
        $this->assertSame('john-doe.com', $actual);

        // Test overriding intermediary keys that are not arrays.
        $this->builder->setValue(['user', 'name', 'first'], 'John');
        $actual = $this->builder->getValue(['user', 'name', 'first']);
        $this->assertSame('John', $actual);
    }

    /**
     * @covers ::removeValue
     * @covers ::validateKeys
     * @uses Laucov\Arrays\ArrayBuilder::__construct
     */
    public function testMustRemoveWithValidKeys(): void
    {
        $this->builder->removeValue(['foo', 0, 'bar']);
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->removeValue(['foo', ['bar', 'baz']]);
    }

    /**
     * @covers ::removeValue
     * @covers ::validateKeys
     * @uses Laucov\Arrays\ArrayBuilder::__construct
     */
    public function testMustRemoveWithAtLeastOneKey(): void
    {
        $this->builder->removeValue(['foo']);
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->removeValue([]);
    }

    /**
     * @covers ::setValue
     * @covers ::validateKeys
     * @uses Laucov\Arrays\ArrayBuilder::__construct
     */
    public function testMustSetWithValidKeys(): void
    {
        $this->builder->setValue(['foo', 0, 'bar'], 'baz');
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->setValue(['foo', ['bar', 'baz']], 'baz');
    }

    /**
     * @covers ::setValue
     * @covers ::validateKeys
     * @uses Laucov\Arrays\ArrayBuilder::__construct
     */
    public function testMustSetWithAtLeastOneKey(): void
    {
        $this->builder->setValue(['foo'], 'bar');
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->setValue([], 'bar');
    }

    /**
     * @covers ::getValue
     * @uses Laucov\Arrays\ArrayBuilder::__construct
     * @uses Laucov\Arrays\ArrayBuilder::validateKeys
     */
    public function testReturnsDefaultValues(): void
    {
        // Test with default fallback value.
        $this->assertNull($this->builder->getValue('id'));
        $this->assertNull($this->builder->getValue(['user', 'id']));
        $this->assertNull($this->builder->getValue(['date', 'id']));

        // Test with custom fallback value.
        $default_value = 'Not found';
        $this->assertSame(
            $default_value,
            $this->builder->getValue('id', $default_value),
        );
        $this->assertSame(
            $default_value,
            $this->builder->getValue(['user', 'id'], $default_value),
        );
        $this->assertSame(
            $default_value,
            $this->builder->getValue(['date', 'id'], $default_value),
        );
    }
}
