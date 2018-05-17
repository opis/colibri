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

namespace Opis\Colibri\Commands\Assets;

use Composer\IO\ConsoleIO;
use Opis\Colibri\Composer\ModuleInstaller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function Opis\Colibri\Functions\{
    info, app, module
};
use Symfony\Component\Filesystem\Filesystem;

class Build extends Command
{
    protected function configure()
    {
        $this
            ->setName('assets:build')
            ->setDescription("Build modules' assets")
            ->setAliases(['build-assets'])
            ->addArgument('module', InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'A list of modules separated by space')
            ->addOption('dependencies', null, InputOption::VALUE_NONE, 'Install/Uninstall asset dependencies');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output
            ->getFormatter()
            ->setStyle('b-info', new OutputFormatterStyle('yellow', null, ['bold']));

        $fs = new Filesystem();
        $installer = new ModuleInstaller(info(), new ConsoleIO($input, $output, new HelperSet()), app()->getComposer());
        $installer = $installer->getAssetsInstaller();

        $modules = $input->getArgument('module');
        $dependencies = $input->getOption('dependencies');

        if (empty($modules)) {
            $modules = app()->getModules();
        } else {
            $modules = array_map(function ($value) {
                return module($value);
            }, $modules);
        }

        /** @var \Opis\Colibri\Module $module */
        foreach ($modules as $module) {

            if (!$module->exists()) {
                continue;
            }

            if (!$module->assets()) {
                continue;
            }

            $output->writeln('<info>Building assets for <b-info>' . $module->name() . '</b-info> module...</info>');

            $name = str_replace('/', '.', $module->name());

            if ($dependencies) {
                $installer->uninstall($module->getPackage());
            } else {
                $fs->remove(info()->assetsDir() . DIRECTORY_SEPARATOR . $name);
            }

            if ($dependencies) {
                $installer->install($module->getPackage());
            } else {
                $fs->mirror($module->assets(), info()->assetsDir() . DIRECTORY_SEPARATOR . $name);
            }
        }
    }
}