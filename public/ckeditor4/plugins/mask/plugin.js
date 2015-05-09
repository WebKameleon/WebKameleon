CKEDITOR.plugins.add("mask", {
    requires: [ "fakeobjects" ],
    init: function (editor) {
        editor.addCommand("mask", new CKEDITOR.dialogCommand("mask"));
        editor.ui.addButton("mask", { label: tr('editor.mask.toolbar'), command: "mask", icon: this.path + "images/mask.gif" });
	
        CKEDITOR.dialog.add("mask", this.path + "dialogs/mask.js");

        CKEDITOR.addCss(
            "img.cke_mask {" +
                "background-image: url(" + CKEDITOR.getUrl(this.path + "images/icon.gif") + ");" +
                "background-position: center center;" +
                "background-repeat: no-repeat;" +
                "width: 22px;" +
                "height: 22px;" +
            "}\n"
        );


        if (editor.addMenuItems) {
            editor.addMenuItems({
                mask: {
                    label: tr('editor.mask.properties'),
                    command: "mask",
                    group: "div",
                    order: 1
                }
            });
        }

        if (editor.contextMenu) {
            editor.contextMenu.addListener(function (element, selection) {
                if (element && element.is("img") && element.getAttribute("_cke_real_element_type") == "mask") {
                    return {
                        mask: CKEDITOR.TRISTATE_OFF
                    };
                }
            });
        }
    },
    afterInit: function (editor) {
        var dataProcessor = editor.dataProcessor,
            dataFilter = dataProcessor && dataProcessor.dataFilter;

        if (dataFilter) {
            dataFilter.addRules({
                elements: {
                    mask: function (element) {
                        var attributes = element.attributes;
                        if (attributes.name) {
                            return editor.createFakeParserElement(element, "cke_mask", "mask");
                        }
                    },
                    img: function (element) {
                        var attributes = element.attributes;
                        if (attributes.include == 1) {
                            return editor.createFakeParserElement(element, "cke_mask", "mask");
                        }
                    }
                }
            });
        }
    }
});
