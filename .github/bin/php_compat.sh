#!/bin/bash

if [ "$#" != 1 ]; then
  echo "missing PHP version"
  exit 1
fi

error=0

PHP_VERSION=$1

dirs="Classes Migrations Legacy tests"
skipfiles=".Build/*,Resources/*"

# wird PHP8.1 oder höher geprüft wird
if [[ "${PHP_VERSION}" < "8.1" ]]; then
  hits=$(find $dirs -type f -name '*.php' -exec grep -l "@php81" {} \; | tr '\n' ',')
  skipfiles="$skipfiles,$hits"
  # Letztes Komma entfernen
  skipfiles=${skipfiles%?}
fi

.Build/bin/phpcs --ignore=$skipfiles -p . --standard=.Build/vendor/phpcompatibility/php-compatibility/PHPCompatibility --runtime-set testVersion $PHP_VERSION

exit $error
