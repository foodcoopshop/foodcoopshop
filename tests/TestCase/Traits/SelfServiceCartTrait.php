<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait SelfServiceCartTrait
{

    private function addProductToSelfServiceCart($productId, $amount, $orderedQuantityInUnits = -1)
    {
        $this->getSelfServicePostOptions();
        $this->post(
            '/warenkorb/ajaxAdd/',
            [
                'productId' => $productId,
                'amount' => $amount,
                'orderedQuantityInUnits' => $orderedQuantityInUnits
            ],
        );
        return $this->getJsonDecodedContent();
    }

    private function getSelfServicePostOptions()
    {
        $this->configRequest([
            'headers' => [
                'X_REQUESTED_WITH' => 'XMLHttpRequest',
                'ACCEPT' => 'application/json',
                'REFERER' => Configure::read('App.fullBaseUrl') . '/' . __('route_self_service'),
            ],
        ]);
    }

    private function finishSelfServiceCart($generalTermsAndConditionsAccepted, $cancellationTermsAccepted)
    {
        $data = [
            'Carts' => [
                'general_terms_and_conditions_accepted' => $generalTermsAndConditionsAccepted,
                'cancellation_terms_accepted' => $cancellationTermsAccepted,
            ],
        ];
        $this->configRequest([
            'headers' => [
                'REFERER' => Configure::read('App.fullBaseUrl') . '/' . __('route_self_service'),
            ],
        ]);
        $this->post(
            $this->Slug->getSelfService(),
            $data,
        );
        $this->runAndAssertQueue();
    }

    private function removeProductFromSelfServiceCart($productId)
    {
        $this->getSelfServicePostOptions();
        $this->post(
            '/warenkorb/ajaxRemove/',
            [
                'productId' => $productId
            ],
        );
        return $this->getJsonDecodedContent();
    }

}
