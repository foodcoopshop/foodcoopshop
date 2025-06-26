<?php
declare(strict_types=1);
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();"
]);
?>
<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
</div>

<?php
$changelogFile = ROOT . DS . 'CHANGELOG.md';
if (!file_exists($changelogFile)) {
    return;
}
$content = file_get_contents($changelogFile);
$content = str_replace('Das Format basiert auf [keepachangelog.com](http://keepachangelog.com) und verwendet [Semantic Versioning](http://semver.org/).', '', $content);
$content = str_replace('# Changelog v4.x und v3.x', '', $content);
$content = str_replace('## unreleased', '', $content);

$content = strip_tags($content);

$content = preg_replace_callback(
'/\[(.*?)\]\((.*?)\)/',
function ($matches) {
    $text = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
    $url = htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
    return '<a href="' . $url . '">' . $text . '</a>';
}, $content);

$content = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $content);
$content = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $content);
$content = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $content);
$content = nl2br($content);

$content = preg_replace('/(<br\s*\/?>\s*){2,}/', '<br>', $content);

echo '<div class="changelog-content">';
    echo $content;
echo '</div>';
