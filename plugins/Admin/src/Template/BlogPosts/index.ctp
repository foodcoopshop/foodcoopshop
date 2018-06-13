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
use Cake\Core\Configure;

?>
<div id="blogPosts">

        <?php
        $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".AppFeatherlight.initLightboxForImages('a.lightbox');
        "
        ]);
        $this->element('highlightRowAfterEdit', [
        'rowIdPrefix' => '#blogPost-'
        ]);
    ?>
   
    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'empty' => 'alle Benutzer', 'options' => $customersForDropdown, 'default' => isset($customerId) ? $customerId: '']); ?>
            <?php
            if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
                echo $this->Form->control('manufacturerId', [
                    'type' => 'select',
                    'label' => '',
                    'empty' => 'alle Hersteller',
                    'options' => $manufacturersForDropdown,
                    'default' => isset($manufacturerId) ? $manufacturerId : ''
                ]);
            }
            ?>
            <div class="right">
                <?php
                echo '<div id="add-blog-post-button-wrapper" class="add-button-wrapper">';
                echo $this->Html->link('<i class="fa fa-plus-square fa-lg"></i> Neuen Blog-Artikel erstellen', $this->Slug->getBlogPostAdd(), [
                    'class' => 'btn btn-default',
                    'escape' => false
                ]);
                echo '</div>';
                echo $this->element('printIcon');
                ?>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
    
<?php

echo '<table class="list">';
echo '<tr class="sort">';
echo '<th class="hide"></th>';
echo '<th>Bild</th>';
echo '<th></th>';
echo '<th>' . $this->Paginator->sort('BlogPosts.is_featured', 'Start-Seite') . '</th>';
echo '<th>' . $this->Paginator->sort('BlogPosts.is_private', 'Nur für Mitglieder') . '</th>';
echo '<th>' . $this->Paginator->sort('BlogPosts.title', 'Titel') . '</th>';
echo '<th>' . $this->Paginator->sort('BlogPosts.short_description', 'Kurze Beschreibung') . '</th>';
echo '<th>' . $this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), 'geändert von') . '</th>';
echo '<th>' . $this->Paginator->sort('Manufacturers.name', 'Hersteller') . '</th>';
echo '<th>' . $this->Paginator->sort('BlogPosts.modified', 'geändert am') . '</th>';
echo '<th>' . $this->Paginator->sort('BlogPosts.active', 'Aktiv') . '</th>';
echo '<th></th>';
echo '</tr>';

$i = 0;

foreach ($blogPosts as $blogPost) {
    $i ++;
    $rowClass = [
        'data'
    ];
    if (! $blogPost->active) {
        $rowClass[] = 'deactivated';
    }
    echo '<tr id="blogPost-' . $blogPost->id_blog_post . '" class="' . implode(' ', $rowClass) . '">';

    echo '<td class="hide">';
    echo $blogPost->id_blog_post;
    echo '</td>';

    echo '<td align="center" style="background-color: #fff;">';
    $srcLargeImage = $this->Html->getBlogPostImageSrc($blogPost->id_blog_post, 'single');
    $largeImageExists = preg_match('/no-single-default/', $srcLargeImage);
    if (! $largeImageExists) {
        echo '<a class="lightbox" href="' . $srcLargeImage . '">';
    }
    echo '<img width="90" src="' . $this->Html->getBlogPostImageSrc($blogPost->id_blog_post, 'home') . '" />';
    if (! $largeImageExists) {
        echo '</a>';
    }
    echo '</td>';

    echo '<td>';
    echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
        'title' => 'Bearbeiten'
    ], $this->Slug->getBlogPostEdit($blogPost->id_blog_post));
    echo '</td>';

    echo '<td align="center">';
    if ($blogPost->is_featured == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    }
    echo '</td>';

    echo '<td align="center">';
    if ($blogPost->is_private == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    }
    echo '</td>';

    echo '<td>';
    echo $blogPost->title;
    echo '</td>';

    echo '<td>';
    echo $blogPost->short_description;
    echo '</td>';

    echo '<td>';
    if (! empty($blogPost->customer->manufacturer)) {
        echo $blogPost->customer->manufacturer->name;
    } else {
        if (!empty($blogPost->customer)) {
            echo $blogPost->customer->name;
        }
    }
    echo '</td>';

    echo '<td>';
    if (! empty($blogPost->manufacturer)) {
        echo $blogPost->manufacturer->name;
    }
    echo '</td>';

    echo '<td>';
    echo $blogPost->modified->i18nFormat(Configure::read('DateFormat.de.DateNTimeLongWithSecs'));
    echo '</td>';

    echo '<td align="center">';
    if ($blogPost->active == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    } else {
        echo $this->Html->image($this->Html->getFamFamFamPath('delete.png'));
    }
    echo '</td>';

    echo '<td>';
    if ($blogPost->active) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('arrow_right.png')), [
            'title' => 'Blog-Artikel anzeigen',
            'target' => '_blank'
        ], $this->Slug->getBlogPostDetail($blogPost->id_blog_post, $blogPost->title));
    }
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="11"><b>' . $i . '</b> Datensätze</td>';
echo '</tr>';

echo '</table>';

?>    
</div>
