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

use InvalidArgumentException;
use Opis\Colibri\Application;
use Opis\Colibri\Collectors\AbstractCollector;
use Opis\Colibri\Collectors\CoreMethodCollectorInterface;
use Opis\Colibri\Serializable\CallbackList;

class CoreMethodCollector extends AbstractCollector implements CoreMethodCollectorInterface
{

    /**
     * Constructor
     *
     * @param   Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app, new CallbackList());
    }

    /**
     * Register a new command
     *
     * @param   string $name Method's name
     * @param   callable $callback Callback
     *
     * @return  self   Self reference
     */
    public function register($name, $callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException();
        }

        $this->dataObject->add($name, $callback);
        return $this;
    }
}
