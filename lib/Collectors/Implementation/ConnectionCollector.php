<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2014-2016 Marius Sarca
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

namespace Opis\Colibri\Collectors\Implementation;

use Closure;
use Opis\Colibri\Application;
use Opis\Colibri\Collectors\AbstractCollector;
use Opis\Colibri\Collectors\ConnectionCollectorInterface;
use Opis\Colibri\Serializable\ConnectionList;

class ConnectionCollector extends AbstractCollector implements ConnectionCollectorInterface
{

    /**
     * Constructor
     *
     * @param   Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app, new ConnectionList());
    }

    /**
     * Defines a new database connection
     *
     * @param   string $name Connection name
     * @param   \Closure $callback Connection constructor callback
     * @param   boolean $default (optional) Default flag
     *
     * @return  self Self reference
     */
    public function create($name, Closure $callback, $default = false)
    {
        $this->dataObject->set($name, $callback($this->app), $default);
        return $this;
    }
}
