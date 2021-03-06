<?php
/* ===========================================================================
 * Copyright 2021 Zindex Software
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

namespace Opis\Colibri\Commands\Yarn;

use Symfony\Component\Console\{Command\Command, Input\InputArgument, Input\InputInterface, Output\OutputInterface};
use function Opis\Colibri\{info, module};

class Remove extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('yarn:remove')
            ->setDescription('Remove module assets from package.json')
            ->addArgument('module', InputArgument::REQUIRED, "Module's name");
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = module(trim($input->getArgument('module')));

        if (!$module->exists()) {
            $output->writeln('<error>Module does not exist</error>');
            return 1;
        }

        if (null === $assets = $module->assets()) {
            $output->writeln('<error>Module does not provide assets</error>');
            return 1;
        }

        $package = json_decode(file_get_contents($assets . '/package.json'))->name;

        $cwd = getcwd();
        chdir(info()->rootDir());
        passthru('yarn remove ' . $package);
        chdir($cwd);

        return 0;
    }
}
