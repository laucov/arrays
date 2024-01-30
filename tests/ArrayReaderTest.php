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

use Laucov\Arrays\ArrayReader;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Arrays\ArrayReader
 */
class ArrayReaderTest extends TestCase
{
    protected ArrayReader $reader;

    protected function setUp(): void
    {
        $this->reader = new ArrayReader([
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
     * @covers ::hasValue
     * @uses Laucov\Arrays\ArrayReader::__construct
     * @uses Laucov\Arrays\ArrayReader::validateKeys
     */
    public function testCanCheckValue(): void
    {
        // Test with single key.
        $this->assertSame(true, $this->reader->hasValue('message'));
        $this->assertSame(false, $this->reader->hasValue('foobar'));

        // Test with nested keys.
        $this->assertSame(true, $this->reader->hasValue(['user', 'name']));
        $this->assertSame(false, $this->reader->hasValue(['user', 'birth']));

        // Test if is not creating keys when referencing inexistent ones.
        $this->assertSame(
            false,
            $this->reader->hasValue(['user', 'birth', 'month']),
        );
        $this->assertSame(false, $this->reader->hasValue(['user', 'birth']));

        // Test with intermediary keys that are not arrays.
        $actual = $this->reader->hasValue(['user', 'name', 'first']);
        $this->assertSame(false, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getArray
     */
    public function testCanGetArray(): void
    {
        $input = [
            'foo' => 'bar',
        ];
        $reader = new ArrayReader($input);
        $output = $reader->getArray();
        $this->assertSame('bar', $output['foo']);
    }

    /**
     * @covers ::__construct
     * @covers ::getValue
     * @uses Laucov\Arrays\ArrayReader::validateKeys
     */
    public function testCanGetValue(): void
    {
        // Test with single key.
        $this->assertSame(
            'Hello, World!',
            $this->reader->getValue('message'),
        );

        // Test with nested keys.
        $this->assertSame(42, $this->reader->getValue(['user', 'age']));
    }

    /**
     * @covers ::hasValue
     * @covers ::validateKeys
     * @uses Laucov\Arrays\ArrayReader::__construct
     */
    public function testMustCheckWithValidKeys(): void
    {
        $this->reader->hasValue(['foo', 0, 'bar']);
        $this->expectException(\InvalidArgumentException::class);
        $this->reader->hasValue(['foo', ['bar', 'baz']]);
    }

    /**
     * @covers ::hasValue
     * @covers ::validateKeys
     * @uses Laucov\Arrays\ArrayReader::__construct
     */
    public function testMustCheckWithAtLeastOneKey(): void
    {
        $this->reader->hasValue(['foo']);
        $this->expectException(\InvalidArgumentException::class);
        $this->reader->hasValue([]);
    }

    /**
     * @covers ::getValue
     * @covers ::validateKeys
     * @uses Laucov\Arrays\ArrayReader::__construct
     */
    public function testMustGetWithValidKeys(): void
    {
        $this->reader->getValue(['foo', 0, 'bar']);
        $this->expectException(\InvalidArgumentException::class);
        $this->reader->getValue(['foo', ['bar', 'baz']]);
    }

    /**
     * @covers ::getValue
     * @covers ::validateKeys
     * @uses Laucov\Arrays\ArrayReader::__construct
     */
    public function testMustGetWithAtLeastOneKey(): void
    {
        $this->reader->getValue(['foo']);
        $this->expectException(\InvalidArgumentException::class);
        $this->reader->getValue([]);
    }
}
