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

namespace Opis\Colibri;

use Opis\Events\Event as BaseEvent;

class Event extends BaseEvent
{
    /** @var    Application */
    protected $app;

    /**
     *
     * @param   Application $app
     * @param   string $name
     * @param   boolean $cancelable (optional)
     */
    public function __construct(Application $app, $name, $cancelable = false)
    {
        $this->app = $app;
        parent::__construct($name, $cancelable);
    }

    /**
     * Get the app
     *
     * @return  \Opis\Colibri\Application
     */
    public function app()
    {
        return $this->app;
    }
}
