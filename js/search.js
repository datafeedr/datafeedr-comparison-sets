jQuery(function ($) {

    // Load search results.
    $("#dfrcs_search_form_wrapper").on("click", "#dfrcs_search", function (e) {

        // Disable search and save search buttons.
        $("#dfrcs_search").addClass("button-disabled").val(dfrcs.searching);

        var hash = $('input#dfrcs_hash').val();

        // Display "loading..." image and "Searching products..." text
        $("#div_dfrcs_search_results").html('<div class="dfrcs_loading"></div><div class="dfrcs_searching_x_products">' + dfrcs.searching_products + '</div>');

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action: "dfrcs_ajax_get_products",
                dfrcs_security: dfrcs.nonce,
                query: $("form .filter:visible :input").serialize(),
                hash: hash
            }
        }).done(function (html) {

            // Undisable search and save search buttons.
            $("#dfrcs_search").removeClass("button-disabled").val(dfrcs.search);

            // Display "Save Search" button & "view api request" link.
            $(".dfrcs_raw_query").show();

            // Display search results.
            $("#div_dfrcs_search_results").fadeOut(100).hide().fadeIn(100).html(html);

        });

        e.preventDefault();

    });

    $("#dfrcs_search_form_wrapper").on("click", "#dfrcs_view_raw_query", function (e) {
        $("#dfrcs_raw_api_query").slideToggle();
        e.preventDefault();
    });

    // Add product to compset.
    $("#div_dfrcs_search_results").on("click", ".add", function (e) {

        var domElement = $(this).get(0);

        var pid = $(this).data("dfrcs-pid");
        var hash = $('input#dfrcs_hash').val();

        $(domElement).removeClass('add').text('Adding...');

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action: "dfrcs_add_product",
                dfrcs_security: dfrcs.nonce,
                pid: pid,
                hash: hash
            }
        }).done(function (html) {
            $(domElement).addClass('included').text('Added');
        });

        e.preventDefault();

    });

});



