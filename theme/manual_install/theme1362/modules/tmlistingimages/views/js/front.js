/*
 * 2002-2015 TemplateMonster
 *
 * TemplateMonster Product List Images
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 2.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-2.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the module to newer
 * versions in the future.
 *
 * @author     TemplateMonster (Alexander Grosul)
 * @copyright  2002-2015 TemplateMonster
 * @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

$(document).ready(function () {
  images_view();
});

function images_view() {
  $(document).on('mouseenter', '.product-image-container', function (e) {
    if ($(this).find('.product_img_link').children('img.hover-image').length) {
      $(this).find('.product_img_link').children('img.hover-image').stop().animate({opacity: 1});
    }
  });
  $(document).on('mouseleave', '.product-image-container', function (e) {
    if ($(this).find('.product_img_link').children('img.hover-image').length) {
      $(this).find('.product_img_link').children('img.hover-image').stop().animate({opacity: 0});
    }
  });
}