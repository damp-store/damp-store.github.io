/*
* 2002-2015 TemplateMonster
*
* TemplateMonster Header Account Block
*
* NOTICE OF LICENSE
*
* This source file is subject to the General Public License (GPL 2.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/GPL-2.0

* @author     TemplateMonster (Alexander Grosul)
* @copyright  2002-2015 TemplateMonster
* @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

$(document).ready(function(){
	$('#HeaderSubmitLogin').on('click', function(e){
		e.preventDefault();
		submitLoginFunction();
	});
	$(document).keypress(function(e) {
		if(e.which == 13) {
			e.preventDefault();
			submitLoginFunction();
		}
	});
});

function submitLoginFunction()
{
	$.ajax({
		type: 'POST',
		url: baseUri,
		async: true,
		cache: false,
		dataType : "json",
		data: 
		{
			controller: 'authentication',
			SubmitLogin: 1,
			ajax: true,
			email: $('#header-email').val(),
			passwd: $('#header-passwd').val(),
			token: token
		},
		success: function(jsonData)
		{
			if (jsonData.hasError) 
			{
				var errors = '';
				for(error in jsonData.errors)
					if(error != 'indexOf')
						errors += '<li>' + jsonData.errors[error] + '</li>';
				$('#create_header_account_error').html('<ol>' + errors + '</ol>').slideDown();
			}
			else
			{
				document.location.reload();
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			error = "TECHNICAL ERROR: unable to load form.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
			if (!!$.prototype.fancybox)
			{
			    $.fancybox.open([
		        {
		            type: 'inline',
		            autoScale: true,
		            minHeight: 30,
		            content: "<p class='fancybox-error'>" + error + '</p>'
		        }],
				{
			        padding: 0
			    });
			}
			else
			    alert(error);
		}
	});
}