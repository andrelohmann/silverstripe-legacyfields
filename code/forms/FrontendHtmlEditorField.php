<?php

class FrontendHtmlEditorField extends HtmlEditorField {

    public function __construct($name, $title = null, $value = '') {
        parent::__construct($name, $title, $value);
        HtmlEditorConfig::require_js();
        Requirements::customScript("ssTinyMceConfig.mode = 'specific_textareas';ssTinyMceConfig.editor_selector='frontendhtmleditor';tinyMCE.init(ssTinyMceConfig);");
    }
}