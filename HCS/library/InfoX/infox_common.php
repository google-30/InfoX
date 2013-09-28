<?php

class infox_common
{
    public static function turnoffLayout($helper)
    {
        $helper->layout->disableLayout();
    }

    // usage:         infox_common::turnoffView($this->_helper);
    public static function turnoffView($helper)
    {
        $helper->layout->disableLayout();   
        $helper->viewRenderer->setNoRender(TRUE);        
    }

}

