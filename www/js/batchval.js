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
	// uriDiv.removeClass('true false');
	uriDiv.addClass('validating');
	// Tell the user we're loading the result
	// uriDiv.append('<img class="loading" src="/wdn/templates_3.0/css/header/images/colorbox/loading.gif" />');
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
		uriDiv.addClass('true');
		return;
	}

	uriDiv.addClass('false');

}
