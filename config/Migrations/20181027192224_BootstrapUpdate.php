<?php
use Migrations\AbstractMigration;

class BootstrapUpdate extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $this->execute("
            UPDATE fcs_manufacturer SET description = replace(description, 'btn-default', 'btn-outline-light');
            UPDATE fcs_blog_posts SET content = replace(content, 'btn-default', 'btn-outline-light');
            UPDATE fcs_pages SET content = replace(content, 'btn-default', 'btn-outline-light');
        ");
    }
}
