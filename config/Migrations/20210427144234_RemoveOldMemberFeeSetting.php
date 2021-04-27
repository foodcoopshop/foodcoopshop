<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveOldMemberFeeSetting extends AbstractMigration
{
    public function change()
    {
        $this->execute("DELETE FROM fcs_action_logs WHERE type IN('payment_member_fee_added','payment_member_fee_deleted','payment_member_fee_flexible_added');");
        $this->execute("DELETE FROM fcs_payments WHERE type IN('member_fee','member_fee_flexible');");
        $this->execute("DELETE FROM fcs_configuration WHERE NAME = 'FCS_MEMBER_FEE_BANK_ACCOUNT_DATA';");
    }
}
