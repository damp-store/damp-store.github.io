{*
* 2002-2015 TemplateMonster
*
* TM Category Products
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
*}
{if isset($blocks) && $blocks}
	{foreach from=$blocks item='block' name='block'}
    	{assign var="block_identificator" value="{$smarty.foreach.block.iteration}_{$block.id}"}
    	<section id="block-category-{$block_identificator|escape:'htmlall':'UTF-8'}" class="block category-block">
        	<h4 class="title_block"><a href="{$link->getCategoryLink($block.id)|escape:'html':'UTF-8'}">{$block.name|escape:'htmlall':'UTF-8'}</a></h4>
            {if isset($block.products) && $block.products}
            	{assign var='products' value=$block.products}
            	{include file="$tpl_dir./product-list.tpl"}
            {else}
            	<p class="alert alert-warning">{l s='No products in this category.' mod='tmcategoryproducts'}</p>
            {/if}
        </section>
        {if $block.carousel}
            {literal}
                <script type="text/javascript">
                    $(document).ready(function(){
                        setNbCatItems();
                        tmCategoryCarousel{/literal}{$block_identificator|escape:'htmlall':'UTF-8'}{literal} = $('#block-category-{/literal}{$block_identificator|escape:'htmlall':'UTF-8'}{literal} > ul').bxSlider({
                            responsive:true,
                            useCSS: false,
                            minSlides: tm_cps_carousel_nb_new,
                            maxSlides: tm_cps_carousel_nb_new,
                            slideWidth: tm_cps_carousel_slide_width,
                            slideMargin: tm_cps_carousel_slide_margin,
                            infiniteLoop: tm_cps_carousel_loop,
                            hideControlOnEnd: tm_cps_carousel_hide_control,
                            randomStart: tm_cps_carousel_random,
                            moveSlides: tm_cps_carousel_item_scroll,
                            pager: tm_cps_carousel_pager,
                            autoHover: tm_cps_carousel_auto_hover,
                            auto: tm_cps_carousel_auto,
                            speed: tm_cps_carousel_speed,
                            pause: tm_cps_carousel_auto_pause,
                            controls: tm_cps_carousel_control,
                            autoControls: tm_cps_carousel_auto_control,
                            startText:'',
                            stopText:'',
                        });
                    
                        var tm_cps_doit;
                        $(window).resize(function () {
                            clearTimeout(tm_cps_doit);
                            tm_cps_doit = setTimeout(function() {
                                resizedwtm_cps{/literal}{$block_identificator|escape:'htmlall':'UTF-8'}{literal}();
                            }, 201);
                        });
                    });
                    function resizedwtm_cps{/literal}{$block_identificator|escape:'htmlall':'UTF-8'}{literal}(){
                        setNbCatItems();
                        tmCategoryCarousel{/literal}{$block_identificator|escape:'htmlall':'UTF-8'}{literal}.reloadSlider({
                            responsive:true,
                            useCSS: false,
                            minSlides: tm_cps_carousel_nb_new,
                            maxSlides: tm_cps_carousel_nb_new,
                            slideWidth: tm_cps_carousel_slide_width,
                            slideMargin: tm_cps_carousel_slide_margin,
                            infiniteLoop: tm_cps_carousel_loop,
                            hideControlOnEnd: tm_cps_carousel_hide_control,
                            randomStart: tm_cps_carousel_random,
                            moveSlides: tm_cps_carousel_item_scroll,
                            pager: tm_cps_carousel_pager,
                            autoHover: tm_cps_carousel_auto_hover,
                            auto: tm_cps_carousel_auto,
                            speed: tm_cps_carousel_speed,
                            pause: tm_cps_carousel_auto_pause,
                            controls: tm_cps_carousel_control,
                            autoControls: tm_cps_carousel_auto_control,
                            startText:'',
                            stopText:'',
                        });
                }
                
                function setNbCatItems()
                {
                    if ($('.category-block').width() < 400)
                        tm_cps_carousel_nb_new = 1;
                    if ($('.category-block').width() >= 400)
                        tm_cps_carousel_nb_new = 2;
                    if ($('.category-block').width() >= 560)
                        tm_cps_carousel_nb_new = 3;
                    if($('.category-block').width() > 840)
                        tm_cps_carousel_nb_new = tm_cps_carousel_nb;
                    }
                </script>
            {/literal}
		{/if}
    {/foreach}
{/if}