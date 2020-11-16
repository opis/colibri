<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
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

namespace Opis\Colibri\HTML;

use Opis\Colibri\Core\View;

class Script extends View
{
    /** @var bool  */
    protected $inBody = false;

    /**
     * Script constructor.
     */
    public function __construct()
    {
        parent::__construct('html.script', [
            'content' => null,
            'attributes' => new Attributes(),
        ]);
    }

    /**
     * @param bool $value
     * @return Script
     */
    public function inBody(bool $value = true): self
    {
        $this->inBody = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInBody(): bool
    {
        return $this->inBody;
    }

    /**
     * @param $content
     * @return Script
     */
    public function content(string $content): self
    {
        return $this->set('content', $content);
    }

    /**
     * @param string $value
     * @return Script
     */
    public function src(string $value): self
    {
        return $this->attribute('src', $value);
    }

    /**
     * @param string $name
     * @param null $value
     * @return Script
     */
    public function attribute(string $name, $value = null): self
    {
        /** @var Attributes $attributes */
        $attributes = $this->vars['attributes'];
        $attributes->add($name, $value);
        return $this;
    }

    /**
     * @param array $attributes
     * @return Script
     */
    public function attributes(array $attributes): self
    {
        foreach ($attributes as $name => $value) {
            if (is_numeric($name)) {
                $name = $value;
                $value = null;
            }
            $this->attribute($name, $value);
        }
        return $this;
    }
}
