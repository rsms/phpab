#!/bin/sh
# $Id$

# path of PHPDoc executable
#PATH_PHPDOC=/Volumes/Stuff/wwwroot/web/phpdocumentor/phpdoc
PATH_PHPDOC=/www/phpdocumentor/phpdoc

# Do the cd-twistin
cd `dirname "$0"`
cd ..

# title of generated documentation, default is 'Generated Documentation'
TITLE="AbstractBase&nbsp;API&nbsp;Documentation"

# name to use for the default package. If not specified, uses 'default'
PACKAGES="ab"

# generate pdf? yes/no
PDF=no

# name of directory(s) to parse directory1,directory2
PATH_PROJECT=$PWD/lib

# name of file(s) to parse (file1,file2... Can contain complete path and * ? wildcards
#INCLUDE_FILES=$PWD/config.php

EXCLUDE=_arb/,old/
PATH_DOCS=$PWD/docs/api
OUTPUTFORMAT=HTML
CONVERTER=frames
TEMPLATE=rasmus

# parse elements marked as private (on/off)
PRIVATE=off

if [ $PDF = yes ]; then
        PDF_OP=",PDF:default:default"
fi

if [ "$INCLUDE_FILES" != "" ]; then
        INCLUDE_FILES="-f '$INCLUDE_FILES'"
fi

#rm -R "$PATH_DOCS/*"

# make documentation
$PATH_PHPDOC -d $PATH_PROJECT $INCLUDE_FILES -t $PATH_DOCS -ti "$TITLE" -dn $PACKAGES \
-o $OUTPUTFORMAT:$CONVERTER:$TEMPLATE$PDF_OP -pp $PRIVATE -i $EXCLUDE|grep -E '(WARNING|ERROR)'|grep -v 'DocBlock would be page-level, but precedes class'

