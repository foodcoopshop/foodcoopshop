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
use App\Controller\Component\StringComponent;
use App\Model\Entity\Block;
use App\Model\Entity\Page;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Editor.initBig('pages-content');" .
        Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.pages .page-image-wrapper .add-image-button', foodcoopshop.Upload.savePageTmpImageInForm);" .
        Configure::read('app.jsNamespace') . ".Admin.initHeaderPromoVisibility('body.pages .page-image-wrapper a.add-image-button', 'body.pages input[name=\\\"Pages[delete_image]\\\"]', '.header-promo-section');" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);

$idForImageUpload = StringComponent::createRandomString(6);
$imageSrc = $this->Html->getHomeImageSrc('single');
if (!empty($this->request->getData('Pages.tmp_image'))) {
    $imageSrc = str_replace('\\', '/', (string) $this->request->getData('Pages.tmp_image'));
}
$imageExists = $imageSrc != '';

$preparedBlocks = [];
foreach ($blocks as $index => $block) {
    $rowKey = !empty($block['id']) ? 'block-' . (string) $block['id'] : 'block-temp-' . $index;
    $preparedBlocks[] = [
        'rowKey' => $rowKey,
        'block' => $block,
    ];
}
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa-fw fas fa-check"></i> <?php echo __('Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa-fw fas fa-times"></i> <?php echo __('Cancel'); ?></a>
    </div>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($page, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $this->Slug->getPageEditHome(),
    'id' => 'pageEditForm'
]);

$this->Form->unlockField('Blocks');

echo $this->Form->hidden('referer', ['value' => $referer]);

echo '<div class="page-header-layout">';
echo '<div class="page-header-image-column">';

echo '<section class="home-edit-section home-edit-section-header-image">';
    echo '<h2>' . __('Header image') . '</h2>';
    echo '<div class="input">';
        echo '<div class="page-image-wrapper">';
            echo $this->Html->link(
                $imageExists ? $this->Html->image($imageSrc) : '<i class="fas fa-plus-square"></i> ' . __('Header image upload'),
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light add-image-button ' . ($imageExists ? 'uploaded' : ''),
                    'title' => __('Upload_new_image_or_change_it'),
                    'data-object-id' => $idForImageUpload,
                    'escape' => false,
                ]
            );
            $imageLabel = __('min {0}px width.', [number_format(Page::IMAGE_UPLOAD_MIN_WIDTH, 0, ',', '.')]);
            if ($imageExists) {
                $imageLabel .= '<br />' . __('Click_on_image_to_change_it.');
            }
            echo '<span class="small">' . $imageLabel . '</span>';
        echo '</div>';
        echo $this->Form->hidden('Pages.tmp_image');
        $this->Form->unlockField('Pages.tmp_image');
    echo '</div>';
echo '</section>';

if ($imageExists) {
    echo '<div class="warning">';
        echo $this->Form->control('Pages.delete_image', [
            'label' => __('Delete_image?') . ' <span class="after small">'.__('Check_and_do_not_forget_to_click_save_button._admin').'</span>',
            'type' => 'checkbox',
            'escape' => false,
        ]);
    echo '</div>';
}

echo '</div>';

echo '<section class="home-edit-section home-edit-section-header-promo page-header-info-column header-promo-section' . ($imageExists ? '' : ' hide') . '">';
    echo '<h2>' . __('Header infos') . ' ' . '(' . __('only for logged out users') . ')</h2>';
echo '<div class="header-promo-actions-editor">';
echo $this->element('headerPromoFields');
echo '</div>';
echo '</section>';

echo '</div>';

echo '<section class="home-edit-section home-edit-section-info-text">';
echo '<h2>' . __('Info text') . ' ' . '(' . __('visible to all users') . ')</h2>';
echo '<div class="home-edit-info-text-row">';
    echo '<div class="home-edit-info-text-label">';
        echo '<p class="small"><a href="'.$this->Html->getDocsUrl(__('docs_route_wysiwyg_editor')).'" target="_blank">'.__('How_do_I_use_the_WYSIWYG_editor?').'</a></p>';
    echo '</div>';
    echo '<div class="home-edit-info-text-editor">';
        echo $this->Form->control('Pages.content', [
            'type' => 'textarea',
            'value' => $homeText,
            'label' => false,
        ]);
    echo '</div>';
echo '</div>';
echo '</section>';

echo '<section class="home-edit-section home-edit-section-blocks">';
echo '<div class="home-blocks-editor">';
    echo '<div class="home-blocks-editor-header">';
        echo '<h2>' . __('Blocks') . ' ' . '(' . __('only for logged out users') . ')</h2>';
        echo '<a href="javascript:void(0);" class="btn btn-success add-home-block-button"><i class="fa-fw fas fa-plus"></i> ' . ($isMobile ? __('Block') : __('Add block')) . '</a>';
    echo '</div>';

    echo '<div class="home-blocks-empty-state' . (count($preparedBlocks) > 0 ? ' hide' : '') . '">';
        echo '<div class="home-blocks-empty-text">' . __('No home block has been created yet.') . '</div>';
        echo '<div class="home-blocks-empty-separator"></div>';
    echo '</div>';

    echo '<div class="home-blocks-list">';
        foreach ($preparedBlocks as $preparedBlock) {
            echo $this->element('homeBlockRow', [
                'rowKey' => $preparedBlock['rowKey'],
                'block' => $preparedBlock['block'],
                'isTemplate' => false,
                'templateKey' => '',
            ]);
        }
        echo $this->element('homeBlockRow', [
            'block' => [],
            'isTemplate' => true,
            'templateKey' => '__INDEX__',
        ]);
    echo '</div>';
echo '</div>';
echo '</section>';

echo '<section class="home-edit-section home-edit-section-map">';
echo '<h2>' . __('Map') . ' ' . '(' . __('visible to all users') . ')</h2>';
echo $this->Form->control('Configurations.FCS_FOODCOOPS_MAP_ENABLED', [
    'type' => 'checkbox',
    'label' => __('Configuration_text_FCS_FOODCOOPS_MAP_ENABLED'),
    'checked' => $foodcoopsMapEnabled,
    'escape' => false,
]);
echo '</section>';

echo $this->Form->end();

?>

<div class="sc"></div>

<?php
echo $this->element('addScript', [
    'script' => <<<JS
(function () {
    var blocksContainer = $('.home-blocks-list');
    var blockTemplate = $('.home-block-row.template');
    var emptyState = $('.home-blocks-empty-state');
    var uploadFormsContainer = $('#home-block-upload-forms');
    var uploadFormTemplate = $('form#mini-upload-form-image-__INDEX__');
    var blockIndex = $('.home-block-row:not(.template)').length;

    var toggleEmptyState = function () {
        if (blocksContainer.find('.home-block-row:not(.template)').length === 0) {
            emptyState.removeClass('hide');
        } else {
            emptyState.addClass('hide');
        }
    };

    var scrollToRow = function (row) {
        var filterContainerHeight = $('.filter-container:visible').outerHeight() || 0;
        var targetTop = Math.max(0, row.offset().top - filterContainerHeight - 12);
        $('html, body').stop(true).animate({scrollTop: targetTop}, 250);
    };

    var initBlockRow = function (row) {
        var objectId = row.data('objectId');
        foodcoopshop.Editor.initBigReduced('home-block-content-' + objectId);
        foodcoopshop.Upload.initImageUpload('body.pages .block-image-upload-button[data-object-id="' + objectId + '"]', foodcoopshop.Upload.saveBlockTmpImageInForm);
    };

    var addUploadFormForBlock = function (objectId) {
        var uploadForm = uploadFormTemplate.clone();
        uploadForm.removeClass('hide');
        uploadForm.attr('id', 'mini-upload-form-image-' + objectId);
        uploadForm.attr('data-object-id', objectId);
        uploadForm.find('.heading').text(__('Upload_new_image'));
        uploadForm.find('.drop img').remove();
        uploadForm.find('a.uploadedFile').remove();
        uploadForm.find('input[type="file"]').val('');
        uploadFormsContainer.append(uploadForm);
    };

    $('.home-block-row:not(.template)').each(function () {
        initBlockRow($(this));
    });
    toggleEmptyState();

    $(document).on('click', '.add-home-block-button', function () {
        var objectId = 'new-block-' + blockIndex + '-' + Date.now();
        blockIndex++;
        var row = blockTemplate.clone();
        row.removeClass('template hide').show();
        row.attr('data-object-id', objectId);
        row.html(row.html().replace(/__INDEX__/g, objectId));
        blocksContainer.append(row);
        addUploadFormForBlock(objectId);
        initBlockRow(row);
        toggleEmptyState();
        scrollToRow(row);
    });

    $(document).on('click', '.remove-home-block-button', function () {
        if (!confirm(__('Really delete block? Don\'t forget to click save afterwards.'))) {
            return;
        }

        var row = $(this).closest('.home-block-row');
        var objectId = row.data('objectId');
        $('form#mini-upload-form-image-' + objectId).remove();
        row.remove();
        toggleEmptyState();
    });

    $(document).on('click', '.home-block-row-actions .submit', function (e) {
        e.preventDefault();
        foodcoopshop.Helper.disableButton($(this));
        foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-check');
        $('#pageEditForm').submit();
    });
}());
JS,
]);
?>

<?php
echo $this->element('imageUploadForm', [
    'id' => $idForImageUpload,
    'action' => '/admin/tools/doTmpPageImageUpload/',
    'imageExists' => $imageExists,
    'existingImageSrc' => $imageSrc,
]);

echo '<div id="home-block-upload-forms" class="hide">';
    foreach ($preparedBlocks as $preparedBlock) {
        $blockRow = $preparedBlock['block'];
        $blockImageSrc = '';
        if (!empty($blockRow['tmp_image'])) {
            $blockImageSrc = str_replace('\\', '/', (string) $blockRow['tmp_image']);
        } elseif (!empty($blockRow['image']) && !empty($blockRow['id'])) {
            $blockEntity = $blockRow instanceof Block ? $blockRow : new Block((array) $blockRow);
            $blockImageSrc = $this->Html->getBlockImageSrc($blockEntity);
        }

        echo $this->element('imageUploadForm', [
            'id' => $preparedBlock['rowKey'],
            'action' => '/admin/tools/doTmpImageUpload/',
            'imageExists' => $blockImageSrc != '',
            'existingImageSrc' => $blockImageSrc,
        ]);
    }

    echo $this->element('imageUploadForm', [
        'id' => '__INDEX__',
        'action' => '/admin/tools/doTmpImageUpload/',
        'imageExists' => false,
        'existingImageSrc' => '',
    ]);
echo '</div>';
?>
