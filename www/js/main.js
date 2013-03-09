WDN.loadJQuery(function() {
    var validator = (function ($) {
        var validatorForm = $("#validator-form"), wrapper = $("#scan-wrapper"), uri;

        return {

            initialize : function () {
                validatorForm.submit(function () {
                    event.preventDefault();
                    uri = $("#uri").val();
                    validator.query();
                })
            },

            query : function () {
                $.getJSON("api.php?uri="+encodeURIComponent(uri), function (data) {
                    validator.loadSummaryTemplate(data);
                });
            },

            loadSummaryTemplate : function (data) {
                var summaryTemplate = Handlebars.compile($("#temp-validator-results").html()),
                render = summaryTemplate(data),
                output = wrapper.html(render).fadeIn(700);
            }
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