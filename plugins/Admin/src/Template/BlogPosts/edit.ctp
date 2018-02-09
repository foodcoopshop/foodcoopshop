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

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Helper.initCkeditorBig('BlogPostContent');" . Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.blog_posts .add-image-button', foodcoopshop.Upload.saveBlogPostTmpImageInForm, foodcoopshop.AppFeatherlight.closeLightbox);" . Configure::read('app.jsNamespace') . ".Admin.initForm('" . (isset($this->request->data['BlogPosts']['id_blog_post']) ? $this->request->data['BlogPosts']['id_blog_post'] : "") . "', 'BlogPosts');
    "
]);

$idForImageUpload = isset($this->request->data['BlogPosts']['id_blog_post']) ? $this->request->data['BlogPosts']['id_blog_post'] : StringComponent::createRandomString(6);
$imageSrc = $this->Html->getBlogPostImageSrc($idForImageUpload, 'single');
if (isset($this->request->data['BlogPosts']['tmp_image']) && $this->request->data['BlogPosts']['tmp_image'] != '') {
    $imageSrc = str_replace('\\', '/', $this->request->data['BlogPosts']['tmp_image']);
}
$imageExists = ! preg_match('/no-single-default/', $imageSrc);

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a> <a href="javascript:void(0);"
            class="btn btn-default cancel"><i class="fa fa-remove"></i> Abbrechen</a>
    </div>
</div>

<div id="help-container">
    <ul>
        <li>Auf dieser Seite kannst du den Blog-Artikel ändern.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create('BlogPosts', [
    'class' => 'fcs-form'
]);

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('BlogPosts.id_blog_post');
echo $this->Form->input('BlogPosts.title', [
    'div' => [
        'class' => 'long text input'
    ],
    'label' => 'Titel',
    'required' => true
]);
echo $this->Form->input('BlogPosts.short_description', [
    'div' => [
        'class' => 'long text input'
    ],
    'required' => false,
    'label' => 'Kurze Beschreibung'
]);

echo '<div class="input">';
echo '<label>Bild';
if ($imageExists) {
    echo '<br /><span class="small">Zum Ändern auf das Bild klicken.</span>';
}
echo '</label>';
echo '<div style="float:right;">';
echo $this->Html->getJqueryUiIcon($imageExists ? $this->Html->image($imageSrc) : $this->Html->image($this->Html->getFamFamFamPath('image_add.png')), [
    'class' => 'add-image-button ' . ($imageExists ? 'uploaded' : ''),
    'title' => 'Neues Bild hochladen bzw. austauschen',
    'data-object-id' => $idForImageUpload
], 'javascript:void(0);');
echo '</div>';
echo $this->Form->hidden('BlogPosts.tmp_image');
echo '</div>';
echo $this->Form->input('BlogPosts.delete_image', [
    'label' => 'Bild löschen?',
    'type' => 'checkbox',
    'after' => '<span class="after small">Anhaken und dann auf <b>Speichern</b> klicken.</span>'
]);

if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
    echo $this->Form->input('BlogPosts.id_manufacturer', [
        'type' => 'select',
        'label' => 'Hersteller',
        'empty' => 'Hersteller auswählen',
        'options' => $manufacturersForDropdown
    ]);
    echo '<span class="description small">Falls der Hersteller nur für Mitglieder angezeigt wird, gilt das auch für diesen Blog-Artikel (unabhängig von der eigenen Einstellung "nur für Mitglieder").</span>';
}

echo $this->Form->input('BlogPosts.is_featured', [
    'label' => 'Auf der Startseite anzeigen?',
    'type' => 'checkbox'
]);
echo $this->Form->input('BlogPosts.is_private', [
    'label' => 'Nur für Mitglieder sichtbar?',
    'type' => 'checkbox'
]);
echo $this->Form->input('BlogPosts.active', [
    'label' => 'Aktiv?',
    'type' => 'checkbox'
]);

if ($appAuth->isSuperadmin() || $appAuth->isAdmin() && $this->request->here != $this->Slug->getBlogPostAdd()) {
    echo $this->Form->input('BlogPosts.update_modified_field', [
        'label' => 'Nach vorne reihen?',
        'type' => 'checkbox',
        'after' => '<span class="after small">Falls angehakt, wird der Blog-Artikel an die erste Stelle der Liste gereiht.</span>'
    ]);
}

if ($this->request->here != $this->Slug->getBlogPostAdd()) {
    echo $this->Form->input('BlogPosts.delete_blog_post', [
        'label' => 'Blog-Artikel löschen?',
        'type' => 'checkbox',
        'after' => '<span class="after small">Anhaken und dann auf <b>Speichern</b> klicken.</span>'
    ]);
}

echo $this->Form->input('BlogPosts.content', [
    'class' => 'ckeditor',
    'type' => 'textarea',
    'label' => 'Text<br /><br /><span class="small"><a href="https://foodcoopshop.github.io/de/wysiwyg-editor" target="_blank">Wie verwende ich den Editor?</a></span>',
    'required' => false
]);

?>

</form>

<div class="sc"></div>

<?php
echo $this->element('imageUploadForm', [
    'id' => $idForImageUpload,
    'action' => '/admin/tools/doTmpImageUpload/',
    'imageExists' => $imageExists,
    'existingImageSrc' => $imageSrc
]);
?>
