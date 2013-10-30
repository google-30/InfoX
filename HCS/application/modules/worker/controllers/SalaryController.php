<?php
include 'InfoX/infox_common.php';
include 'InfoX/infox_project.php';
include 'InfoX/infox_user.php';
include 'InfoX/infox_worker.php';
include 'InfoX/infox_salary.php';

class Worker_SalaryController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workerdetails = $this->_em->getRepository('Synrgic\Infox\Workerdetails');
    }

    public function indexAction()
    {
        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();
        $this->view->sheet = $requestsheet = $this->getParam("sheet",$sheetarr[0]);
        $this->view->workerarr = infox_worker::getworkerlistbysheet($requestsheet);
    }

    public function personalsalaryAction()
    {

    }

    public function salarybymonthAction()
    {
        infox_common::turnoffLayout($this->_helper);
        
        $error="";

        $sheet = $this->getParam("sheet", "HC.C");
        $this->view->sheet = $sheet;
        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();

        // TODO: may cause datetime issue here
        $monthstr = $this->getParam("month", "now");
        if($monthstr=="now")
        {            
            $error .= "salarybymonthAction:monthstr error.";
            $this->view->error = $error;
            return;
        }
        $month = new Datetime($monthstr);        
        $this->view->monthstr = $monthstr;

        // create records first 
        //infox_worker::createSalaryRecordsByMonthSheet($monthstr, $sheet);

        // get all records in this month
        $salaryrecords = infox_salary::getSalaryRecordsByMonthSheet($month, $sheet);

        $attendarr = infox_project::getAttendanceByMonthSheet($monthstr, $sheet);
        //$this->view->attendarr = $attendarr;

        $salarytabs = $this->generateSalaryTabs($salaryrecords, $attendarr);
    
        $this->view->salarytabs = $salarytabs;
    }
   
    private function generateSalaryTabs($salaryrecords, $attendarr)
    {
        $salarytabs = array();
        $sno = 0;
        foreach($salaryrecords as $record)
        {
            $tmparr = array();

            $workertab = "";
            $worker = $record->getWorker();
            
            $sno++;
            $tab = $this->generateWorkerTab($worker, $sno);
            $tmparr[] = $tab;

            $tab = $this->generatePaymentTab($record);
            $tmparr[] = $tab;

            $attendrecord = null;
            foreach($attendarr as $attend)
            {
                $attendworker = $attend->getWorker();

                if($attendworker->getId() == $worker->getId())
                {
                    $attendrecord = $attend;
                    break;
                }
            }
            //$tab = $this->generateAttendanceTab($attendrecord);
            $tab = infox_project::generateAttendanceTab($attendrecord, false);

            $tmparr[] = $tab;

            $salarytabs[] = $tmparr;   
        }

        return $salarytabs;
    } 

    private function generateWorkerTab($worker, $sno)
    {
        $name = $worker->getNamechs();
        if(!$name || $name == "")
        {
            $name = $worker->getNameeng();
        }

        $wpno = $worker->getWpno();
        $eeeno = $worker->getEeeno();
        $price = $worker->getPrice();
        $type = $worker->getWorktype();

        $tab = '<table class="workerinfo">';
        $tab .= "<tr><th rowspan=1>序号</th><th>准证号</th><th>编号</th><th>姓名</th><th>单价</th><th>工种</th></tr>";
        $tab .= "<tr><td>$sno</td><td>$wpno</td><td>$eeeno</td><td>$name</td><td>$price</td><td>$type</td></tr>";
        $tab .= "</table>";
        
        return $tab;
    }   

    private function generatePaymentTab($record)
    {
        $normalhours = $record->getNormalhours();
        $normalpay = $record->getNormalpay();

        $othours = $record->getOthours();
        $otpay = $record->getOtpay();
        $otprice = $record->getOtprice();

        $allhours = $record->getAllhours();
        $allpay = $record->getAllpay();

        $attenddays = "xx";
        $absencedays = "xx";
        $absencefines = "xx";
        $projectpay = "xx";
        $fooddays = "fd";
        $foodpay = "fp";

        $rtmonthpay = $record->getRtmonthpay();
        $rtmonths = $record->getRtmonths();
        $rtall = $record->getRtall();
        $utfee = $record->getUtfee();
        $utallowance = $record->getUtallowance();
        $otherfee = $record->getOtherfee();
        $inadvance = $record->getInadvance();
        $salary = $record->getSalary();
        $netpay = $record->getNetpay();

        $tab = "<table>";
        $tab .= "<tr><td colspan=2>正常工作</td><td colspan=3>加班工作</td><td colspan=2>总工作</td>"
                . "<td rowspan=2>考勤天数</td><td colspan=2>缺勤罚款</td><td rowspan=2>项目总工资</td>";
        $tab .= "<td colspan=2>伙食费</td><td colspan=3>预扣税</td><td colspan=2>水电费</td>"
                . "<td rowspan=2>其他补扣</td><td rowspan=2>提前结帐</td><td rowspan=2>当月净工资</td>"
                . '<td rowspan=3><button data-mini="true" data-theme="b">输入</button></td></tr>';
        $tab .= "<tr><td>小时</td><td>金额</td><td>单价</td><td>小时</td><td>金额</td><td>小时</td>"
                . "<td>金额</td><td>天数</td><td>金额</td>";
        $tab .= "<td>天数</td><td>金额</td>";
        $tab .= "<td>当月</td><td>月数</td><td>累计</td>";        
        $tab .= "<td>扣款</td><td>补助</td>";
        $tab .= "</tr>";

        // insert value
        $tab .= "<tr><td>$normalhours</td><td>$normalpay</td>";
        $tab .= "<td>$otprice</td><td>$othours</td><td>$otpay</td>";
        $tab .= "<td>$allhours</td><td>$allpay</td><td>$attenddays</td>";
        $tab .= "<td>$absencedays</td><td>$absencefines</td>";
        $tab .= "<td>$projectpay</td>";
        $tab .= "<td>$fooddays</td><td>$foodpay</td>";
        $tab .= "<td>$rtmonthpay</td><td>$rtmonths</td><td>$rtall</td>";        
        $tab .= "<td>$utfee</td><td>$utallowance</td>";
        $tab .= "<td>$otherfee</td><td>$inadvance</td><td>$salary</td>";
        $tab .= "</tr>";

        $tab .= "</table>";                
        return $tab;
    }

    public function gensalaryrecordsAction()
    {
        infox_common::turnoffView($this->_helper);        
        $sheet = $this->getParam("sheet", "HC.C");
        $month = $this->getParam("month", "");
        $date = new Datetime($month . "01");        
        $monthstr = $date->format("Y-m");
        
        infox_worker::createSalaryRecordsByMonthSheet($monthstr, $sheet);


    }
}
