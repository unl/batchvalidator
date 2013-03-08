WDN.loadJQuery(function() {
    var validator = (function ($) {
        var validatorForm = $("#validator-form"), uri;

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
                var summaryTemplate = Handelbars.compile($("#temp-validator-results").html()),
                render = summaryTemplate(data),
                output = validatorForm.after(render).fadeIn(700);
            }
        }

    }(WDN.jQuery));

    validator.initialize();
});