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

if (empty($blogPosts)) return;

if (!isset($useCarousel) || $useCarousel) {
    $this->element('addScript', array('script' =>
        Configure::read('app.jsNamespace').".Helper.initBlogPostCarousel();"
    ));
}

if (isset($style) && $style != '') {
    $style = ' style="'.$style.'"';
} else {
    $style = '';
}

echo '<div class="blog-wrapper"'.$style.'>';

    foreach($blogPosts as $blogPost) {
        echo '<a class="blog-post-wrapper transistion" href="'.$this->Slug->getBlogPostDetail($blogPost['BlogPost']['id_smart_blog_post'], $blogPost['BlogPostLang']['meta_title']).'">';
            echo '<span class="img-wrapper">';
                echo '<img src="' . $this->Html->getBlogPostImageSrc($blogPost['BlogPost']['id_smart_blog_post'], 'home'). '" />';
            echo '</span>';
            echo '<h3>'.$blogPost['BlogPostLang']['meta_title'].'</h3>';
            echo '<span class="desc">'.$blogPost['BlogPostLang']['short_description'].'</span>';
        echo '</a>';
    }
    
echo '</div>';

?>