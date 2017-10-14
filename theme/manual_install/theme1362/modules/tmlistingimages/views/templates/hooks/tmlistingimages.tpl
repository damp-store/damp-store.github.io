{*
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
*}
{if count($product_images) > 1}
	{foreach from=$product_images item=image}
		{assign var=imageId value="`$product.id_product`-`$image.id_image`"}
		{if !empty($image.legend)}
			{assign var=imageTitle value=$image.legend|escape:'html':'UTF-8'}
		{else}
			{assign var=imageTitle value=$product.name|escape:'html':'UTF-8'}
       {/if}
	   {if $image.cover != 1}
            <img class="img-responsive hover-image" src="{$link->getImageLink($product.link_rewrite, $imageId, 'home_default')|escape:'html':'UTF-8'}" alt="{$imageTitle|escape:'html':'UTF-8'}" title="{$imageTitle|escape:'html':'UTF-8'}" />
            {break}
       {/if}
    {/foreach}
{/if}