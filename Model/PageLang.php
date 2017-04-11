<?php
/**
 * PageLang
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PageLang extends AppModel
{

    public $useTable = 'cms_lang';
    public $primaryKey = 'id_cms';

    public $validate = array(
        'meta_title' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib einen Titel an.'
            ),
            'minLength' => array(
                'rule' => array(
                    'minLength',
                    3
                ),
                'message' => 'Bitte gib mindestens 3 Zeichen ein.'
            )
        )
    );
}
