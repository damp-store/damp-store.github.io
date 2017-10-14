{**
* 2002-2015 TemplateMonster
*
* TM Mega Layout
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
*  @author    TemplateMonster (Alexander Grosul & Alexander Pervakov)
*  @copyright 2002-2016 TemplateMonster
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

<ul class="nav nav-tabs tmmegalayout-nav panel">
    {foreach from=$tabs item=tab key=tab_name name=tabs}
        <li id="tab-{$smarty.foreach.tabs.iteration|escape:'htmlall':'UTF-8'}" class="{if $smarty.foreach.tabs.iteration == 1}active{/if}">
            <a href="#items-{$smarty.foreach.tabs.iteration|escape:'htmlall':'UTF-8'}" data-toggle="tab" id="{$tab.id|escape:'htmlall':'UTF-8'}" {if isset($tab.id_hook)}data-tab-id="{$tab.id_hook|escape:'htmlall':'UTF-8'}" class="layouts-tab"{/if}>{$tab_name|escape:'htmlall':'UTF-8'}</a>
            <input class="layout-list-info hidden" value='{if $tab.type == 'layout'}{$tab.layouts_list_json|escape:"quotes":"UTF-8"}{/if}'>
        </li>
    {/foreach}
</ul>

<div class="tab-content tmmegalayout-tab-content">
    <script type="text/javascript">
        var tmml_theme_url = '{$theme_url|escape:"quotes":"UTF-8"}';
    </script>
    {foreach from=$tabs item=content key=tab_name name=content}
        {if $content.type == 'layout'}
            <div id="items-{$smarty.foreach.content.iteration|escape:'htmlall':'UTF-8'}" class="tab-pane layout-tab-content {if $smarty.foreach.content.iteration == 1}active{/if}">
                <div class="tmpanel">
                    <div class="tmpanel-content clearfix">
                        {include file="{$templates_dir|escape:'htmlall':'UTF-8'}tmmegalayout-tab-content.tpl" content=$content}
                     </div>
                </div>
            </div>
        {else}
            <div id="items-{$smarty.foreach.content.iteration|escape:'htmlall':'UTF-8'}" class="tab-pane {if $smarty.foreach.content.iteration == 1}active{/if}">
                <div class="tmpanel panel">
                    <div class="tmpanel-content clearfix">
                        {$content.content|escape:'quotes':'UTF-8'}
                    </div>
                </div>
            </div>
        {/if}
    {/foreach}
</div>

{addJsDefL name='tmml_row_classese_text'}{l s='Enter row classes' mod='tmmegalayout'}{/addJsDefL}
{addJsDefL name='tmml_sp_class_text'}{l s='Specific class' mod='tmmegalayout'}{/addJsDefL}
{addJsDefL name='tmml_confirm_text'}{l s='Confirm' mod='tmmegalayout'}{/addJsDefL}
{addJsDefL name='tmml_class_validate_error'}{l s='One of specific classes is invalid' mod='tmmegalayout'}{/addJsDefL}
{addJsDefL name='tmml_cols_validate_error'}{l s='At least one column size must be checked' mod='tmmegalayout'}{/addJsDefL}
{addJsDefL name='tmml_loading_text'}{l s='Loading...' mod='tmmegalayout'}{/addJsDefL}
{addJsDefL name='tmml_layout_validate_error_text'}{l s='Layout name is invalid. Only latin letters, arabic numbers and "-"(not first symbol) can be used.' mod='tmmegalayout'}{/addJsDefL}
{addJsDefL name='tmml_wrapper_heading'}{l s='Wrapper' mod='tmmegalayout'}{/addJsDefL}
{addJsDefL name='tmml_row_heading'}{l s='Row' mod='tmmegalayout'}{/addJsDefL}
{addJsDefL name='tmml_col_heading'}{l s='Column' mod='tmmegalayout'}{/addJsDefL}
{addJsDefL name='tmml_module_heading'}{l s='Module' mod='tmmegalayout'}{/addJsDefL}