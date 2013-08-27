<?php

class GridHelper_Workeronsite extends Grid_Helper_Abstract 
{
    protected function td_begindate($field, $row) 
    {
        $id = $row["id"];    
        $value = $row[$field] ? $row[$field]->format("Y/m/d") : "";
        $input = '<input type="text" id="begindate'. $id . '" class="datepicker" value="' . $value . '" data-mini="true">';
        return $input;
    }    

    protected function td_enddate($field, $row) 
    {
        $id = $row["id"];    
        $value = $row[$field] ? $row[$field]->format("Y/m/d") : "";
        $input = '<input type="text" id="enddate'. $id .'" class="datepicker" value="' . $value . '" data-mini="true">';
        return $input;
    }    

    protected function td_site($field, $row) 
    {
        $id = $row["id"];
        $curSiteobj = $row[$field];
        $curSiteid = $curSiteobj->getId();

        $em = Zend_Registry::get('em');
        $sites = $em->getRepository('Synrgic\Infox\Site')->findAll();

        $select = '<select id="site' . $id . '" data-mini="true">';
        $options = '<option value="0">选择工地</option>';    
        foreach($sites as $tmp)
        {
            $siteid = $tmp->getId();
            $name = $tmp->getName();

            if($siteid == $curSiteid)
            {
                $option = '<option value="' . $siteid . '" selected>' . $name . '</option>';
            }
            else
            {
                $option = '<option value="' . $siteid . '" >' . $name . '</option>';
            }
            $options .= $option;
        }
 
        $select .= $options . "</select>";

        return $select;    
    }

    protected function td_days($field, $row) 
    {
        $id = $row["id"];    
        $value = $row[$field] ? $row[$field] : "";
        $input = '<input type="text" id="days'. $id . '" value="' . $value . '">';
        return $input;
    }    

    protected function td_reason($field, $row) 
    {
        $id = $row["id"];    
        $curReason = $row[$field] ? $row[$field] : "";

        $infolab = "info03";
        $em = Zend_Registry::get('em');
        $values = $em->getRepository('Synrgic\Infox\Miscinfo')->findOneBy(array("label"=>$infolab))->getValues();
        $valueArr = explode(";", $values);

        $select = '<select id="reason' . $id . '" data-mini="true">';
        $options = '<option value="0">选择原因</option>';    
        foreach($valueArr as $tmp)
        {
            if($tmp=="")
            {
                continue;
            }

            if($tmp == $curReason)
            {
                $option = '<option value="' . $tmp . '" selected>' . $tmp . '</option>';
            }
            else
            {
                $option = '<option value="' . $tmp . '" >' . $tmp . '</option>';
            }
            $options .= $option;
        }
 
        $select .= $options . "</select>";

        return $select;    
    }  

    protected function td_remark($field, $row) 
    {
        $id = $row["id"];    
        $value = $row[$field] ? $row[$field] : "";
        $input = '<input type="text" id="remark'. $id . '" value="' . $value . '">';
        return $input;
    }  

    protected function td_update($field, $row) 
    {
    	return '<button onclick="updaterecord(' . $row['id'] . ')" data-inline="true" data-mini="true">更新</button>';
    }    

    protected function td_delete($field, $row) 
    {
    	return '<button onclick="deleterecord(' . $row['id'] . ')" data-inline="true" data-mini="true">删除</button>';
    }    

    protected function td_begindate1($field, $row) 
    {
        $col = 'begindate';
        return $row[$col] ? $row[$col]->format("Y/m/d") : "&nbsp;";
    }    

    protected function td_enddate1($field, $row) 
    {
        $col = 'enddate';
        return $row[$col] ? $row[$col]->format("Y/m/d") : "&nbsp;";
    }    

    protected function td_site1($field, $row) 
    {
        $col = "site";
        $site = $row[$col];
        $sitename = $site ? $site->getName() : "&nbsp;";

        return $sitename;        
    }

    protected function td_days1($field, $row) 
    {
        $col = 'days';
        return $row[$col] ? $row[$col] : "&nbsp;";
    }      

    protected function td_reason1($field, $row) 
    {
        $col = 'reason';
        return $row[$col] ? $row[$col] : "&nbsp;";
    } 

    protected function td_remark1($field, $row) 
    {
        $col = 'remark';
        return $row[$col] ? $row[$col] : "&nbsp;";
    } 

    protected function td_workername($field, $row) 
    {
        $workerobj = $row['worker'];
        $longname = $workerobj->getNamechs() . "/" . $workerobj->getNameeng();

        return $longname;
    } 

    protected function td_worktype($field, $row) 
    {
        $worker = $row['worker'];
        $workerskill = $worker->getWorkerskill();
        $worktype = $workerskill->getWorktype();

        return $worktype;
    } 


}

?>
