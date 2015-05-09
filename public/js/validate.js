head.js(
    KAM_ROOT + "js/jquery.validate.js",
    function () {
        jQueryKam(function ($) {
            $("form[validate]").validate({
                errorClass : "invalid",
                errorElement : "span",
                messages : {
                    required : tr("This field is required"),
                    remote : tr("Please fix this field"),
                    email : tr("Please enter a valid email address"),
                    url : tr("Please enter a valid URL"),
                    date : tr("Please enter a valid date"),
                    dateISO : tr("Please enter a valid date (ISO)"),
                    number : tr("Please enter a valid number"),
                    digits : tr("Please enter only digits"),
                    creditcard : tr("Please enter a valid credit card number"),
                    equalTo : tr("Please enter the same value again"),
                    maxlength : $.validator.format(tr("Please enter no more than {0} characters")),
                    minlength : $.validator.format(tr("Please enter at least {0} characters")),
                    rangelength : $.validator.format(tr("Please enter a value between {0} and {1} characters long")),
                    range : $.validator.format(tr("Please enter a value between {0} and {1}")),
                    max : $.validator.format(tr("Please enter a value less than or equal to {0}")),
                    min : $.validator.format(tr("Please enter a value greater than or equal to {0}"))
                }
            });
        });
    }
);

