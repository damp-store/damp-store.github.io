/*
* 2002-2016 TemplateMonster
*
* TM Products Slider
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
* @copyright  2002-2016 TemplateMonster
* @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

$(document).ready(function(){

	if (typeof(product_slider_width) == 'undefined')
		product_slider_width = 1170;
	if (typeof(product_slider_type) == 'undefined')
		product_slider_type = 'fade';
	if (typeof(product_slider_speed) == 'undefined')
		product_slider_speed = 500;
	if (typeof(product_slider_pause) == 'undefined')
		product_slider_pause = 3000;
	if (typeof(product_slider_loop) == 'undefined')
		product_slider_loop = 0;
	if (typeof(product_slider_pause_h) == 'undefined')
		product_slider_pause_h = 0;
	if (typeof(product_slider_pager) == 'undefined')
		product_slider_pager = 0;
	if (typeof(product_slider_controls) == 'undefined')
		product_slider_controls = 0;
	if (typeof(product_slider_auto_controls) == 'undefined')
		product_slider_auto_controls = 0;


	if (!!$.prototype.bxSlider)
		$('#product-slider').bxSlider({
			useCSS: false,
			maxSlides: 1,
			slideWidth: product_slider_width,
			infiniteLoop: true,
			hideControlOnEnd: true,
			pager: product_slider_pager,
			autoHover: product_slider_pause_h,
			auto: product_slider_loop,
			speed: product_slider_speed,
			pause: product_slider_pause,
			controls: product_slider_controls,
			autoControls: product_slider_auto_controls,
			mode: product_slider_type,
			startText:'',
			stopText:'',
		});
});