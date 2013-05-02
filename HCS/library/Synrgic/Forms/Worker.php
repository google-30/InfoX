<?php

define('UPLOAD_BASE', APPLICATION_PATH. '/data/uploads');
class Synrgic_Forms_Upload extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setName('upload');
        $this->setAttrib('enctype', 'multipart/form-data');

        $description = new Zend_Form_Element_Text('description');
        $description->setLabel('Description')
                  ->setRequired(true)
                  ->addValidator('NotEmpty');

        $path = UPLOAD_BASE;
        //$path1 = APPLICATION_PATH . '/../public/upload/workers';
        //echo "path=$path";
        $file = new Zend_Form_Element_File('file');
        $file->setLabel('File')
            ->setDestination($path)
            ->setRequired(true);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Upload');

        $this->addElements(array($description, $file, $submit));

    }
}
