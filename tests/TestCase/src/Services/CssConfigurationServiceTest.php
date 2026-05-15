<?php
declare(strict_types=1);

use App\Services\CssConfigurationService;
use PHPUnit\Framework\TestCase;

class CssConfigurationServiceTest extends TestCase
{

    public function testValidateAndFormat(): void
    {
        $css = (new CssConfigurationService())->format('body{color:red}');
        $this->assertStringContainsString('body {', $css);
        $this->assertStringContainsString('color: red;', $css);
    }

    public function testEmptyCss(): void
    {
        $this->assertSame('', (new CssConfigurationService())->format(''));
    }

}