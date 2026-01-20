<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddCompositeCartIndex extends BaseMigration
{
    public function change(): void
    {
        $this->execute('CREATE INDEX idx_cart_status_type ON fcs_carts (status, cart_type)');
    }
}
