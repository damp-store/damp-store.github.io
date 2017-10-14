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

function sortInit(elNumb){
	if($("#list_"+elNumb+" li").length > 1)
	{
		$("#list_"+elNumb).sortable({
			 cursor: 'move',
			 update: function(event, ui) {
			  $('#list_'+elNumb+' li').not('.no-slides').each(function(index){
				 $(this).find('.sort_order').text(index + 1);
			  });
			}
			 }).bind('sortupdate', function() {
			var test = $(this).sortable('toArray');
			var h4_title = $(this).prev('h4').html();
			var id_shop = $(this).find("input[name='id_shop']").val();
			$.ajax({
				type: 'POST',
				url: theme_url + '&configure=themeconfigurator&ajax',
				headers: { "cache-control": "no-cache" },
				dataType: 'json',
				data: {
					action: 'updateposition',
					item: test,
					title: h4_title,
					id_shop: id_shop
				},
				success: function(msg)
				{
					if (msg.error)
					{
						showErrorMessage(msg.error);
						return;
					}
					showSuccessMessage(msg.success);
				}
			});
		 });
	}
}

$(function() {
	if(shopCount.length)
		for(i=0; i<shopCount.length; i++)
			sortInit(i + 1);
	
	 $(".slides-list li button").on('click', function(){
		
		var id_product = $(this).parents('form').find('input[name="id_product"]').val();
		var id_shop = $(this).parents('form').find('input[name="id_shop"]').val();
		var slide_status = $(this).parents('form').find('input[name="item_status"]').val();
		
		$.ajax({
			type:'POST',
			url:theme_url + '&configure=themeconfigurator&ajax',
			headers: { "cache-control": "no-cache" },
			dataType: 'json',
			data: {
				action: 'updatestatus',
				id_product: id_product,
				id_shop: id_shop,
				slide_status: slide_status
			},
			success: function(msg)
			{
				if (msg.error_status)
				{
					showErrorMessage(msg.error_status);
					return;
				}
				showSuccessMessage(msg.success_status);
			}
		});
		
		if($(this).hasClass('action-disabled'))
			{
				$(this).removeClass('action-disabled').addClass('action-enabled');
				$(this).find('i').removeClass('icon-remove').addClass('icon-check');
				$(this).parent().find('input[name="item_status"]').val(1);
			}
			else
			{
				$(this).removeClass('action-enabled').addClass('action-disabled');
				$(this).find('i').removeClass('icon-check').addClass('icon-remove');
				$(this).parent().find('input[name="item_status"]').val(0);
		}
		
		return false; 
	 });
 });