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
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".AppFeatherlight.initLightboxForImages();"
));
?>

<h1><?php echo $title_for_layout; ?>
<?php if (isset($manufacturer['Manufacturer']['name'])) {
    echo ' von ' . $manufacturer['Manufacturer']['name'];
} ?>
<span><?php echo count($blogPosts); ?> gefunden</span></h1>

<?php
foreach ($blogPosts as $blogPost) {
    echo '<div class="blog-post-wrapper">';

    $blogDetailLink = $this->Slug->getBlogPostDetail($blogPost['BlogPost']['id_smart_blog_post'], $blogPost['BlogPostLang']['meta_title']);
    echo '<div class="first-column">';
        $srcLargeImage = $this->Html->getBlogPostImageSrc($blogPost['BlogPost']['id_smart_blog_post'], 'single');
        $largeImageExists = preg_match('/no-single-default/', $srcLargeImage);
    if (!$largeImageExists) {
        echo '<a data-featherlight="image" href="'.$srcLargeImage.'">';
    }
        echo '<img src="' . $this->Html->getBlogPostImageSrc($blogPost['BlogPost']['id_smart_blog_post'], 'home'). '" />';
    if (!$largeImageExists) {
        echo '</a>';
    }
        echo '</div>';

        echo '<div class="second-column">';
        echo '<h4>'.$this->Html->link(
            $blogPost['BlogPostLang']['meta_title'],
            $blogDetailLink
        ).'</h4>';
        echo $blogPost['BlogPostLang']['short_description'].'<br />';
        echo $this->Html->link(
            '<i class="fa fa-plus-circle"></i> Mehr anzeigen',
            $blogDetailLink,
            array('escape' => false)
        );
        
        if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
            echo $this->Html->getJqueryUiIcon(
                $this->Html->image($this->Html->getFamFamFamPath('page_edit.png')),
                array(
                    'title' => 'Bearbeiten'
                ),
                $this->Slug->getBlogPostEdit($blogPost['BlogPost']['id_smart_blog_post'])
                );
        }
        
    echo '</div>';

    echo '<div class="third-column">';

        echo $this->Html->link(
            'Blog-Artikel anzeigen',
            $blogDetailLink,
            array('class' => 'btn btn-success')
        );

        echo '<div class="additional-info">';
            echo 'Geändert am ' . $this->Time->formatToDateNTimeShort($blogPost['BlogPost']['modified']);
    if (!empty($blogPost['Manufacturer']['id_manufacturer'])) {
        echo '<br />';
        if ($blogPost['Manufacturer']['active']) {
            echo '<a href="'.$this->Slug->getManufacturerBlogList($blogPost['Manufacturer']['id_manufacturer'], $blogPost['Manufacturer']['name']).'">Zum Blog von  ' . $blogPost['Manufacturer']['name'].'</a>';
        } else {
            echo 'von ' . $blogPost['Manufacturer']['name'];
        }
    }
            echo '</div>';

            echo '</div>';

            echo '</div>';

            echo '<div class="sc"></div>';
}
?>