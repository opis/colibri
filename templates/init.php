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

use Opis\Colibri\{
    ApplicationInitializer,
    ApplicationContainer
};

use Opis\Cache\Drivers\File as CacheDriver;
use Opis\DataStore\Drivers\JSONFile as ConfigDriver;
use Opis\I18n\Translator\Drivers\JsonFile as TranslatorDriver;

return new class implements ApplicationInitializer
{
    /**
     * @inheritDoc
     */
    public function init(ApplicationContainer $app)
    {
        // Timezone settings
        date_default_timezone_set('UTC');

        $dir = $app->getAppInfo()->writableDir();

        $app->setCacheDriver(new CacheDriver($dir . DIRECTORY_SEPARATOR . 'cache'))
            ->setConfigDriver(new ConfigDriver($dir . DIRECTORY_SEPARATOR . 'config', '', true))
            ->setTranslatorDriver(new TranslatorDriver($dir . DIRECTORY_SEPARATOR . 'intl'));

        // Setup database connection
        // $connection = new \Opis\Database\Connection('dsn', 'user', 'password');
        // $app->setDatabaseConnection($connection);
    }
};