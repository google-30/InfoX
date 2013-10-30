<?php

class infox_common
{
    public static function turnoffLayout($helper)
    {
        $helper->layout->disableLayout();
    }

    // usage: infox_common::turnoffView($this->_helper);
    public static function turnoffView($helper)
    {
        $helper->layout->disableLayout();   
        $helper->viewRenderer->setNoRender(TRUE);        
    }

    public static function getSerializeArrayValueByKey($array, $key)
    {
/*
array(1) {
  ["form"]=>
  array(16) {
    [0]=>
    array(2) {
      ["name"]=>
      string(9) "quickdate"
      ["value"]=>
      string(10) "10/12/2013"
    }
    [1]=>
    array(2) {
      ["name"]=>
      string(3) "sid"
      ["value"]=>
      string(1) "1"
    }
    [2]=>
    array(2) {
      ["name"]=>
      string(10) "attend3251"
      ["value"]=>
      string(1) "8"
    }
    ...
    }
  }
*/        
        $formarr = $array["form"];
        foreach($formarr as $tmp)
        {
            //print_r($tmp); echo "\n";
            $tmpkey = $tmp["name"]; 
            if($key == $tmpkey)
            {
                return $tmp["value"];
            }
        }
                    
        return "";
    }
    
    public static function setErrorOutput($errstr)
    {
        $error = '<span style="color:red;font-weight:bold">ERROR:' . $errstr . '</span><br>';
        return $error;
    }
    
    public static function getUsername()
    {  
        $username = "UserXXX";
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity()->username;
            //echo "username=$username<br>";                        
        }

        return $username;
    }
    
}

