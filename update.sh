#!/bin/bash

hcspath=./
doctrine=$hcspath/HCS/bin/doctrine
cd $hcspath
#git pull -q origin master
#git submodule update  --init --recursive

source ./bootstrap
$doctrine orm:schema-tool:drop --force
$doctrine orm:schema-tool:create
loadinfox.php

