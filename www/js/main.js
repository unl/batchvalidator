WDN.loadJQuery(function() {
    var validator = (function ($) {
        var validatorForm = $("#validator-form"), wrapper = $("#scan-wrapper"), api_url = "api.php?uri=", 
        loader = $('.loader'), uri, url_check = /^(((http|https):\/\/)|www\.)[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#!]*[\w\-\@?^=%&amp;\/~\+#])\//;

        return {

            initialize : function () {
                var the_url = $("#uri");
                the_url.keyup(function () {
                    if (url_check.test(this.value)) {
                        $("#submit").removeAttr('disabled');
                    } else {
                        $("#submit").attr('disabled', '');
                    }
                })
                validatorForm.submit(function () {
                    event.preventDefault();
                    if (url_check.test(the_url.val())) {
                        uri = $("#uri").val();
                        validator.initialQuery();
                    } else {
                        the_url.after('Ugh, that is not a URL.');
                    }
                });
                wrapper.on('begin', validator.beginQueue);
            },

            initialQuery : function () {
                $.getJSON(api_url+encodeURIComponent(uri), function (data) {
                    if (data.last_scan) {
                        validator.loadSummaryTemplate(data);
                    } else { // This site hasn't been scanned, so start the queue
                        validator.loadSummaryTemplate(data); // Show the barebones as an underlay.
                        wrapper.trigger('begin'); // Start the queue
                        validator.subsequentQuery(); // POST the queue to get it going
                    }
                });
            },

            loadSummaryTemplate : function (data) {
                var summaryTemplate = Handlebars.compile($("#temp-validator-results").html()),
                render = summaryTemplate(data),
                output = wrapper.html(render).fadeIn(700);
                $('html, body').animate({
                    scrollTop: wrapper.offset().top - 15
                }, 500);
                $('.recheck-button').click(function(event) {
                    event.preventDefault();
                    wrapper.trigger('begin'); // Start the queue
                    validator.subsequentQuery(); // POST the queue to get it going
                });
            },

            subsequentQuery : function () {
                $.post(api_url + encodeURIComponent(uri), 'action=check', function(data) {
                    validator.loadSummaryTemplate(data);
                }, "json");
            },

            beginQueue : function () {
                $('#validator-results-setup').css({"opacity" : 0.05});
                loader.clone().appendTo(wrapper).show();
                $('html, body').animate({
                    scrollTop: wrapper.offset().top - 15
                }, 500);
            },


        }

    }(WDN.jQuery));

    validator.initialize();
});

// Handlebars helpers
Handlebars.registerHelper('percentage', function (current, total) {
    var percentage = Math.round(current/total * 100);
    if (isNaN(percentage)) {
        return '!';
    }
    return percentage + "%";
});

Handlebars.registerHelper('strip_site', function (page) {
    var site = WDN.jQuery("#uri").val();
    return page.replace(site, "/"); //Keep the slash?
});

Handlebars.registerHelper('format_boolean', function (marker) {
    if (!marker) {
        return '&#x2716;';
    }
    return "&#x2714;";
});

Handlebars.registerHelper('links', function (links) {
    var total = 0;
    if (links["301"]){
        total = links["301"].length;
    }
    if (links["404"]) {
        total = total + links["404"].length;
    }
    return total;
});

// Error Reporting
Handlebars.registerHelper('error_percentage', function (current, total) {
    var percentage = Math.round(current/total * 100);
    if (isNaN(percentage) || percentage < 100) {
        return 'error';
    }
});

Handlebars.registerHelper('error_total', function (total) {
    if (parseFloat(total) !== 0) {
        return 'error';
    }
});

Handlebars.registerHelper('error_boolean', function (marker) {
    if (!marker) {
        return 'error';
    }
});