#!/bin/bash

cp -r $1/app/code/community/Blackbird/ $2/app/code/community/Blackbird/
cp -r $1/app/etc/modules/* $2/app/etc/modules/
cp -r $1/app/design/frontend/base/default/layout/merlinsearch.xml $2/app/design/frontend/base/default/layout/merlinsearch.xml
cp -r $1/app/design/frontend/base/default/template/merlinsearch/ $2/app/design/frontend/base/default/template/merlinsearch/
cp -r $1/lib/Merlin/ $2/lib/Merlin/
