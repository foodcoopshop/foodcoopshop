<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * TestData seed.
 */
class InitTestDataSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $query = file_get_contents(TESTS . 'config' . DS . 'sql' . DS . 'test-db-data.sql');
        $this->execute($query);
    }
}
