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

namespace Opis\Colibri\Collectors;

/**
 * Collects dispatchers
 */
interface DispatcherCollectorInterface
{

    /**
     * Register a new dispatcher
     *
     * @param   string $name Dispatcher's name
     * @param   callable $builder A callback that will return an instance of \Opis\Routing\Dispatcher
     *
     * @return  self  Self reference
     */
    public function register($name, $builder);
}
