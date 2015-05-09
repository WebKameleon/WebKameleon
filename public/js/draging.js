kameleon_draging = true;

function km_levelname_display(val, hfb)
{
    if (val) {
        jQueryKam('.km_dragdrop_place').addClass('km_dragdrop_place_active');
        jQueryKam('.km_szpaltanames_' + hfb).show();
        jQueryKam('.km_dragdrop_' + hfb).addClass('km_dragdrop_place_active');
    } else {
        jQueryKam('.km_dragdrop_place').removeClass('km_dragdrop_place_active');
        jQueryKam('.km_szpaltanames_' + hfb).hide();
        jQueryKam('.km_dragdrop_' + hfb).removeClass('km_dragdrop_place_active');
    }
}

jQueryKam(document).ready(function ($)
{
    var oldMouseStart = $.ui.sortable.prototype._mouseStart;
    $.ui.sortable.prototype._mouseStart = function (event, overrideHandle, noActivation) {
        this._trigger("beforeStart", event, this._uiHash());
        oldMouseStart.apply(this, [event, overrideHandle, noActivation]);
    };

    $('.km_dragdrop_body').sortable({
        connectWith: '.km_dragdrop_body',
        handle: '.km_dragicon',
        cursor: 'move',
        cursorAt: { top: 0, left: 0 },
        placeholder: 'km_placeholder',
        forceHelperSize: true,
        forcePlaceholderSize: true,
        tolerance: 'pointer',
        revert: true,
        distance: 30,
        opacity: 0.4,
        beforeStart: function (event, ui) {
            km_levelname_display(true, "body");
        },
        stop: function (event, ui) {
            km_levelname_display(false, "body");
            var tm_drag = '';
            $(ui.item).parent().find('.km_dragbox').each(function (i) {
                tm_drag += $(this).attr('sid') + ';';
            });
            var level = $(ui.item).parent().attr("level");
            var sid = $(ui.item).attr("sid");
            km_module_drag(level, sid, tm_drag, km_infos["page"]);
        }
    });

    $('.km_dragdrop_head').sortable({
        connectWith: '.km_dragdrop_head',
        handle: '.km_dragicon',
        cursor: 'move',
        cursorAt: { top: 0, left: 0 },
        placeholder: 'km_placeholder',
        forceHelperSize: true,
        forcePlaceholderSize: true,
        tolerance: 'pointer',
        revert: true,
        distance: 30,
        opacity: 0.4,
        beforeStart: function (event, ui) {
            km_levelname_display(true, "head");
        },
        stop: function (event, ui) {
            km_levelname_display(false, "head");
            var tm_drag = '';

            jQueryKam(ui.item).parent().find('.km_dragbox').each(function (i) {
                tm_drag += jQueryKam(this).attr('sid') + ';';
            });
            var level = jQueryKam(ui.item).parent().attr("level");
            var sid = jQueryKam(ui.item).attr("sid");
            km_module_drag(level, sid, tm_drag, km_infos["page_header"]);
        }
    });

    $('.km_dragdrop_foot').sortable({
        connectWith: '.km_dragdrop_foot',
        handle: '.km_dragicon',
        cursor: 'move',
        cursorAt: { top: 0, left: 0 },
        placeholder: 'km_placeholder',
        forceHelperSize: true,
        forcePlaceholderSize: true,
        tolerance: 'pointer',
        revert: true,
        distance: 30,
        opacity: 0.4,
        beforeStart: function (event, ui) {
            km_levelname_display(true, "foot");
        },
        stop: function (event, ui) {
            km_levelname_display(false, "foot");
            var tm_drag = '';

            jQueryKam(ui.item).parent().find('.km_dragbox').each(function (i){
                tm_drag += jQueryKam(this).attr('sid') + ';';
            });
            var level = jQueryKam(ui.item).parent().attr("level");
            var sid = jQueryKam(ui.item).attr("sid");
            km_module_drag(level, sid, tm_drag, km_infos["page_footer"]);
        }
    });
});


