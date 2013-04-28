<?
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Synrgic_Models_Form_Decorator_Page extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $element = $this->getElement();
        if(!$element instanceof Synrgic_Models_Form_Element_Page){
            return $content;
        }
        
        $view = $element->getView();
        if( !$view instanceof Zend_View_Interface){
            return $content;
        }

        $name = $element->getFullyQualifiedName();
        $pageHelper = $element->getPageHelper();
        $page = $pageHelper->getPage();


        $markup=<<<EOF
            <script type="text/javascript">
            // TODO - re-check these options which are from tinyMCE full example
            tinyMCE.init({
                         // General options
mode : "exact",
elements: "
EOF;
$markup.=$name.'-editor';
$markup.=<<<EOF
",
theme : "advanced",
plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave,visualblocks",

setup: function(ed){
ed.onChange.add(function(ed,content){
                tinyMCE.triggerSave();
$('#
EOF;
$markup.=$name.'-content';
$markup.=<<<EOF
').val(tinyMCE.activeEditor.getContent());
});},



/*
// Theme options
theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft,visualblocks",
theme_advanced_toolbar_location : "top",
theme_advanced_toolbar_align : "left",
theme_advanced_statusbar_location : "bottom",
theme_advanced_resizing : true,

// Example content CSS (should be your site CSS)
content_css : "css/content.css",

                // Drop lists for link/image/media/template dialogs
                template_external_list_url : "lists/template_list.js",
                external_link_list_url : "lists/link_list.js",
                external_image_list_url : "lists/image_list.js",
                media_external_list_url : "lists/media_list.js",

                // Style formats
                style_formats : [
                {title : 'Bold text', inline : 'b'},
                {title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
                {title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
                {title : 'Example 1', inline : 'span', classes : 'example1'},
                {title : 'Example 2', inline : 'span', classes : 'example2'},
                {title : 'Table styles'},
                {title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
                ],

                // Replace values for the template plugin
                template_replace_values : {
                username : "Some User",
                staffid : "991234"
                }
 */

            });
        </script>
EOF;

        $states = array(  \Synrgic\CMS\Page::DRAFT => 'Draft', 
                          \Synrgic\CMS\Page::PUBLISHED => 'Published');

        $markup.= 'State:' . $view->formSelect($name .  '[state]',$page->getState(),null,$states);

        $markup.= 'Title:' . $view->formText($name . '[title]', $page->getTitle());

        $markup.= $view->formTextarea( $name .  '[editor]', $page->getContent());
        $markup.= $view->formHidden( $name .  '[content]', $page->getContent());

        switch( $this->getPlacement()){
            case self::PREPEND:
                return $markup  . $this->getSeparator() . $content;
            case self::APPEND:
            default:
                return $content . $this->getSeparator(). $markup;
        }

    }
}
