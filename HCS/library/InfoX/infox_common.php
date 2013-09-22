<?php

class infox_common
{
    public static function turnoffLayout($helper)
    {
        $helper->layout->disableLayout();
    }

    public static function turnoffView($helper)
    {
        $helper->layout->disableLayout();   
        $helper->viewRenderer->setNoRender(TRUE);        
    }

}

