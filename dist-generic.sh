#!/bin/sh
#
# Perform a complete distribution.
#
#  - Update setup.py and src/module.h with current version (including revision)
#  - (Re)Build everything
#  - Create source distribution
#  - Create binary distribution
#  - Upload packages and update remote "latest"-links
#
# Arguments
#   1:  Path to python binary for which environment to build and
#       distribute for.
#

REMOTE_HOST='trac.hunch.se'
REMOTE_PATH='/var/lib/trac/ab/dist'
REMOTE_PATH_DOCS='/var/lib/trac/ab/docs'

if [ "$1" == "-h" ] || [ "$1" == "--help" ]; then
  echo "Package and distribute a new version." >&2
  echo "usage: $0" >&2
  exit 1
fi

cd $(dirname $0)
. dist.sh

CP=/bin/cp
if [ "$(uname -s)" == "Darwin" ]; then
  # don't include any extended attributes or resource forks
  CP="$CP -X"
fi

ensure_clean_working_revision


# Clean previous
DIST_PACKAGE_NAME="$PACKAGE-$VER"
DIST_DIR="dist/$DIST_PACKAGE_NAME"
DIST_PACKAGE_FILENAME="$DIST_PACKAGE_NAME.tgz"
DIST_PACKAGE_PATH="dist/$DIST_PACKAGE_FILENAME"
DIST_DIR_ROOT="$DIST_DIR/usr/share/abstractbase"
rm -rf "$DIST_DIR"
mkdir -vp "$DIST_DIR_ROOT"

# Package
for f in $(find -E lib -type f -regex '.+/(.*\.php|test)'); do
  DEST="$DIST_DIR_ROOT/$f"
  mkdir -pv $(dirname "$DEST")
  $CP -v "$f" "$DEST"
done
for f in LICENSE README; do
  $CP -v $f "$DIST_DIR_ROOT/"
done
rm -f "$DIST_PACKAGE_PATH"
echo "tar --directory \"$DIST_DIR\" -czvf \"$DIST_PACKAGE_PATH\" ."
tar --directory "$DIST_DIR" -czvf "$DIST_PACKAGE_PATH" .

# Generate documentation
echo 'Generating documentation...'
lib/makedoc.sh

# Upload & update links on server
echo "Uploading $DIST_PACKAGE_PATH to $REMOTE_HOST:$REMOTE_PATH"
scp -qC "$DIST_PACKAGE_PATH" $REMOTE_HOST:$REMOTE_PATH/
ssh $REMOTE_HOST "cd $REMOTE_PATH;\
if [ -f \"$DIST_PACKAGE_FILENAME\" ]; then\
  ln -sf \"$DIST_PACKAGE_FILENAME\" \"$PACKAGE-latest.tgz\";\
fi;"
echo "Uploading docs/api to $REMOTE_HOST:$REMOTE_PATH_DOCS"
scp -qCr docs/api $REMOTE_HOST:$REMOTE_PATH_DOCS/.api_upload
ssh $REMOTE_HOST "cd $REMOTE_PATH_DOCS; rm -rf api; mv -v .api_upload api"

# Note about tagging
echo 'Done!'
echo 'You might want to tag this version:'
REPROOT=$(svn info .|grep 'Repository Root:'|cut -d ' ' -f 3)
echo svn cp . $REPROOT/tags/$DIST_PACKAGE_NAME
