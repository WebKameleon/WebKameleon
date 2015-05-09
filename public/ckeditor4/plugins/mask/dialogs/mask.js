CKEDITOR.dialog.add("mask", function (editor) {
    // Function called in onShow to load selected element.
    var loadElements = function (editor, selection, element) {
        this.editMode = true;
        this.editObj = element;

        var attributeValue = this.editObj.getAttribute("name");
        if (attributeValue) {
            this.setValueOf("info", "txtName", attributeValue);
        } else {
            this.setValueOf("info", "txtName", "");
        }
    };

    var clipboard = [];

    if (typeof editor.config.km_clipboard == "undefined" || editor.config.km_clipboard == null || typeof editor.config.km_clipboard.mask == "undefined" || jQueryKam.isEmptyObject(editor.config.km_clipboard.mask)) {
        clipboard.push([tr('editor.mask.empty'), ""]);
    } else {
        clipboard.push([tr('editor.mask.choose'), ""]);
        jQueryKam.each(editor.config.km_clipboard.mask, function (k, v) {
            clipboard.push([v, k]);
        });
    }

    return {
        title: tr('editor.mask.title'),
        minWidth: 300,
        minHeight: 90,
        onOk: function () {
            // Always create a new anchor, because of IE BUG.
            var name = this.getValueOf("info", "txtName"),
                element = CKEDITOR.env.ie ?
                    editor.document.createElement("<mask>") :
                    editor.document.createElement("mask");

            // Move contents and attributes of old anchor to new anchor.
            if (this.editMode) {
                this.editObj.copyAttributes(element, { name: 1 });
                this.editObj.moveChildren(element);
            }

            // Set name.
            element.removeAttribute("_cke_saved_name");
            element.setAttribute("name", name);

            // Insert a new anchor.
            var fakeElement = editor.createFakeElement(element, "cke_mask", "mask");
            if (!this.editMode) {
                editor.insertElement(fakeElement);
            } else {
                fakeElement.replace(this.fakeObj);
                editor.getSelection().selectElement(fakeElement);
            }

            return true;
        },
        onShow: function () {
            this.editObj = false;
            this.fakeObj = false;
            this.editMode = false;

            var selection = editor.getSelection();
            var element = selection.getSelectedElement();
            if (element && element.getAttribute("_cke_real_element_type") && element.getAttribute("_cke_real_element_type") == "mask") {
                this.fakeObj = element;
                element = editor.restoreRealElement(this.fakeObj);
                loadElements.apply(this, [ editor, selection, element ]);
                selection.selectElement(this.fakeObj);
            }
            this.getContentElement("info", "txtName").focus();
        },
        contents: [
            {
                id: "info",
                label: tr('editor.mask.title'),
                accessKey: "I",
                elements: [
                    {
                        type: "text",
                        id: "txtName",
                        label: tr('editor.mask.name'),
                        validate: function () {
                            if (!this.getValue()) {
                                alert(tr('editor.mask.errorName'));
                                return false;
                            }
                            return true;
                        }
                    },
                    {
                        type: "select",
                        id: "clipboard",
                        label: editor.lang.paste,
                        items: clipboard,
                        style: "width : 100%;",
                        onChange: function () {
                            var sel = this.getDialog().getContentElement("info", "clipboard").getValue();
                            var tel = this.getDialog().getContentElement("info", "txtName");
                            tel.setValue(sel);
                        }

                    }
                ]
            }
        ]
    };
});
