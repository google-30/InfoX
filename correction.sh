#!/bin/bash
set -v
#hcspath=/home/philip/Dev/hcsrepo/HCS/application/
hcspath=./HCS/application/
mkdir -p  $hcspath/data/cache
chmod a+w  $hcspath/data/cache

mkdir -p $hcspath/models/proxies
chmod a+w $hcspath/models/proxies

mkdir -p $hcspath/data/uploads
chmod a+w  $hcspath/data/uploads

mkdir -p $hcspath/data/uploads/workers
chmod a+w  $hcspath/data/uploads/workers

mkdir -p $hcspath/data/uploads/workers/images
chmod a+w  $hcspath/data/uploads/workers/images

mkdir -p $hcspath/data/uploads/archives/softwares
mkdir -p $hcspath/data/uploads/archives/documents
chmod -R 777 $hcspath/data/uploads/archives

# material
mkdir -p $hcspath/data/uploads/materials/
chmod a+w  $hcspath/data/uploads/materials/

