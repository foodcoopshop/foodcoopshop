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

if (empty($blogPosts)) {
    return;
}

if (!isset($useCarousel) || $useCarousel) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".Helper.initBlogPostCarousel();"
    ]);
}

if (isset($style) && $style != '') {
    $style = ' style="'.$style.'"';
} else {
    $style = '';
}

echo '<div class="blog-wrapper"'.$style.'>';

foreach ($blogPosts as $blogPost) {
    echo '<a class="blog-post-wrapper transistion" href="'.$this->Slug->getBlogPostDetail($blogPost->id_blog_post, $blogPost->title).'">';
    echo '<span class="img-wrapper">';
        echo '<img class="blog-post-image" src="' . $this->Html->getBlogPostImageSrc($blogPost, 'home'). '" />';
    echo '</span>';
    echo '<h3>'.$blogPost->title.'</h3>';
    echo '<span class="desc">'.$blogPost->short_description.'</span>';
    echo '</a>';
}

echo '</div>';
