<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class BlogPostShowOnStartPageUntilDate extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_blog_posts` ADD `show_on_start_page_until` DATE NULL DEFAULT NULL AFTER `is_featured`;");
        $this->execute("UPDATE `fcs_blog_posts` SET `show_on_start_page_until` = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE `active` = 1 AND `is_featured` = 1;");
        $this->execute("ALTER TABLE `fcs_blog_posts` DROP `is_featured`;");
    }
}
