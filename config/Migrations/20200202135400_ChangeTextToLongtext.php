<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ChangeTextToLongtext extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE fcs_blog_posts CHANGE content content LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
            ALTER TABLE fcs_manufacturer CHANGE description description LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci;
            ALTER TABLE fcs_pages CHANGE content content LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
            ALTER TABLE fcs_product CHANGE description description LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci;
        ");
    }
}
