#!/bin/sh
# $Id$
# Generates documentation using phpdocumentor
###############################################################################
# Sanity checks

PHPDOC=`which phpdoc`
cd `dirname $0`/..
PWD=`pwd`

if [ ! -x $PHPDOC ]; then
	echo "Can't find phpdoc in PATH."
	echo "   You need to add phpdocumentor/phpdoc to your PATH,"
	echo "   or create a symlink to it in an existing path in PATH."
	exit 1
fi
if [ "$1" == "-h" ]; then
	echo "Usage: $0 [-h] [output-path]"
	exit 123
fi

###############################################################################
# Configuration:

TITLE="AbstractBase&nbsp;API&nbsp;Documentation"

# name to use for the default package. If not specified, uses 'default'
DEFAULT_PACKAGE="ab.unknown"

PDF=no

# name of directory(s) to parse directory1,directory2
LIB_PATHS=$PWD

# name of file(s) to parse (file1,file2... Can contain complete path and * ? wildcards
INCLUDE_FILES=

# exclude
EXCLUDE=_arb/,old/,_test/

# where documentation will be put
PATH_DOCS=$PWD/docs/api
if [ "$1" != "" ]; then
	PATH_DOCS="$1"
fi

# parse elements marked as private (on/off)
PRIVATE=off


###############################################################################

if [ "$INCLUDE_FILES" != "" ]; then
	INCLUDE_FILES="-f '$INCLUDE_FILES'"
fi

$PHPDOC -q -d $LIB_PATHS \
	$INCLUDE_FILES \
	-t $PATH_DOCS \
	-ti "$TITLE" \
	-dn $DEFAULT_PACKAGE \
	-o HTML:frames:abstractbase \
	-pp $PRIVATE \
	-i $EXCLUDE
	#|grep -E '(WARNING|ERROR)'|grep -v 'DocBlock would be page-level, but precedes class'
