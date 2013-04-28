#!/bin/bash
set -v
#hcspath=/home/philip/Dev/hcsrepo/HCS/application/
hcspath=./HCS/application/
mkdir -p  $hcspath/data/cache
chmod a+w  $hcspath/data/cache

mkdir -p $hcspath/models/proxies
chmod a+w $hcspath/models/proxies

mkdir -p $hcspath/models/proxies
chmod a+w $hcspath/models/proxies

mkdir -p $hcspath/data/uploads/media
chmod a+w $hcspath/data/uploads/media
 
mkdir -p  $hcspath/data/uploads/adverts
chmod a+w $hcspath/data/uploads/adverts

mkdir -p $hcspath/data/uploads/services-pic
chmod a+w  $hcspath/data/uploads/services-pic

mkdir -p $hcspath/data/uploads
chmod a+w  $hcspath/data/uploads
