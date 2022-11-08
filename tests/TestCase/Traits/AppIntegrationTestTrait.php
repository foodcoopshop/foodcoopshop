<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use Cake\TestSuite\IntegrationTestTrait;

trait AppIntegrationTestTrait
{
    use IntegrationTestTrait;

    public function ajaxPost($url, $data = []): void
    {
        $this->configRequest([
            'headers' => [
                'X_REQUESTED_WITH' => 'XMLHttpRequest',
                'ACCEPT' => 'application/json',
            ],
        ]);
        $this->post($url, $data);
    }

}

?>