WDN.loadJQuery(function() {
    var validator = (function ($) {
        var validatorForm = $("#validator-form"), wrapper = $("#scan-wrapper"), api_url = "api.php?uri=", 
        loader = $('.loader').not('.mini'), mini_loader = $('.loader.mini'), uri, url_check = /^(((http|https):\/\/)|www\.)[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#!]*[\w\-\@?^=%&amp;\/~\+#])\//;

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
                validatorForm.on('submit' , function (event) {
                    event.preventDefault();
                    uri = $("#uri").val();
                    validator.initialQuery();
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
                // Bind events to elements inside summary
                $('.recheck-button').click(function(event) {
                    event.preventDefault();
                    wrapper.trigger('begin'); // Start the queue
                });
                $('#validator-results tr[data-page]').on('click', function (event) {
                    var current_tr = $(this);
                    var next_tr = current_tr.next('.expansion-row');
                    validator.beginHTMLValidation(current_tr, next_tr);
                    validator.showSubRow(current_tr, next_tr);
                });
            },

            subsequentQuery : function () {
                $.post(api_url + encodeURIComponent(uri), 'action=check', function(data) {
                    validator.loadSummaryTemplate(data);
                }, "json");
            },

            beginQueue : function () {
                validator.subsequentQuery(); // POST the queue to get it going
                $('#validator-results-setup').css({"opacity" : 0.05});
                loader.clone().appendTo(wrapper).show();
                $('html, body').animate({
                    scrollTop: wrapper.offset().top - 15
                }, 500);
            },

            showSubRow : function (tr, next_tr) {
                WDN.log('showing sub row');
                next_tr.toggle(400);
                tr.off('click').on('click', function () {
                    validator.hideSubRow(tr, next_tr);
                }); // Remove the current click event and add a new one to close
            },

            hideSubRow : function (tr, next_tr) {
                WDN.log('hiding sub row');
                next_tr.toggle(400);
                tr.off('click').on('click', function () {
                    validator.beginHTMLValidation(tr, next_tr);
                    validator.showSubRow(tr, next_tr);
                })
            },

            beginHTMLValidation : function (tr, tr_next) {
                var error_wrapper = tr_next.find('.html-errors-wrapper');
                error_wrapper.empty();
                // show a spinner
                mini_loader.clone().appendTo(error_wrapper).show();
                validator.getHTMLValidationResults(tr.attr('data-page'), error_wrapper);
            },

            getHTMLValidationResults : function (page, wrapper) {
                $.getJSON(api_url + encodeURIComponent(uri) + '&page=' + encodeURIComponent(page) + '&action=html_errors', function (data) {
                    validator.showHTMLValidationResults(data, wrapper);
                });
            },

            showHTMLValidationResults : function (data, wrapper) {
                var summaryTemplate = Handlebars.compile($("#temp-html-validator-results").html()),
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
    return page.replace(site, "/");
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