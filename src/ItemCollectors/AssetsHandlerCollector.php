<?php
/* ===========================================================================
 * Copyright 2014-2017 The Opis Project
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

namespace Opis\Colibri\ItemCollectors;

use Opis\Colibri\ItemCollector;
use Opis\Colibri\Serializable\CallbackList;

/**
 * @property CallbackList $data
 */
class AssetsHandlerCollector extends ItemCollector
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(new CallbackList());
    }

    /**
     * @param string $name
     * @param callable $callback
     * @return self
     */
    public function register(string $name, callable $callback): self
    {
        $this->data->add($name, $callback);
        return $this;
    }

    /**
     * @param callable $callback
     * @return AssetsHandlerCollector
     */
    public function globalHandler(callable $callback): self
    {
        return $this->register('*', $callback);
    }
}