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

namespace Opis\Colibri\Test;

use Opis\Colibri\Application;
use Opis\Http\Request;
use Opis\Http\Response;
use PHPUnit\Framework\TestCase;

class BaseClass extends TestCase
{
    /** @var Application */
    protected $app;

    public function setUp()
    {
        $this->app = include __DIR__ . '/app/app.php';
    }

    /**
     * @param string $path
     * @param string $method
     * @param bool $secure
     * @param array $input
     * @param array $server
     * @return Response
     */
    protected function exec(
        string $path,
        string $method = 'GET',
        bool $secure = false,
        array $input = [],
        array $server = []
    ) {
        $server['HTTPS'] = $secure ? 'on' : 'off';
        $request = Request::create($path, $method, $input, [], [], $server);
        return $this->app->run($request);
    }
}