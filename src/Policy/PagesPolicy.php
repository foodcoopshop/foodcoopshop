<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Datasource\FactoryLocator;

class PagesPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        switch ($request->getParam('action')) {
            case 'detail':
                $pageId = (int) $request->getParam('idAndSlug');
                $pageTable = FactoryLocator::get('Table')->get('Pages');
                $page = $pageTable->find('all', [
                    'conditions' => [
                        'Pages.id_page' => $pageId,
                        'Pages.active' => APP_ON,
                    ]
                ])->first();
                if (!empty($page) && $identity === null && $page->is_private) {
                    return false;
                }
                break;
            case 'discourseSso':
                if ($identity === null) {
                    return false;
                }
                break;
        }

        return true;

    }

}