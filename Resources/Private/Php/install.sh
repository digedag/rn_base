#!/bin/bash

# Abbruch bei Fehler
set -e

SCRIPT_DIR="$(dirname "$(realpath "$0")")"
ROOT_DIR="$(realpath "$SCRIPT_DIR/../../..")"
EXT_COMPOSER="$ROOT_DIR/composer.json"
PHP_LIB_DIR="$ROOT_DIR/Resources/Private/Php"
VENDOR_DIR="$PHP_LIB_DIR/vendor"
CONTRIB_DIR="$PHP_LIB_DIR/Contrib"
PACKAGE_NAME="doctrine/collections"
PACKAGE_VERSION="2.2.2"
TARGET_AUTOLOAD_NAMESPACE="Doctrine\\Common\\Collections\\"
TARGET_AUTOLOAD_PATH="Resources/Private/Php/Contrib/doctrine/collections/src"

echo "➤ Installing to ${ROOT_DIR}..."

echo "➤ Installing ${PACKAGE_NAME} (${PACKAGE_VERSION})..."
cd "$PHP_LIB_DIR"
composer.phar require --no-scripts --no-interaction "${PACKAGE_NAME}:${PACKAGE_VERSION}"

echo "➤ Moving vendor directory to Contrib..."
rm -rf "$CONTRIB_DIR"
mv "$VENDOR_DIR" "$CONTRIB_DIR"

echo "➤ Updating composer.json autoload section..."

if jq -e --arg key "$TARGET_AUTOLOAD_NAMESPACE" '.autoload."psr-4"[$key]' "$EXT_COMPOSER" > /dev/null; then
    echo "✔ Namespace already present in composer.json"
else
    ESCAPED_NAMESPACE=$(echo "$TARGET_AUTOLOAD_NAMESPACE" | sed 's/\\/\\\\/g')
    tmpfile=$(mktemp)
    jq ".autoload.\"psr-4\" += {\"$ESCAPED_NAMESPACE\": \"$TARGET_AUTOLOAD_PATH\"}" "$EXT_COMPOSER" > "$tmpfile" && mv "$tmpfile" "$EXT_COMPOSER"
    echo "✔ Namespace added to composer.json"
fi

echo "➤ Done."
