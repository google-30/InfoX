<?php

class infox_user {

    //private static $_em = Zend_Registry::get('em');
    private $_ctl;

    private function __construct() {
        
    }

    /*
      private function __construct($controller)
      {
      $_ctl = $controller;
      }
     */

    public static function getUserName() {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity()->username;
            //echo "username=$username<br>";            
            return $username;
        } else {
            $ted = "Ted...";
            //echo "username=$ted<br>";            
            return $ted;
        }
    }

    public static function getUserRole() {
        $username = self::getUserName();
        $em = Zend_Registry::get('em');
        $userRepo = $em->getRepository('Synrgic\User');
        $userObj = $userRepo->findOneBy(array("username" => $username));
        $role = $userObj ? $userObj->getRole() : "";
        //echo "XXXXXXXXXX=" . $role;
        return $role;
    }

    public static function getUserSites() {
        $em = Zend_Registry::get('em');
        $humanresRepo = $em->getRepository('Synrgic\Infox\Humanresource');
        $siteRepo = $em->getRepository('Synrgic\Infox\Site');

        $username = self::getUserName();
        $user = $humanresRepo->findOneBy(array("username" => $username));

        $role = self::getUserRole();
        if ($role == "leader") {
            $id = $user->getId();

            $sites1 = $this->_site->findBy(array("permission1" => true));
            $sites = array();
            foreach ($sites1 as $tmp) {
                $leaders = $tmp->getLeaders();
                $array = explode(";", $leaders);
                if (in_array($id, $array)) {
                    $sites[] = $tmp;
                }
            }
        } else {
            $sites = $siteRepo->findAll();
        }

        //$this->view->sites = $sites;
        return $sites;
    }

    public static function getActiveSites() {
        $em = Zend_Registry::get('em');
        $humanresRepo = $em->getRepository('Synrgic\Infox\Humanresource');
        $siteRepo = $em->getRepository('Synrgic\Infox\Site');

        $username = self::getUserName();
        $user = $humanresRepo->findOneBy(array("username" => $username));

        $role = self::getUserRole();
        if ($role == "leader") {
            $id = $user->getId();

            $sites1 = $this->_site->findBy(array("permission1" => true));
            $sites = array();
            foreach ($sites1 as $tmp) {
                $leaders = $tmp->getLeaders();
                $array = explode(";", $leaders);
                if (in_array($id, $array)) {
                    $sites[] = $tmp;
                }
            }
        } else {
            $sites1 = $siteRepo->findBy(array("completed"=>False));
            $sites2 = $siteRepo->findBy(array("completed"=>NULL));
            $sites = array_merge($sites1, $sites2);
        }

        //$this->view->sites = $sites;
        return $sites;
    }

}
