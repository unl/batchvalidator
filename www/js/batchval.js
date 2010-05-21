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
	uriDiv.children('span').append('<a href="#" class="errors" onclick="showResults(\''+uriDiv.attr("id")+'\'); return false;">'+result.errors.length+' Error(s)</a> <a href="#" class="errors" onclick="showResults(\''+uriDiv.attr("id")+'\'); return false;">'+result.warnings.length+' Warning(s)</a>');
	uriDiv.append("<div class='details'></div>");
	uriDiv.children('.details').append("<div class='errorDetails'><h3 class='sec_main'>Errors</h3></div>");
	for (var j=0; j<result.errors.length; j++) {
		uriDiv.children('.details').children('.errorDetails').append("<div><h4><em>Line "+result.errors[j].line +", Column "+ result.errors[j].col + ":</em> "+ result.errors[j].message +"</h4><pre>"+ result.errors[j].source + "</pre>" + result.errors[j].explanation + "</div>");
	}
	uriDiv.children('.details').append("<div class='warningDetails'><h3 class='sec_main'>Warnings</h3></div>");
	for (var i=0; i<result.warnings.length; i++) {
		uriDiv.children('.details').children('.warningDetails').append("<div><h4><em>Line "+result.warnings[i].line +", Column "+ result.warnings[i].col + ":</em> "+ result.warnings[i].message +"</h4><pre>"+ result.warnings[i].source + "</pre>" + result.warnings[i].explanation + "</div>");
	}
}
function showResults(errorDiv) {
	WDN.jQuery('#'+errorDiv).colorbox({inline:true, maxWidth:'940px', height:'60%', href:'#'+errorDiv+" .details"});
}