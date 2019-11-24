<?php
use Migrations\AbstractMigration;

class NullableDbFields extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_blog_posts` CHANGE `id_manufacturer` `id_manufacturer` int(11) unsigned NULL;
            ALTER TABLE `fcs_category` CHANGE `id_parent` `id_parent` int(10) unsigned NULL;
            ALTER TABLE `fcs_sliders` CHANGE `image` `image` varchar(255) NULL;"
        );
    }
}
