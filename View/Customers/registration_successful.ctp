<?php
/**
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
$this->element('addScript', array('script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
));
?>

<h1><?php echo $title_for_layout; ?></h1>

<ul>

    <li>Die Bestätigung deiner Registrierung wurde per E-Mail an dich versendet.</li>

    <?php if (!Configure::read('app.db_config_FCS_DEFAULT_NEW_MEMBER_ACTIVE')) { ?>
    <li><b>Dein Mitgliedskonto ist zwar erstellt, aber noch nicht aktiviert. Das heißt, du kannst dich noch nicht einloggen!</b><br />
        Du wirst per E-Mail benachrichtigt, sobald wir dein Konto aktiviert haben.</li>
    <?php } ?>
</ul>

<?php
if (!empty($blogPosts)) {
    echo '<h2><a href="'.$this->Slug->getBlogList().'">Aktuelles</a></h2>';
    echo $this->element('blogPosts', array(
    'blogPosts' => $blogPosts
    ));
}
?>