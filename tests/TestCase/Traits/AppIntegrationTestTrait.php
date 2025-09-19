<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use Cake\TestSuite\IntegrationTestTrait;

trait AppIntegrationTestTrait
{

    use IntegrationTestTrait;

    /**
     * @var array<string, array<string, string>>
     */
    private array $ajaxHeaders = [
        'headers' => [
            'X_REQUESTED_WITH' => 'XMLHttpRequest',
            'ACCEPT' => 'application/json',
        ],
    ];

    public function ajaxGet(string $url): void
    {
        $this->configRequest($this->ajaxHeaders);
        $this->get($url);
    }

    /**
     * @param array<mixed> $data
     */
    public function ajaxPost(string $url, array $data = []): void
    {
        $this->configRequest($this->ajaxHeaders);
        $this->post($url, $data);
    }

}
?>