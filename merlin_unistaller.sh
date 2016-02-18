#!/bin/bash

#Enter Folder of Magento Installation For Instance /var/www/html/magento
rm -r $1/app/code/community/Blackbird/ 
rm  $1/app/etc/modules/Blackbird_Merlinsearch.xml
rm $1/app/design/frontend/base/default/layout/merlinsearch.xml
rm -r $1/app/design/frontend/base/default/template/merlinsearch/
rm -r $1/lib/Merlin/
