WDN.loadJQuery(function() {
    var validator = (function ($) {
        var validatorForm = $("#validator-form"), wrapper = $("#scan-wrapper"), api_url = "api.php?uri=", 
        loader = $('.loader').not('.mini'), submit_button = $("#submit"), mini_loader = $('.loader.mini'), uri, form_disabled = true, 
        url_check = /^(((http|https):\/\/)|www\.)[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#!]*[\w\-\@?^=%&amp;\/~\+#])\//;

        return {

            initialize : function () {
                var the_url = $("#uri");
                the_url.keyup(function () {
                    validator.validateURL(this.value);
                });
                if (baseURI) { // baseURI passed through query string
                    if (validator.validateURL(baseURI) && form_disabled === false) {
                        validator.submitValidationRequest();
                    }
                }
                validatorForm.on('submit' , function (event) {
                    event.preventDefault();
                    if (form_disabled === false) {
                        validator.submitValidationRequest();
                    }
                });
                wrapper.on('begin', validator.beginQueue);
            },

            submitValidationRequest : function () {
                submit_button.val('Checking...');
                uri = $("#uri").val();
                validator.querySiteInformation();
            },

            validateURL : function (test) {
                if (url_check.test(test)) {
                    $("#submit").removeAttr('disabled');
                    form_disabled = false;
                    return true;
                } 
                $("#submit").attr('disabled', '');
                form_disabled = true;
                return false;
            },

            querySiteInformation : function () {
                $.getJSON(api_url+encodeURIComponent(uri), function (data) {
                    if (!data.status) { //Site has never been checked
                        wrapper.trigger('begin'); // Start the queue
                    } else if (data.status == 'complete') { //Queue has completed...
                        validator.loadSummaryTemplate(data);
                        
                        $('.loader').remove(); //Remove the spinner
                        
                        wrapper.fadeIn(700); //Display the wrapper
                        
                        $('html, body').animate({
                            scrollTop: wrapper.offset().top - 15
                        }, 500);
                    } else { // This site is being scanned
                        if ($('#scan-container > .loader').length == 0) {
                            loader.clone().appendTo($('#scan-container')).show(); //Show the spinner if it isn't already visible.
                        }

                        validator.loadSummaryTemplate(data); //Show the current results under the spinner

                        //Poll the server again in 5 seconds until complete.
                        setTimeout(function()
                        {
                            validator.querySiteInformation()
                        }, 5000);
                    }
                });
            },
            
            loadSummaryTemplate : function (data) {
                var summaryTemplate = Handlebars.compile($("#temp-validator-results").html()),
                render = summaryTemplate(data);
                wrapper.html(render);
                
                submit_button.val('Check');
                
                // Bind events to elements inside summary
                $('.recheck-button').click(function (event) {
                    event.preventDefault();
                    wrapper.trigger('begin'); // Start the queue
                });
                $('.external-site').on('click', function (event) {
                    event.stopPropagation();
                    window.open(this.href);
                    return false;
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
                    validator.querySiteInformation();
                    
                    validator.loadSummaryTemplate(data);
                }, "json");
            },

            beginQueue : function () {
                validator.subsequentQuery(); // POST the queue to get it going
                
                loader.clone().appendTo($('#scan-container')).show(); //Show the spinner
                
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

Handlebars.registerHelper('format_version', function (version) {
    if (!version || version == 'unknown') {
        version = '-unknown';
    }

    return '(v' + version + ')';
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