#!/bin/bash
# module generator - gen module file structure under HCS/application/modules/
# usage: mod_gen.sh worker
set -v
modpath=HCS/application/modules/
workpath=$modpath/$1
viewhtml="<p>this page is generated by mod_gen.sh</p>"

mkdir -p $workpath/controllers
touch $workpath/controllers/IndexController.php  
touch $workpath/controllers/ManageController.php
# TODO: insert basic control code

indexview=$workpath/views/scripts/index
mkdir -p $indexview
touch $indexview/index.phtml
echo $viewhtml > $indexview/index.phtml

manageview=$workpath/views/scripts/manage
mkdir -p $manageview
touch $manageview/index.phtml
echo $viewhtml > $manageview/index.phtml
