<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use Cake\TestSuite\IntegrationTestTrait;

trait AppIntegrationTestTrait
{

    use IntegrationTestTrait;

    private $ajaxHeaders = [
        'headers' => [
            'X_REQUESTED_WITH' => 'XMLHttpRequest',
            'ACCEPT' => 'application/json',
        ],
    ];

    public function ajaxGet($url): void
    {
        $this->configRequest($this->ajaxHeaders);
        $this->get($url);
    }

    public function ajaxPost($url, $data = []): void
    {
        $this->configRequest($this->ajaxHeaders);
        $this->post($url, $data);
    }

}
?>