<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

 class Product extends AppEntity {

    const ALLOWED_TAGS_DESCRIPTION       = '<p><b><strong><i><em><br><img>';
    const ALLOWED_TAGS_DESCRIPTION_SHORT = '<p><b><strong><i><em><br>';
    const ALLOWED_STATUSES = [APP_OFF, APP_ON];
    const NAME_SEPARATOR = ': ';

    protected array $_virtual = ['is_new'];

    public bool $nameSetterMethodEnabled = true;

    protected function _setName($value): string
    {
        if ($this->nameSetterMethodEnabled) {
            return StringComponent::removeSpecialChars(strip_tags(trim($value)));
        }
        return $value;
    }

    public function _setDescription($value): string
    {
        return StringComponent::prepareWysiwygEditorHtml($value, self::ALLOWED_TAGS_DESCRIPTION);
    }

    public function _setDescriptionShort($value): string
    {
        return StringComponent::prepareWysiwygEditorHtml($value, self::ALLOWED_TAGS_DESCRIPTION_SHORT);
    }

    public function _setUnity($value): string
    {
        return StringComponent::removeSpecialChars(strip_tags(trim($value)));
    }

    public function _getIsNew(): bool
    {
        if ($this->new === null) {
            return false;
        }

        return $this->new->addDays((int) Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW'))->isFuture();

    }

}
