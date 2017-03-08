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
    Configure::read('app.jsNamespace').".AppFeatherlight.initLightboxForImages('.blog_posts.detail .img-wrapper a');"
));
?>

<h1><?php echo $title_for_layout; ?></h1>

<?php
    echo '<div class="img-wrapper">';
        $srcLargeImage = $this->Html->getBlogPostImageSrc($blogPost['BlogPost']['id_smart_blog_post'], 'single');
        $largeImageExists = preg_match('/no-single-default/', $srcLargeImage);
        if (!$largeImageExists) {
            echo '<a href="'.$srcLargeImage.'">';
                echo '<img class="blog-post-image" src="' . $this->Html->getBlogPostImageSrc($blogPost['BlogPost']['id_smart_blog_post'], 'single'). '" />';
            echo '</a>';
        }
    echo '</div>';
    
    if ($blogPost['BlogPostLang']['short_description'] != '') {
        echo '<p><b>'.$blogPost['BlogPostLang']['short_description'].'</b></p>';
    }
    
    echo $blogPost['BlogPostLang']['content'];
    
    echo '<p><i>';
        echo '<br />Geändert am ' . $this->Time->formatToDateNTimeShort($blogPost['BlogPost']['modified']);
        if (!empty($blogPost['Manufacturer']['id_manufacturer'])) {
            echo '<br />';
            if ($blogPost['Manufacturer']['active']) {
                echo '<a href="'.$this->Slug->getManufacturerBlogList($blogPost['Manufacturer']['id_manufacturer'], $blogPost['Manufacturer']['name']).'">Zum Blog von  ' . $blogPost['Manufacturer']['name'].'</a>';
            } else {
                echo 'von ' . $blogPost['Manufacturer']['name'];
            }
        }
    echo '</i></p>';
    echo '<div class="sc"></div>';
    
    if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
        echo $this->Html->getJqueryUiIcon(
            $this->Html->image($this->Html->getFamFamFamPath('page_edit.png')),
            array(
                'title' => 'Bearbeiten'
            )
            ,$this->Slug->getBlogPostEdit($blogPost['BlogPost']['id_smart_blog_post'])
        );
    }
    
?>

<?php
    if (!empty($neighbors['prev']) || !empty($neighbors['next'])) {
        echo '<h2>Weitere Beiträge</h2>';
    }
    if (!empty($neighbors['next'])) {
        echo $this->element('blogPosts', array(
            'blogPosts' => array($neighbors['next']),
            'useCarousel' => false,
            'style' => 'float: left;'
        ));
    }
    if (!empty($neighbors['prev'])) {
        echo $this->element('blogPosts', array(
            'blogPosts' => array($neighbors['prev']),
            'useCarousel' => false,
            'style' => 'float: right;'
        ));
    }
?>
