jQuery.ajaxWithProgress = function (params) {
    params.xhr = function () {
        var xhr = jQuery.ajaxSetup().xhr();
        xhr.addEventListener("readystatechange", function () {
            if (j && j._progress)
                j._progress(this.responseText)
        });
        return xhr;
    };

    var j = jQuery.ajax(params);
    j.progress = function (fn) {
        this._progress = fn;
    };
    return j;
};


jQuery(function ($) {

    // Output compset via AJAX.
    $('.dfrcs_ajax[data-dfrcs]').each(function () {
        var t = $(this);
        t.addClass('dfrcs_loading');
        $.ajaxWithProgress({
            type: "POST",
            url: dfrcs.ajax_url,
            data: {
                action: "dfrcs_output_compset_ajax",
                dfrcs_security: dfrcs.nonce,
                source: t.data('dfrcs')
            }
        }).progress(function (html) {
            t.removeClass('dfrcs_loading');
            t.html(html);
        });
    });

    // Show debug.
    $(".dfrcs").on("click", ".dfrcs_compset_actions > .debug", function (e) {
        var compset_id = $(this).closest(".dfrcs").children(".dfrcs_inner").attr("id");
        $("#" + compset_id + " .dfrcs_compset_debug").toggle("slow");

        e.preventDefault();
    });

    // Show admin actions and show removed products.
    $(".dfrcs").on("click", ".dfrcs_compset_actions > .manage", function (e) {
        var compset_id = $(this).closest(".dfrcs").children(".dfrcs_inner").attr("id");
        $("#" + compset_id + " .dfrcs_product_actions").toggle("slow");
        $("#" + compset_id + " .dfrcs_removed").toggle("slow");

        e.preventDefault();
    });

    // Refresh cache.
    $(".dfrcs").on("click", ".dfrcs_compset_actions > .refresh", function (e) {

        var source = $(this).closest(".dfrcs").children(".dfrcs_inner").data("dfrcs");
        var compset_id = "#" + $(this).closest(".dfrcs").children(".dfrcs_inner").attr("id");

        $(compset_id).empty().addClass('dfrcs_loading');

        $.ajax({
            type: "POST",
            url: dfrcs.ajax_url,
            data: {
                action: "dfrcs_refresh_compset_ajax",
                dfrcs_security: dfrcs.nonce,
                source: source,
                post_id: dfrcs.post_id,
                hash: compset_id
            }
        }).done(function (html) {
            $(compset_id).removeClass('dfrcs_loading').html(html);
        });

        e.preventDefault();

    });

    // Remove product from compset.
    $(".dfrcs").on("click", ".dfrcs_product_actions > .dfrcs_remove_product", function (e) {

        var pid = $(this).data("dfrcs-pid");
        var status = $(this).data("dfrcs-status");
        var compset_id = "#" + $(this).closest(".dfrcs").children(".dfrcs_inner").attr("id");
        var row = compset_id + ' .dfrcs_product_' + pid;

        $(row).slideUp("1000", function () {
            $(row).toggleClass('dfrcs_removed');
        });

        $.ajax({
            type: "POST",
            url: dfrcs.ajax_url,
            data: {
                action: "dfrcs_remove_product",
                dfrcs_security: dfrcs.nonce,
                status: status,
                pid: pid,
                hash: compset_id
            }
        }).done(function (html) {
            $(row).slideUp(1000, function () {
                $(row).remove();
            });
        });

        e.preventDefault();

    });

    // Unremove product from compset.
    $(".dfrcs").on("click", ".dfrcs_product_actions > .dfrcs_restore_product", function (e) {

        var pid = $(this).data("dfrcs-pid");
        var status = $(this).data("dfrcs-status");
        var compset_id = "#" + $(this).closest(".dfrcs").children(".dfrcs_inner").attr("id");
        var row = compset_id + ' .dfrcs_product_' + pid;

        $(row).toggleClass('dfrcs_removed');

        $.ajax({
            type: "POST",
            url: dfrcs.ajax_url,
            data: {
                action: "dfrcs_restore_product",
                dfrcs_security: dfrcs.nonce,
                status: status,
                pid: pid,
                hash: compset_id
            }
        }).done(function (html) {
            $(row + ' .dfrcs_remove_product').replaceWith("Restored!").fadeIn();
        });

        e.preventDefault();

    });

    // http://swip.codylindley.com/popupWindowDemo.html
    $.fn.popupWindow = function (instanceSettings) {

        return this.each(function () {

            $(this).click(function () {

                $.fn.popupWindow.defaultSettings = {
                    centerBrowser: 0, // center window over browser window? {1 (YES) or 0 (NO)}. overrides top and left
                    centerScreen: 0, // center window over entire screen? {1 (YES) or 0 (NO)}. overrides top and left
                    height: 500, // sets the height in pixels of the window.
                    left: 0, // left position when the window appears.
                    location: 0, // determines whether the address bar is displayed {1 (YES) or 0 (NO)}.
                    menubar: 0, // determines whether the menu bar is displayed {1 (YES) or 0 (NO)}.
                    resizable: 0, // whether the window can be resized {1 (YES) or 0 (NO)}. Can also be overloaded using resizable.
                    scrollbars: 1, // determines whether scrollbars appear on the window {1 (YES) or 0 (NO)}.
                    status: 0, // whether a status line appears at the bottom of the window {1 (YES) or 0 (NO)}.
                    width: 500, // sets the width in pixels of the window.
                    windowName: null, // name of window set from the name attribute of the element that invokes the click
                    windowURL: null, // url used for the popup
                    top: 0, // top position when the window appears.
                    toolbar: 0 // determines whether a toolbar (includes the forward and back buttons) is displayed {1 (YES) or 0 (NO)}.
                };

                settings = $.extend({}, $.fn.popupWindow.defaultSettings, instanceSettings || {});

                var windowFeatures = 'height=' + settings.height +
                    ',width=' + settings.width +
                    ',toolbar=' + settings.toolbar +
                    ',scrollbars=' + settings.scrollbars +
                    ',status=' + settings.status +
                    ',resizable=' + settings.resizable +
                    ',location=' + settings.location +
                    ',menuBar=' + settings.menubar;

                settings.windowName = this.name || settings.windowName;
                settings.windowURL = this.href || settings.windowURL;
                var centeredY, centeredX;

                if (settings.centerBrowser) {

                    if ($.browser.msie) {//hacked together for IE browsers
                        centeredY = (window.screenTop - 120) + ((((document.documentElement.clientHeight + 120) / 2) - (settings.height / 2)));
                        centeredX = window.screenLeft + ((((document.body.offsetWidth + 20) / 2) - (settings.width / 2)));
                    } else {
                        centeredY = window.screenY + (((window.outerHeight / 2) - (settings.height / 2)));
                        centeredX = window.screenX + (((window.outerWidth / 2) - (settings.width / 2)));
                    }
                    window.open(settings.windowURL, settings.windowName, windowFeatures + ',left=' + centeredX + ',top=' + centeredY).focus();
                } else if (settings.centerScreen) {
                    centeredY = (screen.height - settings.height) / 2;
                    centeredX = (screen.width - settings.width) / 2;
                    window.open(settings.windowURL, settings.windowName, windowFeatures + ',left=' + centeredX + ',top=' + centeredY).focus();
                } else {
                    window.open(settings.windowURL, settings.windowName, windowFeatures + ',left=' + settings.left + ',top=' + settings.top).focus();
                }
                return false;
            });
        });
    };
});


