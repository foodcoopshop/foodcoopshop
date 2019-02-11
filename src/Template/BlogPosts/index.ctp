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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".AppFeatherlight.initLightboxForImages();"
]);
?>

<h1><?php echo $title_for_layout; ?>
<?php if (isset($manufacturer->name)) {
    echo ' ' . __('of_{0}', [$manufacturer->name]);
} ?>
<span><?php echo $blogPosts->count(); ?> <?php echo __('found'); ?></span></h1>

<?php
foreach ($blogPosts as $blogPost) {
    echo '<div class="blog-post-wrapper">';

    $blogDetailLink = $this->Slug->getBlogPostDetail($blogPost->id_blog_post, $blogPost->title);
    echo '<div class="first-column">';
        $srcLargeImage = $this->Html->getBlogPostImageSrc($blogPost, 'single');
        $largeImageExists = preg_match('/(no-single-default|default-large)/', $srcLargeImage);
        if (!$largeImageExists) {
            echo '<a data-featherlight="image" href="'.$srcLargeImage.'">';
        }
        echo '<img src="' . $this->Html->getBlogPostImageSrc($blogPost, 'home'). '" />';
        if (!$largeImageExists) {
            echo '</a>';
        }
        echo '</div>';

        echo '<div class="second-column">';
        echo '<h4>'.$this->Html->link(
            $blogPost->title,
            $blogDetailLink
        ).'</h4>';
        echo $blogPost->short_description.'<br />';
        echo $this->Html->link(
            '<i class="fas fa-plus-circle"></i> ' . __('Show_more'),
            $blogDetailLink,
            ['escape' => false]
        );

    if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt"></i>',
            $this->Slug->getBlogPostEdit($blogPost->id_blog_post),
            [
                'class' => 'btn btn-outline-light edit-shortcut-button',
                'title' => __('Edit'),
                'escape' => false
            ]
        );
    }

    echo '</div>';

    echo '<div class="third-column">';

        echo $this->Html->link(
            __('Show_Blog_post'),
            $blogDetailLink,
            ['class' => 'btn btn-success']
        );

        echo '<div class="additional-info">';
            echo __('Modified_on') . ' ' .$blogPost->modified->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'));
    if (!empty($blogPost->manufacturer->id_manufacturer)) {
        echo '<br />';
        if ($blogPost->manufacturer->active) {
            if ($this->request->getRequestTarget() == $this->Slug->getManufacturerBlogList($blogPost->manufacturer->id_manufacturer, $blogPost->manufacturer->name)) {
                echo '<a href="'.$this->Slug->getManufacturerDetail($blogPost->manufacturer->id_manufacturer, $blogPost->manufacturer->name).'">' . __('Go_to_manufacturer') . ' ' . $blogPost->manufacturer->name.'</a>';
            } else {
                echo '<a href="'.$this->Slug->getManufacturerBlogList($blogPost->manufacturer->id_manufacturer, $blogPost->manufacturer->name).'">' . __('Go_to_blog_from') . ' ' . $blogPost->manufacturer->name.'</a>';
            }
        } else {
            echo ' ' . __('of_{0}', [$blogPost['Manufacturers']['name']]);
        }
    }
            echo '</div>';

            echo '</div>';

            echo '</div>';

            echo '<div class="sc"></div>';
}
?>
