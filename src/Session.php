<?php
/* ===========================================================================
 * Copyright 2019 Zindex Software
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

use Opis\Session\ISessionHandler;
use Opis\Session\Session as BaseSession;
use function Opis\Colibri\Functions\app;

class Session extends BaseSession
{
    public function __construct(array $config = [], ISessionHandler $handler = null)
    {
        parent::__construct($config, $handler, app()->getSessionCookieContainer());
    }
}