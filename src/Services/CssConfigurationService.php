<?php
declare(strict_types=1);

namespace App\Services;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;

class CssConfigurationService
{

    public function format(string $css): string
    {
        $css = trim($css);
        if ($css === '') {
            return '';
        }

        try {
            $document = (new Parser($css))->parse();
        } catch (\Throwable) {
            return $css . "\n";
        }

        return trim($document->render(OutputFormat::createPretty())) . "\n";
    }

}