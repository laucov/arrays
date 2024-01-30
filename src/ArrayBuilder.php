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

namespace Laucov\Arrays;

/**
 * Controls the contents of an array.
 */
class ArrayBuilder extends ArrayReader
{
    /**
     * Stored array.
     */
    protected array $array;

    /**
     * Remove a value.
     */
    public function removeValue(int|string|array $keys): static
    {
        // Resolve single key.
        if (!is_array($keys)) {
            unset($this->array[$keys]);
            return $this;
        }

        // Check keys.
        $keys = $this->validateKeys($keys);

        // Get the last key.
        $last_key = array_pop($keys);

        // Find/fill intermediary keys.
        $array = &$this->array;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array) || !is_array($array[$key])) {
                return $this;
            }
            $array = &$array[$key];
        }

        // Remove value.
        unset($array[$last_key]);

        return $this;
    }

    /**
     * Set a value.
     */
    public function setValue(int|string|array $keys, mixed $value): static
    {
        // Resolve single key.
        if (!is_array($keys)) {
            $this->array[$keys] = $value;
            return $this;
        }

        // Check keys.
        $keys = $this->validateKeys($keys);

        // Get the last key.
        $last_key = array_pop($keys);

        // Find/fill intermediary keys.
        $array = &$this->array;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }

        // Set value.
        $array[$last_key] = $value;

        return $this;
    }
}
