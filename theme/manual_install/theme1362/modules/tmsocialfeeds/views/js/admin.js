/*
* 2002-2016 TemplateMonster
*
* TM Social Feeds
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

$(document).ready(function() {
	$('.socials-items .hook-title button').on('click',function(){
		if($(this).parent().next('.soc-settings-block').is(':hidden'))
		{
			tempName = $(this).html();
			$(this).text(closeText).addClass('btn-warning');
			$(this).parent().next('.soc-settings-block').slideDown();
		}
		else
		{
			$(this).text(tempName).removeClass('btn-warning');
			$(this).parent().next('.soc-settings-block').slideUp();
		}
	});
});