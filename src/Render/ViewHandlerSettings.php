<?php
/* ===========================================================================
 * Copyright 2020 Zindex Software
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Colibri\Render;

interface ViewHandlerSettings
{
    public function implicit(string $name, mixed $value): self;

    public function filter(callable $callback): self;

    public function where(string $name, string $regex): self;

    /**
     * @param string $name
     * @param string[] $values
     * @return $this
     */
    public function whereIn(string $name, array $values): self;
}