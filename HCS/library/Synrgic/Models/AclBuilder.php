<?php

/* -
 * Copyright (c) 2012-2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Synrgic_Models_AclBuilder {

    public static function buildAcl(Zend_Acl $acl) {

        $acltable = array(
            'anonymous' => array(
                'parent' => null,
                'acl' => array(
                    'management:auth' => array('view', 'login', 'logout'),
                /*
                  'default:index'=>array('view'),
                  'adverts:index'=>array('view'),
                  'information:index'=>array('view'),
                  'noticeboard:index'=>array('view','data'),
                  'phone:index'=>array('view'),
                  'room:index'=>array('view','roomlights', 'roomcurtains','roomtv','roomradio','roompreset','roomtemp','data','setstate'),
                  'welcome:index'=>array('view','set','start'),
                  'adverts:index'=>array('updschedule'), //allow all see adverts if possible

                  // Non module/controller based
                  'menu:hotel'=>array('view'),

                  // infoX
                  'material:index'=>array('view'),
                 */
                ),
            ),
            'manager' => array(
                'parent' => null,
                'acl' => array(
                    'management:dashboard' => array('view'),
                    'management:index' => array('view'),
                    'management:auth' => array('view', 'login', 'logout'),
                    'worker:manage' => array('view'),
                    'project:manage' => array('view'),
                    //'material:manage'=>array('view','add','edit','delete','savedetail'),   
                    'material:manage' => array('view', 'add', 'edit', 'delete', 'submit', 'appmanage', 'appedit', 'appdel', 'matdel',
                        'updatedata', 'submitmatapps', 'reviewmatapps', 'rejectmatapps', 'approvematapps'),
                )
            ),
            'leader' => array(
                'parent' => null,
                'acl' => array(
                    'management:dashboard' => array('view'),
                    'management:index' => array('view'),
                    'management:auth' => array('view', 'login', 'logout'),
                    'material:apply' => array('view', 'postdata', 'applymaterials', 'getselections', 'delselection', 'submitselections'),
                )
            ),
            'staff' => array(
                'parent' => null,
                'acl' => array(
                    'default:index' => array('view'),
                    'management:dashboard' => array('view'),
                    'management:index' => array('view'),
                    'management:auth' => array('view', 'login', 'logout'),
                    'worker:manage' => array('view', 'add', 'edit', 'delete', 'submit',
                        'output', 'workerexpire', 'previewlist', 'previewexpiry',
                        'resign', 'renew', 'deleterenew', 'renewlist'),
                    'worker:onsite' => array('view', 'onsiterecord', 'addrecord', 'updaterecord', 
                        'deleterecord', 'attendancerecord', 'addattendancerecord', 'updateattendancerecord', 
                        'deleteattendancerecord', 'workersonsite', 'sitelist', 'addrecordquick'),
                    'worker:custominfo' => array('view', 'postinfo'),
                    'worker:import' => array('view', 'submit', 'truncateworkerdetails'),
                    'worker:archive' => array('view', 'edit', 'previewlist', "activeworker"),
                    
                    'salary:salary' => array('view', 'personal', 'salarybymonth', 'gensalaryrecords',
                        'datainput', 'datapost', 'salarysheet', 'salaryreceipts',
                        'salaryreceiptsbyworker', 'summary', 'summarybysite'),
                    'salary:worker' => array('view', 'personal', ''),
                    'salary:history' => array('view', 'site', 'company'),
                    'salary:settings' => array('view', 'submit'),
                    'material:manage' => array('view', 'add', 'edit', 'delete', 'submit',
                        'importmaterials', 'previewlist', 'supplyprice', 'postsupplyprice'),
                    'material:apply' => array('view', 'postdata', 'applymaterials', 'getselections', 'delselection', 'submitselections', 'applist', 'appedit', 'appmatdel'),
                    'material:appmanage' => array('view', 'appedit', 'appdel', 'appmatdel',
                        'appdetail', 'updatematapp', 'submitmatapps', 'reviewmatapps', 'rejectmatapps',
                        'previewform', 'previeworder', 'polist', 'appapprove', 'appgiveup', 'apppending'),
                    'material:emachinery' => array('view', 'add', 'edit', 'delete', 'submit', 'onsiterecord', 'addrecord', 'updaterecord', 'deleterecord',),
                    'material:import' => array('view', 'submit', 'truncateall'),
                    'material:summary' => array('view', 'submit', 'truncateall'),
                    'material:po' => array('view', 'submit', 'podetails', 'posettings'),
                    'material:type' => array('view', 'add', 'edit', 'delete', 'submit', 'posttype'),
                    'project:manage' => array('view', 'add', 'edit', 'delete', 'submit',
                        'sitedetail', 'addpart', 'delpart', 'workerlist',
                        'emachinery', 'applist', 'allmaterials', 'siteinfo', 'sitestatus'),
                    'project:attendance' => array('view', 'attendancepage', 'attendialog',
                        'postattend', 'attendsheet', 'attendquick', 'quicksubmit', 'savedailyinput', 'postattendmonth'),
                    'archive:manage' => array('view', 'add', 'edit', 'delete', 'submit'),
                    'supplier:manage' => array('view', 'add', 'edit', 'delete', 'submit', 'truncate'),
                    'humanresource:manage' => array('view', 'add', 'edit', 'delete', 'submit'),
                    'material:type' => array('view', 'add', 'edit', 'delete', 'submit', 'posttype'),
                    'company:info' => array('view', 'add', 'edit', 'delete', 'submit'),
                    'miscinfo:manage' => array('view', 'addinfo', 'postinfo', 'edit', 'delete', 'submit'),
                    'infoxsys:manage' => array('view', 'edit', 'submit'),
                )
            ),
            'admin' => array(
                'parent' => null,
                'acl' => array(
                    'default:index' => array('view'),
                    'management:dashboard' => array('view'),
                    'management:index' => array('view'),
                    'management:auth' => array('view', 'login', 'logout'),
                    'worker:manage' => array('view', 'add', 'edit', 'delete', 'submit',
                        'output', 'workerexpire', 'previewlist', 'previewexpiry', 'resign'),
                    'worker:onsite' => array('view', 'onsiterecord', 'addrecord', 'updaterecord', 
                        'deleterecord', 'attendancerecord', 'addattendancerecord', 'updateattendancerecord', 
                        'deleteattendancerecord', 'workersonsite', 'sitelist'),

                    'worker:custominfo' => array('view', 'postinfo'),
                    'worker:import' => array('view', 'submit', 'truncateworkerdetails'),
                    'worker:archive' => array('view', 'edit', 'previewlist'),
                    'salary:salary' => array('view', 'personal', 'salarybymonth', 'gensalaryrecords',
                        'datainput', 'datapost', 'salarysheet', 'salaryreceipts',
                        'summary', 'summarybysite'),
                    'salary:worker' => array('view', 'personal', ''),
                    'salary:history' => array('view', ''),
                    'salary:settings' => array('view', 'submit'),
                    'material:manage' => array('view', 'add', 'edit', 'delete', 'submit',
                        'importmaterials', 'previewlist', 'supplyprice', 'postsupplyprice'),
                    'material:apply' => array('view', 'postdata', 'applymaterials',
                        'getselections', 'delselection', 'submitselections', 'applist', 'appedit', 'appmatdel'),
                    'material:appmanage' => array('view', 'appedit', 'appdel', 'appmatdel',
                        'appdetail', 'updatematapp', 'submitmatapps', 'reviewmatapps', 'rejectmatapps',
                        'previewform', 'previeworder', 'polist', 'appapprove', 'appgiveup', 'apppending'),
                    'material:emachinery' => array('view', 'add', 'edit', 'delete', 'submit', 'onsiterecord', 'addrecord', 'updaterecord', 'deleterecord',),
                    'material:import' => array('view', 'submit', 'truncateall'),
                    'material:summary' => array('view', 'submit', 'truncateall'),
                    'material:po' => array('view', 'submit', 'podetails', 'posettings'),
                    'project:manage' => array('view', 'add', 'edit', 'delete', 'submit',
                        'sitedetail', 'addpart', 'delpart', 'workerlist', 'emachinery',
                        'applist', 'allmaterials', 'siteinfo', 'sitestatus'),
                    'project:attendance' => array('view', 'attendancepage', 'attendialog',
                        'postattend', 'attendsheet', 'attendquick', 'quicksubmit', 'savedailyinput', 'postattendmonth'),
                    'archive:manage' => array('view', 'add', 'edit', 'delete', 'submit'),
                    'supplier:manage' => array('view', 'add', 'edit', 'delete', 'submit', 'truncate'),
                    'humanresource:manage' => array('view', 'add', 'edit', 'delete', 'submit'),
                    'material:type' => array('view', 'add', 'edit', 'delete', 'submit', 'posttype'),
                    'company:info' => array('view', 'add', 'edit', 'delete', 'submit'),
                    'miscinfo:manage' => array('view', 'addinfo', 'postinfo', 'edit', 'delete', 'submit'),
                    'infoxsys:manage' => array('view', 'edit', 'submit'),
                )
            ),
            'hr&payroll' => array(
                'parent' => null,
                'acl' => array(
                    'default:index' => array('view'),
                    'management:dashboard' => array('view'),
                    'management:index' => array('view'),
                    'management:auth' => array('view', 'login', 'logout'),
                    'worker:manage' => array('view', 'add', 'edit', 'delete', 'submit',
                        'output', 'workerexpire', 'previewlist', 'previewexpiry',
                        'resign', 'renew', 'deleterenew', 'renewlist', 'loadrenewlist', 'loadrenew'),
                    'worker:onsite' => array('view', 'onsiterecord', 'addrecord', 'updaterecord', 
                        'deleterecord', 'attendancerecord', 'addattendancerecord', 'updateattendancerecord', 
                        'deleteattendancerecord', 'workersonsite', 'sitelist', 'addrecordquick'),
                    'worker:custominfo' => array('view', 'postinfo'),
                    'worker:import' => array('view', 'submit', 'truncateworkerdetails'),
                    'worker:archive' => array('view', 'edit', 'previewlist', "activeworker"),
                    'salary:salary' => array('view', 'personal', 'salarybymonth', 'gensalaryrecords',
                        'datainput', 'datapost', 'salarysheet', 'salaryreceipts',
                        'salaryreceiptsbyworker', 'summary', 'summarybysite'),
                    'salary:worker' => array('view', 'personal', ''),
                    'salary:history' => array('view', 'site', 'company'),
                    'salary:settings' => array('view', 'submit'),
                    'project:manage' => array('view', 'add', 'edit', 'delete', 'submit',
                        'sitedetail', 'addpart', 'delpart', 'workerlist', 'emachinery', 'applist', 'allmaterials', 'siteinfo'),
                    'project:attendance' => array('view', 'attendancepage', 'attendialog',
                        'postattend', 'attendsheet', 'attendquick', 'quicksubmit', 'savedailyinput', 'postattendmonth'),
                    'archive:manage' => array('view', 'add', 'edit', 'delete', 'submit'),
                    'company:info' => array('view', 'add', 'edit', 'delete', 'submit'),
                    'miscinfo:manage' => array('view', 'addinfo', 'postinfo', 'edit', 'delete', 'submit'),
                    'infoxsys:manage' => array('view', 'edit', 'submit'),
                )
            ),
            'material' => array(
                'parent' => null,
                'acl' => array(
                    'default:index' => array('view'),
                    'management:dashboard' => array('view'),
                    'management:index' => array('view'),
                    'management:auth' => array('view', 'login', 'logout'),
                    'material:manage' => array('view', 'add', 'edit', 'delete', 'submit',
                        'importmaterials', 'previewlist', 'supplyprice', 'postsupplyprice'),
                    'material:apply' => array('view', 'postdata', 'applymaterials', 'getselections', 'delselection', 'submitselections', 'applist', 'appedit', 'appmatdel'),
                    'material:appmanage' => array('view', 'appedit', 'appdel', 'appmatdel',
                        'appdetail', 'updatematapp', 'submitmatapps', 'reviewmatapps', 'rejectmatapps',
                        'previewform', 'previeworder', 'polist', 'appapprove', 'appgiveup', 'apppending'),
                    'material:emachinery' => array('view', 'add', 'edit', 'delete', 'submit', 'onsiterecord', 'addrecord', 'updaterecord', 'deleterecord',),
                    'material:import' => array('view', 'submit', 'truncateall'),
                    'material:summary' => array('view', 'submit', 'truncateall'),
                    'material:po' => array('view', 'submit', 'podetails', 'posettings'),
                    'material:type' => array('view', 'add', 'edit', 'delete', 'submit', 'posttype'),
                    'project:manage' => array('view', 'add', 'edit', 'delete', 'submit',
                        'sitedetail', 'addpart', 'delpart', 'workerlist', 'emachinery', 'applist', 'allmaterials', 'siteinfo'),
                    'archive:manage' => array('view', 'add', 'edit', 'delete', 'submit'),
                    'supplier:manage' => array('view', 'add', 'edit', 'delete', 'submit', 'truncate'),
                    'company:info' => array('view', 'add', 'edit', 'delete', 'submit'),
                    'miscinfo:manage' => array('view', 'addinfo', 'postinfo', 'edit', 'delete', 'submit'),
                    'infoxsys:manage' => array('view', 'edit', 'submit'),
                )
            ),
        );

        $acl->deny(); // Default to deny for all resources

        /** 	
         * First iteration through the table 
         */
        foreach ($acltable as $role => $data) {
            $parent = $data['parent'];
            if (!$acl->hasRole($role)) {
                if ($parent == null) {
                    $acl->addRole($role);
                } else {
                    $acl->addRole($role, $acl->getRole($parent));
                }
            }
            $resources = $data['acl'];

            foreach ($resources as $res => $privileges) {
                if (!$acl->has($res)) {
                    $acl->addResource($res);
                }
                if (!empty($privileges)) {
                    self::_allowAcl($acl, $role, $res, $privileges);
                }
            }
        }
    }

    public static function mapResource($module, $controller, $action) {
        if ($controller == null)
            $controller = 'index';

        if ($action == 'index')
            $action = 'view';

        return $module . ":" . $controller;
    }

    private static function _allowAcl(Zend_Acl $acl, $role, $res, $privileges) {
        if (!$privileges && is_string($privileges)) {
            $privileges = str_split($privileges, ',');
        }
        if ($res === '-') {
            $res = null;
        }
        $acl->allow($role, $res, $privileges);
    }

}
