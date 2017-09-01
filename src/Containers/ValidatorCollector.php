<?php
/* ===========================================================================
 * Copyright 2013-2016 The Opis Project
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

namespace Opis\Colibri\Containers;

use Opis\Colibri\CollectingContainer;
use Opis\Colibri\Serializable\CallbackList;

/**
 * Class ValidatorCollector
 * @package Opis\Colibri\Containers
 * @method CallbackList data()
 */
class ValidatorCollector extends CollectingContainer
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(array());
    }


    /**
     * @param string $name
     * @param string $class
     * @return $this
     */
    public function register($name, $class)
    {
        if (class_exists($class)) {
            $this->dataObject[$name] = $class;
        }

        return $this;
    }
}