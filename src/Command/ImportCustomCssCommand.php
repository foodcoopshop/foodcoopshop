<?php
declare(strict_types=1);

namespace App\Command;

use App\Services\CssConfigurationService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

class ImportCustomCssCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $customCssFiles = [
            WWW_ROOT . 'css' . DS . 'custom.css',
            WWW_ROOT . 'css' . DS . 'mobile-frontend-custom.css',
        ];

        $cssParts = [];
        $existingCustomCssFiles = [];
        foreach($customCssFiles as $customCssFile) {
            if (!file_exists($customCssFile)) {
                continue;
            }

            $css = file_get_contents($customCssFile);
            if ($css === false) {
                $io->err('Could not read ' . basename($customCssFile) . '.');
                return static::CODE_ERROR;
            }

            $cssParts[] = $css;
            $existingCustomCssFiles[] = $customCssFile;
        }

        if ($existingCustomCssFiles === []) {
            $io->out('No custom CSS files found.');
            return static::CODE_SUCCESS;
        }

        $configurationsTable = $this->getTableLocator()->get('Configurations');
        $configuration = $configurationsTable->get('FCS_CUSTOM_CSS');
        if ($configuration->value != '') {
            array_unshift($cssParts, $configuration->value);
        }

        $css = (new CssConfigurationService())->format(implode("\n\n", $cssParts));

        $configuration->value = $css;
        if (!$configurationsTable->save($configuration)) {
            $io->err('Could not save FCS_CUSTOM_CSS.');
            return static::CODE_ERROR;
        }

        foreach($existingCustomCssFiles as $customCssFile) {
            if (!unlink($customCssFile)) {
                $io->err('Could not delete ' . basename($customCssFile) . '.');
                return static::CODE_ERROR;
            }
        }

        $configurationsTable->loadConfigurations();
        $io->out('Imported custom CSS files to FCS_CUSTOM_CSS and deleted the files.');
        return static::CODE_SUCCESS;
    }

}