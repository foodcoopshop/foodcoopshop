<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<div id="blogPosts">

        <?php
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace') . ".Admin.init();" .
            Configure::read('app.jsNamespace') . ".AppFeatherlight.initLightboxForImages('a.lightbox');".
            Configure::read('app.jsNamespace').".Admin.initCustomerDropdown(" . ($customerId != '' ? $customerId : '0') . ");
            "
        ]);
        $this->element('highlightRowAfterEdit', [
            'rowIdPrefix' => '#blogPost-'
        ]);
    ?>
   
    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
        	<h1><?php echo $title_for_layout; ?></h1>
            <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'empty' => __d('admin', 'all_members'), 'options' => []]); ?>
            <?php
            if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
                echo $this->Form->control('manufacturerId', [
                    'type' => 'select',
                    'label' => '',
                    'empty' => __d('admin', 'all_manufacturers'),
                    'options' => $manufacturersForDropdown,
                    'default' => isset($manufacturerId) ? $manufacturerId : ''
                ]);
            }
            ?>
            <div class="right">
                <?php
                echo '<div id="add-blog-post-button-wrapper" class="add-button-wrapper">';
                echo $this->Html->link('<i class="fas fa-plus-circle"></i> '.__d('admin', 'Add_blog_post').'', $this->Slug->getBlogPostAdd(), [
                    'class' => 'btn btn-outline-light',
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
echo '<th>'.__d('admin', 'Image').'</th>';
echo '<th></th>';
echo '<th>' . $this->Paginator->sort('BlogPosts.is_featured', __d('admin', 'Homepage')) . '</th>';
echo '<th>' . $this->Paginator->sort('BlogPosts.is_private', __d('admin', 'Only_for_members')) . '</th>';
echo '<th>' . $this->Paginator->sort('BlogPosts.title', __d('admin', 'Title')) . '</th>';
echo '<th>' . $this->Paginator->sort('BlogPosts.short_description', __d('admin', 'Short_description')) . '</th>';
echo '<th>' . $this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), __d('admin', 'Modified_by')) . '</th>';
echo '<th>' . $this->Paginator->sort('Manufacturers.name', __d('admin', 'Manufacturer')) . '</th>';
echo '<th>' . $this->Paginator->sort('BlogPosts.modified', __d('admin', 'Modified_on')) . '</th>';
echo '<th>' . $this->Paginator->sort('BlogPosts.active', __d('admin', 'Active')) . '</th>';
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
    $srcLargeImage = $this->Html->getBlogPostImageSrc($blogPost, 'single');
    $srcSmallImage = $this->Html->getBlogPostImageSrc($blogPost, 'home');

    $largeImageExists = ! preg_match('/no-single-default/', $srcLargeImage);

    if ($largeImageExists) {
        echo '<a class="lightbox" href="' . $srcLargeImage . '">';
    }
    echo '<img width="90" src="' . $srcSmallImage . '" />';
    if ($largeImageExists) {
        echo '</a>';
    }
    echo '</td>';

    echo '<td>';
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            $this->Slug->getBlogPostEdit($blogPost->id_blog_post),
            [
                'class' => 'btn btn-outline-light',
                'title' => __d('admin', 'Edit'),
                'escape' => false
            ]
        );
    echo '</td>';

    echo '<td align="center">';
    if ($blogPost->is_featured == 1) {
        echo '<i class="fas fa-check-circle ok"></i>';
    }
    echo '</td>';

    echo '<td align="center">';
    if ($blogPost->is_private == 1) {
        echo '<i class="fas fa-check-circle ok"></i>';
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
    echo $blogPost->modified->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs'));
    echo '</td>';

    echo '<td align="center">';
    if ($blogPost->active == 1) {
        echo '<i class="fas fa-check-circle ok"></i>';
    } else {
        echo '<i class="fas fa-minus-circle not-ok"></i>';
    }
    echo '</td>';

    echo '<td>';
    if ($blogPost->active) {
        echo $this->Html->link(
            '<i class="fas fa-arrow-right ok"></i>',
            $this->Slug->getBlogPostDetail($blogPost->id_blog_post, $blogPost->title),
            [
                'class' => 'btn btn-outline-light',
                'title' => __d('admin', 'Show_blog_post'),
                'target' => '_blank',
                'escape' => false
            ]
        );
    }
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="11"><b>' . $i . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $i).'</td>';
echo '</tr>';

echo '</table>';

?>    
</div>
