<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) FoodCoopShop, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Test\Fixture;

class HeaderPromosFixture extends AppFixture
{

    public string $table = 'fcs_header_promos';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $records = [
        [
            'id' => 1,
            'page_id' => 9999,
            'title' => 'Demo Header Promo',
            'lead_text' => 'Subtitle for tests',
            'text' => '<p>Demo text</p>',
            'primary_label' => 'Mehr',
            'primary_href' => 'https://example.com/anmelden',
            'secondary_label' => 'Kontakt',
            'secondary_href' => 'https://example.com/kontakt',
            'modified' => '2026-06-10 10:00:00',
            'created' => '2026-06-10 10:00:00',
        ],
    ];

}
