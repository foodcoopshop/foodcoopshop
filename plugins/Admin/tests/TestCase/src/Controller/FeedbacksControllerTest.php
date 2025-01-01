<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5s.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;

class FeedbacksControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->changeConfiguration('FCS_USER_FEEDBACK_ENABLED', 1);
    }

    public function testSaveFeedbackNotCorrectText(): void
    {
        $this->loginAsCustomer();
        $text = 'This';
        $privacyType = 20;
        $this->doPostMyFeedbackForm($text, $privacyType, false);
        $this->assertResponseContains('Bitte gib zwischen 10 und 1.000 Zeichen ein.');
    }

    public function testSaveFeedbackAsCustomerAndApproveBySuperadmin(): void
    {

        $this->loginAsCustomer();
        $text = 'This is my feedback';
        $privacyType = 20;
        $this->doPostMyFeedbackForm($text, $privacyType, false);
        $this->assertFlashMessage('Dein Feedback wurde erstellt.');

        $this->get($this->Slug->getFeedbackList());
        $this->assertResponseNotContains($text);

        // never approve, even if feedback is edited
        $this->doPostMyFeedbackForm($text, 10, false);
        $this->get($this->Slug->getFeedbackList());
        $this->assertResponseNotContains($text);

        $this->loginAsSuperadmin();
        $this->doPostFeedbackForm(Configure::read('test.customerId'), $text, $privacyType, true);
        $this->assertFlashMessage('Das Feedback von <b>Demo Mitglied</b> wurde geändert.');

        $this->get($this->Slug->getFeedbackList());
        $this->assertResponseContains($text);
        $this->assertResponseContains('Demo, Scharnstein');
        $this->assertResponseContains('Feedback von Mitgliedern');

    }

    public function testSaveFeedbackAsManufacturerAndApproveBySuperadmin(): void
    {
        $this->loginAsMeatManufacturer();
        $text = 'This is my feedback';
        $privacyType = 10;
        $this->doPostMyFeedbackForm($text, $privacyType, false);
        $this->assertFlashMessage('Dein Feedback wurde erstellt.');

        $this->get($this->Slug->getFeedbackList());
        $this->assertResponseNotContains($text);

        $this->loginAsSuperadmin();
        $this->doPostFeedbackForm(Configure::read('test.meatManufacturerId'), $text, $privacyType, true);
        $this->assertFlashMessage('Das Feedback von <b>Demo Fleisch-Hersteller</b> wurde geändert.');

        $this->get($this->Slug->getFeedbackList());
        $this->assertResponseContains($text);
        $this->assertResponseContains('Demo Fleisch-Hersteller, Scharnstein');
        $this->assertResponseContains('Feedback von Herstellern');
    }

    public function testSaveAndDeleteFeedbackByAdmin(): void
    {

        $this->loginAsAdmin();
        $text = 'This is my feedback';
        $privacyType = 20;
        $this->doPostMyFeedbackForm($text, $privacyType, false);
        $this->assertFlashMessage('Dein Feedback wurde erstellt.');

        $this->get($this->Slug->getFeedbackList());
        $this->assertResponseContains($text);

        $this->doPostMyFeedbackForm($text, $privacyType, true);
        $this->assertFlashMessage('Dein Feedback wurde gelöscht.');

        $this->get($this->Slug->getFeedbackList());
        $this->assertResponseNotContains($text);

    }

    private function doPostMyFeedbackForm($text, $privacyType, $delete): void
    {
        $this->post(
            $this->Slug->getMyFeedbackForm(),
            [
                'Feedbacks' => [
                    'text' => $text,
                    'privacy_type' => $privacyType,
                    'delete_feedback' => $delete,
                ],
                'referer' => '/',
            ],
        );
    }

    private function doPostFeedbackForm($customerId, $text, $privacyType, $approvedCheckbox): void
    {
        $this->post(
            $this->Slug->getFeedbackForm($customerId),
            [
                'Feedbacks' => [
                    'text' => $text,
                    'privacy_type' => $privacyType,
                    'approved_checkbox' => $approvedCheckbox,
                ],
                'referer' => '/',
            ],
        );
    }

}
