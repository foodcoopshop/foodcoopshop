<?php
declare(strict_types=1);

use Migrations\AbstractSeed;
use Cake\TestSuite\Fixture\FixtureHelper;
use App\Test\Fixture\AppFixture;

class InitTestDataSeed extends AbstractSeed
{
    public function run(): void
    {
        $fixtureHelper = new FixtureHelper();
        $fixtures = $fixtureHelper->loadFixtures(AppFixture::IMPLEMENTED_FIXTURES);
        $fixtureHelper->truncate($fixtures);
        $fixtureHelper->insert($fixtures);
    }
}
