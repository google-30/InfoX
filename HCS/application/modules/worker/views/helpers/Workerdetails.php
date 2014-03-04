<?php

class GridHelper_Workerdetails extends Grid_Helper_Abstract {

    protected function td_name($field, $row) {
        $name = ($row["namechs"] != "") ? $row["namechs"] : $row["nameeng"];
        return $name;
    }

    protected function getDate($field, $row) {
        //return $row->$field ? $row->$field->format('Y/m/d') : "&nbsp;";
        return $row->$field ? $row->$field->format('d/m/Y') : "&nbsp;";
    }

    protected function td_resignation($field, $row) {
        return $this->getDate($field, $row);
    }

    protected function td_wpexpiry($field, $row) {
        return $this->getDate($field, $row);
    }

    protected function td_ppexpiry($field, $row) {
        return $this->getDate($field, $row);
    }

    protected function td_doa($field, $row) {
        return $this->getDate($field, $row);
    }

    protected function td_dob($field, $row) {
        return $this->getDate($field, $row);
    }

    protected function td_issuedate($field, $row) {
        return $this->getDate($field, $row);
    }

    protected function td_securityexp($field, $row) {
        return $this->getDate($field, $row);
    }

    protected function td_arrivaldate($field, $row) {
        return $this->getDate($field, $row);
    }

    protected function td_medicaldate($field, $row) {
        return $this->getDate($field, $row);
    }

    protected function td_csoc($field, $row) {
        return $this->getDate($field, $row);
    }

    protected function td_site($field, $row) {
        $wid = $row['id'];
        $em = Zend_Registry::get('em');
        $workerdetails = $em->getRepository('Synrgic\Infox\Workerdetails');
        $workeronsite = $em->getRepository('Synrgic\Infox\Workeronsite');
        $workerobj = $workerdetails->findOneBy(array("id" => $wid));
        $onsiterecords = $workeronsite->findBy(array("worker" => $workerobj));
        $sitenames = "";
        $sitenameArr = array();
        foreach ($onsiterecords as $tmp) {
            $siteobj = $tmp->getSite();

            $sitename = $siteobj->getName();
            //$enddate = $tmp->getEnddate();
            //$nowdate = new DateTime("now");
            //if ($enddate > $nowdate || !$enddate) 
            if (!$siteobj->getCompleted() && !in_array($sitename, $sitenameArr)){
                //$sitenames .= $sitename . "&nbsp;";
                $sitenameArr[] = $sitename;                
            }            
        }
        //return $sitenames;
        return implode("<br>", $sitenameArr);
    }

    protected function td_salary($field, $row) {        
        $url = "/salary/worker/personal/id/" . $row["id"];
        $html = '<a href="' . $url .  '" target="_blank">工资</a>';
        return $html;
    }
    
    protected function td_actions($field, $row) {    
        $link1 = "<a>编辑</a>";
        $wid = $row["id"];
        $name = $row["namechs"];
        $eeeno = $row["eeeno"];
        $link2 = '<a onclick="workerResign(' . "$wid, '$name','$eeeno'" .')">离职</a>';
        
        $actions = $link1 . " | " . $link2;
        return $actions;
    }
}

?>
