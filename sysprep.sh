#!/bin/sh
#
# This script installs required dependancies on a Ubuntu Machine
#
#set -xv

PACKAGES="apache2\
		 postgresql\
		 libapache2-mod-php5\
		 php5-pgsql\
		 php5-cli"

if ! [ `id -u` -eq 0 ] ; then
	echo "You must run this script with root permissions"
	exit 1;
fi

if ! [ -e /usr/bin/apt-get ] ; then 
	echo "This script only works on a ubuntu machine"
#	exit 1;
fi	

echo "Installing/updating required dependancies..."
apt-get install $PACKAGES
echo "Done"
