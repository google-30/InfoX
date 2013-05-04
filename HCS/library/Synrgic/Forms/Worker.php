<?php

define('UPLOAD_BASE', APPLICATION_PATH. '/data/uploads');
class Synrgic_Forms_Worker extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setName('workerinfo');
        $this->setAttrib('enctype', 'multipart/form-data');

        $path = UPLOAD_BASE;
        $file = new Zend_Form_Element_File('file');
        $file->setLabel('照片')->setDestination($path)->setRequired(true);

        $nameeng = new Zend_Form_Element_Text('nameeng');
        $nameeng->setLabel('姓名')->setRequired(true)->addValidator('NotEmpty');

        $namechs = new Zend_Form_Element_Text('namechs');
        $namechs->setLabel('中文姓名')->setRequired(true)->addValidator('NotEmpty');

        $fin = new Zend_Form_Element_Text('fin');
        $fin->setLabel('准证号码');
        $passexp = new Zend_Form_Element_Text('passexp');
        $passexp->setLabel('准证期限');

        $passport = new Zend_Form_Element_Text('passport');
        $passport->setLabel('护照号码');
        $passportexp = new Zend_Form_Element_Text('passportexp');
        $passportexp->setLabel('护照期限');

        $age = new Zend_Form_Element_Text('age');
        $age->setLabel('年龄');
        $birth = new Zend_Form_Element_Text('birth');
        $birth->setLabel('出生日期');

        $marital = new Zend_Form_Element_Text('marital');
        $marital->setLabel('婚姻状况');

        $address = new Zend_Form_Element_Text('address');
        $address->setLabel('在新地址');

        $hometown = new Zend_Form_Element_Text('hometown');
        $hometown->setLabel('籍贯');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Submit');

        $this->addElements(array($file, $nameeng,$namechs));
        $this->addElements(array($fin, $passexp));
        $this->addElements(array($passport, $passportexp));
        $this->addElements(array($age, $birth));
        $this->addElements(array($marital, $address, $hometown));
        $this->addElements(array($submit));
    }
}
