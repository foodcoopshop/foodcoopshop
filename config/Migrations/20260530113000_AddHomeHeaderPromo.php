<?php
declare(strict_types=1);

use Cake\Core\Configure;
use App\Model\Entity\Page;
use Cake\ORM\TableRegistry;
use Migrations\BaseMigration;
use App\Model\Table\HeaderPromosTable;
use App\Model\Table\ConfigurationsTable;

class AddHomeHeaderPromo extends BaseMigration
{
    public function up(): void
    {

        /** @var ConfigurationsTable $configurationsTable */
        $configurationsTable = TableRegistry::getTableLocator()->get('Configurations');
        $configurationsTable->loadConfigurations();
        
        if ((bool) Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            return;
        }

        if (!(bool) Configure::read('app.showManufacturerListAndDetailPage')) {
            return;
        }

        /** @var HeaderPromosTable $headerPromosTable */
        $headerPromosTable = TableRegistry::getTableLocator()->get('HeaderPromos');

        $existingPromo = $headerPromosTable->find('all', conditions: [
            'HeaderPromos.page_id' => Page::PAGE_ID_HOME,
        ])->first();
        if ($existingPromo !== null) {
            return;
        }

        $title = trim((string) Configure::read('appDb.FCS_APP_NAME'));
        if ($title === '') {
            return;
        }

        $headerPromo = $headerPromosTable->newEntity([
            'page_id' => Page::PAGE_ID_HOME,
            'title' => mb_substr($title, 0, 55),
            'lead_text' => __('Come by and join us!'),
            'text' => __('Become part of our food coop and help shape a fair, regional food supply. Here you will find fresh, sustainable products directly from producers.'),
            'primary_label' => __('Become a member'),
            'primary_href' => Configure::read('App.fullBaseUrl') . Configure::read('app.slugHelper')->getLogin(),
        ]);

        $headerPromosTable->saveOrFail($headerPromo);

    }
}
