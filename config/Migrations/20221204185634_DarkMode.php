<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class DarkMode extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('fcs_customer');
        $table->addColumn('dark_mode_enabled', 'tinyinteger', [
            'default' => '0',
            'limit' => null,
            'null' => true,
            'signed' => false,
        ]);
        $table->update();
    }
}
