WDN.loadJQuery(function() {
    var validator = (function ($) {
        var validatorForm = $("#validator-form"),
        wrapper = $("#scan-wrapper"),
        api_url = "api.php?uri=",
        pollTimeout = false,
        loader = $('.loader'),
        submit_button = $("#submit"),
        uri,
        form_disabled = true,
        waiting = false;
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
                wrapper.on('waiting', function (event, data) {
                    validator.loadWaitingTemplate(data);
                });
                wrapper.on('waiting', function (event, data) {
                    validator.loadSummaryTemplate(data);
                });
                wrapper.on('complete', function (event, data) {
                    validator.loadSummaryTemplate(data);
                });
                wrapper.on('complete', validator.clearWaiting);
            },

            submitValidationRequest : function () {
                submit_button.val('Checking...');
                uri = $("#uri").val();
                validator.querySiteInformation();

                if (history.pushState) {
                    history.pushState(null, null, '?uri=' + encodeURIComponent(uri));
                    WDN.analytics.callTrackPageview(window.location);
                }

                // This is a new query, so reset the waiting var
                waiting = false;
                //clear any remaining timeouts
                clearTimeout(validator.pollTimeout);
            },

            submitContactEmailRequest : function () {
                $("#email-submit").val('Submitting...');

                var data = $('#email-contact-form').serializeArray();
                data.push({name: 'action', value: 'contact_email'});

                $.post(api_url + encodeURIComponent(uri), data , function(data) {
                    waiting = false;
                    wrapper.trigger('waiting', data);
                }, "json");
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
                    } else if (data.status == 'complete' || data.status == 'timeout' || data.status == 'restricted' || data.status == 'error') { //Queue has completed...
                        wrapper.trigger('complete', data);
                        validator.loadSummaryTemplate(data);

                        wrapper.fadeIn(700); //Display the wrapper

                        $('html, body').animate({
                            scrollTop: wrapper.offset().top - 15
                        }, 500);
                    } else { // This site is being scanned
                        wrapper.trigger('waiting', data);

                        //Poll the server again in 5 seconds until complete.
                        validator.pollTimeout = setTimeout(function()
                        {
                            validator.querySiteInformation();
                        }, 5000);
                    }
                });
            },

            loadWaitingTemplate : function (data) {
                if (waiting) { // If we're already waiting, no need to do anything
                    validator.showQueuePlacement(data);
                    return;
                }

                var contactTemplate = Handlebars.compile($("#temp-waiting").html()),
                    render = contactTemplate(data);

                $('#scan-waiting').html(render).show();

                if (WDN.idm.user.mail) {
                    $('#email').val(WDN.idm.user.mail[0]);
                }

                loader.clone().appendTo($('#spinner-wrapper')).show();
                validator.showQueuePlacement(data, true);

                // set the waiting var
                waiting = true;

                // register a watcher
                $("#email-contact-form").one('submit', function (event) {
                    event.preventDefault();
                    validator.submitContactEmailRequest();
                });
            },

            clearWaiting : function () {
                waiting = false;
                $('#scan-waiting').hide();
            },

            showQueuePlacement : function (data, initialDisplay) {
                var queueTemplate = Handlebars.compile($("#temp-queueplacement").html()),
                render = queueTemplate(data);
                if (initialDisplay) {
                    $('#queueplacement-wrapper').hide().html(render).slideDown();
                } else {
                    $('#queueplacement-wrapper').html(render);
                }
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

            rerunCheck : function () { // Do another full site check
                $.post(api_url + encodeURIComponent(uri), 'action=check', function(data) {
                    validator.querySiteInformation();
                    validator.loadSummaryTemplate(data);
                }, "json");
            },

            beginQueue : function () {
                validator.rerunCheck(); // POST the queue to get it going

                wrapper.trigger('waiting', {});

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
                });
            },

            beginHTMLValidation : function (tr, tr_next) {
                var error_wrapper = tr_next.find('.html-errors-wrapper');
                error_wrapper.empty();
                // show a spinner
                loader.clone().appendTo(error_wrapper).show();
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
        };

    }(WDN.jQuery));

    validator.initialize();
});

var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-3203435-15']);
_gaq.push(['_trackPageview']);

(function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();

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

Handlebars.registerHelper('homepage_only', function (page_limit, options) {
    if (page_limit == 1) {
        return options.fn(this);
    }

    return options.inverse(this);
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

Handlebars.registerHelper('status_timeout', function (status, options) {
    if (status == 'timeout') {
        return options.fn(this);
    }

    return options.inverse(this);
});

Handlebars.registerHelper('status_error', function (status, options) {
    if (status == 'error') {
        return options.fn(this);
    }

    return options.inverse(this);
});

Handlebars.registerHelper('status_restricted', function (status, options) {
    if (status == 'restricted') {
        return options.fn(this);
    }

    return options.inverse(this);
});

Handlebars.registerHelper('grid_2006', function (count, options) {
    if (count > 0) {
        return options.fn(this);
    }

    return options.inverse(this);
});

Handlebars.registerHelper('has_logged_links', function (logged_links, options) {
    if (typeof logged_links !== 'undefined' && logged_links.length > 0) {
        return options.fn(this);
    }

    return options.inverse(this);
});

Handlebars.registerHelper('position', function (position) {
    if (position === 0) {
        return '<p class="indicator-bar its-on">Your Queue Placement: <span class="spot">Now Checking</span></p>';
    }

    return '<p class="indicator-bar">Your Queue Placement: <span class="spot">' + position + '</span></p>';
});
