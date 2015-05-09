#!/bin/sh
dir=`pwd`
cd `dirname $0`/..
mkdir tmp
cp -rp application library public scripts tmp
cd tmp
find -name ".svn"|xargs rm -rf
rm application/configs/local.ini
mkdir -p files media/templates media/ufiles media/uimages media/uincludes
tar czf ../kameleon.tgz *
cd ..
rm -rf tmp