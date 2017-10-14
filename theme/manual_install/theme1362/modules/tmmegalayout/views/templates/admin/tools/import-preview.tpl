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

{if isset($error)}
    {$error|escape:'quotes':'UTF-8'}
{else}
    <div class="layout-preview-box">
        {l s='Layout name:' mod='tmmegalayout'}
        <span class="layout_name">{$layout_name|escape:'htmlall':'UTF-8'}</span><br>
        {l s='Hook:' mod='tmmegalayout'}
        <span class="hook_name">{$hook_name|escape:'htmlall':'UTF-8'}</span><br>
        {l s='Preview:' mod='tmmegalayout'}
        <div class="tmmegalayout-admin container">
            {$layout_preview|escape:'quotes':'UTF-8'}
        </div>
    </div>
    <button class="btn btn-default center-block" id="importLayoutArchive">{l s='Import' mod='tmmegalayout'}</button> 
{/if}
 