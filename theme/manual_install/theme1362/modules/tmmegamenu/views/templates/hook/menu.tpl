{*
* 2002-2015 TemplateMonster
*
* TM Mega Menu
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

{if isset($MENU) && $MENU !=''} 
    {if $hook == 'left_column' || $hook == 'right_column'}
        <section class="block">
            <h4 class="title_block">{l s='Menu' mod='tmmegamenu'}</h4>
            <div class="block_content {$hook|escape:'htmlall':'UTF-8'}_menu column_menu top-level tmmegamenu_item">
    {else}
        <div class="{$hook|escape:'htmlall':'UTF-8'}_menu top-level tmmegamenu_item">
            <div class="menu-title tmmegamenu_item">{l s='Menu' mod='tmmegamenu'}</div>
    {/if}
        {$MENU}
    
    {if $hook == 'left_column' || $hook == 'right_column'}
            </div>
        </section>
    {else}
        </div>
    {/if}
{/if}