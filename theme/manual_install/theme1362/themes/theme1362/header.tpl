<!DOCTYPE HTML>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"{if isset($language_code) && $language_code} lang="{$language_code|escape:'html':'UTF-8'}"{/if}><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8 ie7"{if isset($language_code) && $language_code} lang="{$language_code|escape:'html':'UTF-8'}"{/if}><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9 ie8"{if isset($language_code) && $language_code} lang="{$language_code|escape:'html':'UTF-8'}"{/if}><![endif]-->
<!--[if gt IE 8]> <html class="no-js ie9"{if isset($language_code) && $language_code} lang="{$language_code|escape:'html':'UTF-8'}"{/if}><![endif]-->
<html{if isset($language_code) && $language_code} lang="{$language_code|escape:'html':'UTF-8'}"{/if}><head>
    <meta charset="utf-8" />
    <title>{$meta_title|escape:'html':'UTF-8'}</title>
  
  
    {if isset($meta_description) && $meta_description}
      <meta name="description" content="{$meta_description|escape:'html':'UTF-8'}" />
    {/if}
    {if isset($meta_keywords) && $meta_keywords}
      <meta name="keywords" content="{$meta_keywords|escape:'html':'UTF-8'}" />
    {/if}
    <meta name="generator" content="PrestaShop" />
    <meta name="robots" content="{if isset($nobots)}no{/if}index,{if isset($nofollow) && $nofollow}no{/if}follow" />
    <meta name="viewport" content="width=device-width, minimum-scale=0.25, maximum-scale=1.0, initial-scale=1.0" /> 
    <meta name="apple-mobile-web-app-capable" content="yes" /> 
    <link rel="icon" type="image/vnd.microsoft.icon" href="{$favicon_url}?{$img_update_time}" />
    <link rel="shortcut icon" type="image/x-icon" href="{$favicon_url}?{$img_update_time}" />
    {if isset($css_files)}
      {foreach from=$css_files key=css_uri item=media}
        <link rel="stylesheet" href="{$css_uri}" media="{$media}" />
      {/foreach}
    {/if}
    {if isset($js_defer) && !$js_defer && isset($js_files) && isset($js_def)}
      {$js_def}
      {foreach from=$js_files item=js_uri}
        <script src="{$js_uri|escape:'html':'UTF-8'}"></script>
      {/foreach}
    {/if}
    {$HOOK_HEADER}
    <link rel="stylesheet" href="http{if Tools::usingSecureMode()}s{/if}://fonts.googleapis.com/css?family=Ubuntu:300,400&subset=latin,greek,greek-ext,cyrillic-ext,latin-ext,cyrillic" media="all" rel='stylesheet' type='text/css' />

    <link rel="stylesheet" href="http{if Tools::usingSecureMode()}s{/if}://fonts.googleapis.com/css?family=Ubuntu:500,700&subset=latin,greek,greek-ext,cyrillic-ext,latin-ext,cyrillic" media="all" rel='stylesheet' type='text/css' />

    {if (($hide_left_column || $hide_right_column) && ($hide_left_column !='true' || $hide_right_column !='true')) && !$content_only}
      {assign var="columns" value="2"}
    {elseif (($hide_left_column && $hide_right_column) && ($hide_left_column =='true' && $hide_right_column =='true')) && !$content_only}
      {assign var="columns" value="1"}
    {elseif $content_only}
      {assign var="columns" value="1"}
    {else}
     {assign var="columns" value="3"}
   {/if}
  </head>
  <body{if isset($page_name)} id="{$page_name|escape:'html':'UTF-8'}"{/if} class="{if isset($page_name)}{$page_name|escape:'html':'UTF-8'}{/if}{if isset($body_classes) && $body_classes|@count} {implode value=$body_classes separator=' '}{/if}{if $hide_left_column} hide-left-column{else} show-left-column{/if}{if $hide_right_column} hide-right-column{else} show-right-column{/if}{if isset($content_only) && $content_only} content_only{/if} lang_{$lang_iso} {if !$content_only}{if $columns == 2} two-columns{elseif $columns == 3} three-columns{else} one-column{/if}{/if}">
  {if !isset($content_only) || !$content_only}
  <!--For older internet explorer-->
    <div class="old-ie">
      <a href="http://windows.microsoft.com/en-US/internet-explorer/..">
        <img src="{$img_dir}ie8-panel/warning_bar_0000_us.jpg" height="42" width="820" alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today."/>
      </a>
    </div>
  <!--END block for older internet explorer-->
    {if isset($restricted_country_mode) && $restricted_country_mode}
      <div id="restricted-country">
        <p>{l s='You cannot place a new order from your country.'}{if isset($geolocation_country) && $geolocation_country} <span class="bold">{$geolocation_country|escape:'html':'UTF-8'}</span>{/if}</p>
      </div>
    {/if}
    <div id="page">
      <div id="preloader">
          <div class="cssload-dots"></div>
      </div>
      <!-- #preloader -->
      <div class="header-container">
        <header id="header">
          {capture name='displayBanner'}{hook h='displayBanner'}{/capture}
          {if $smarty.capture.displayBanner}
            <div class="banner">
              <div class="container">
                <div class="row">
                  {$smarty.capture.displayBanner}
                </div>
              </div>
            </div>
          {/if}
          {capture name='displayNav'}{hook h='displayNav'}{/capture}
          {if $smarty.capture.displayNav}
            <div class="nav">
              <div class="container">
                <div class="row">
                  <nav>{$smarty.capture.displayNav}</nav>
                </div>
              </div>
            </div>
          {/if}
          <div class="top">
            <div class="container">
              <div class="row">
                <div id="header_logo">
                  <a href="{if isset($force_ssl) && $force_ssl}{$base_dir_ssl}{else}{$base_dir}{/if}" title="{$shop_name|escape:'html':'UTF-8'}">
                    <img class="logo img-responsive" src="{$logo_url}" alt="{$shop_name|escape:'html':'UTF-8'}"{if isset($logo_image_width) && $logo_image_width} width="{$logo_image_width}"{/if}{if isset($logo_image_height) && $logo_image_height} height="{$logo_image_height}"{/if}/>
                  </a>
                </div>
                {if isset($HOOK_TOP)}{$HOOK_TOP}{/if}
              </div>
            </div>
          </div>
        </header>
      </div>
      <div class="columns-container">
        {assign var='displayMegaTopColumn' value={hook h='tmMegaLayoutTopColumn'}}
          {if $displayMegaTopColumn && $page_name == 'index'}
              {hook h='tmMegaLayoutTopColumn'}
          {else}
              {capture name='displayTopColumn'}{hook h='displayTopColumn'}{/capture}
              {if $smarty.capture.displayTopColumn}
                  <div id="slider_row" class="row">
                      <div id="top_column" class="center_column col-xs-12 col-sm-12">{$smarty.capture.displayTopColumn}</div>
                  </div>
              {/if}
          {/if}
        
        <div id="columns" class="container">
          {if $page_name !='index' && $page_name !='pagenotfound'}
            {include file="$tpl_dir./breadcrumb.tpl"}
          {/if}
          <div class="row">
            <div class="large-left col-sm-{12 - $right_column_size}">
              <div class="row">
                <div id="center_column" class="center_column col-xs-12 col-sm-{12 - $left_column_size}">
  {/if}