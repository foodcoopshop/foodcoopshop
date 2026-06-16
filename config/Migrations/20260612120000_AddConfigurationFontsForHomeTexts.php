<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddConfigurationFontsForHomeTexts extends BaseMigration
{
    public function change(): void
    {
        $this->execute("INSERT INTO fcs_configuration (name, active, value, type, position) VALUES ('FCS_FONT_HEADER_PROMO_TITLE',1,'fuzzy-bubbles','dropdown',3620);");
        $this->execute("INSERT INTO fcs_configuration (name, active, value, type, position) VALUES ('FCS_FONT_BLOCK_HEADING',1,'open-sans','dropdown',3630);");
    }
}
