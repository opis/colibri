<?php
/* ===========================================================================
 * Copyright 2018-2020 Zindex Software
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

class CSSCollection extends Collection
{
    public function url(string $href): static
    {
        return $this->add((new Link())->attributes(['href' => $href, 'rel' => 'stylesheet']), $href);
    }

    public function inline(string $content, ?string $media = null): static
    {
        return $this->add(new Style($content, $media), md5($content));
    }
}
