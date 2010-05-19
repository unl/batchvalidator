WDN.jQuery(document).ready(function(){
	
	
})

function validateAll()
{
	WDN.jQuery('.uri').each(function(){
		// Grab the URI
		var uri = WDN.jQuery(this).html();
		
		var uriDiv = WDN.jQuery(this).parent();
		
		WDN.jQuery('body').queue('validation', function() {
			checkValidity(uri, uriDiv)
		});
	});
	WDN.jQuery('body').dequeue('validation');
}

function validateInvalid() {
	//scroll to first Invalid
	var falseDiv = WDN.jQuery(".false:first").offset();
	window.scroll(0, falseDiv.top);

	WDN.jQuery('.false .uri').each(function(){
		// Grab the URI
		var uri = WDN.jQuery(this).html();
		var uriDiv = WDN.jQuery(this).parent();
		WDN.jQuery('body').queue('validation', function() {
			checkValidity(uri, uriDiv)
		});
	});
	WDN.jQuery('body').dequeue('validation');
}

function checkValidity(uri, uriDiv)
{
	uriDiv.addClass('validating');
	// Fetch the validator results in JSON format.
	WDN.get('validator.php?base='+baseURI+'&u='+escape(uri), null, function(result) {
		handleJSONResult(result, uriDiv);
	}, 'json');
}

function handleJSONResult(result, uriDiv)
{
	WDN.log(result);
	uriDiv.removeClass('validating');
	
	// Advance the queue
	WDN.jQuery('body').dequeue('validation');
	
	if (result.validity) {
		// It is valid, say no more!
		uriDiv.removeClass('unknown false');
		uriDiv.addClass('true');
		
		return;
	}
	
	uriDiv.removeClass('unknown true');
	uriDiv.addClass('false');
	uriDiv.children('span').append("<a href='#' class='errors'>"+result.errors.length+" Error(s)</a> <a href='#' class='warnings'>"+result.warnings.length+" Warning(s)</a>");

}
