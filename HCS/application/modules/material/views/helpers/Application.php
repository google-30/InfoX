<?php

class GridHelper_Application extends Grid_Helper_Abstract 
{
    protected function td_applicant($field, $row) 
    {
        $applicant = "&nbsp;";
        if($row[$field])
    	{
            $applicant = $row[$field]->getName();
        }
        return $applicant;
    }

    protected function td_site($field, $row) 
    {
        $content = "&nbsp;";
        if($row[$field])
    	{
            $content = $row[$field]->getName();
        }
        return $content;
    }

    protected function td_details($field, $row) 
    {
        $appid = $row["id"];
        $detaillink = "/material/appmanage/appdetail/id/" . $appid;
        $html = '<a href="' . $detaillink .'" target="_blank"><button data-mini="true">详细</button></a>';
        return $html;
    }    

    protected function td_materials($field, $row) 
    {
        //return "xxx";
        $em = Zend_Registry::get('em');
        $appRepo = $em->getRepository('Synrgic\Infox\Application');        
        $matappdataRepo = $em->getRepository('Synrgic\Infox\Matappdata');        
        $appid = $row['id'];
        $appobj = $appRepo->findOneBy(array("id"=>$appid));
        $matappobjs = $matappdataRepo->findBy(array("application"=>$appobj));
        $matappstr = "";
        foreach($matappobjs as $mat)
        {
            $longname = $mat->getLongname() . "&nbsp;--&nbsp;";
            $matappstr .= $longname;
        }
        
        return substr($matappstr, 0, 50);
    }
}

?>
