<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class EnsureUtf8mb4ForBlocks extends BaseMigration
{
    public function change(): void
    {
        $this->execute("ALTER TABLE fcs_blocks CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;");
        $this->execute("ALTER TABLE fcs_header_promos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;");
    }
}
