#!/bin/bash

if [ "$#" -eq 0 ]; then
  echo "Bitte Verzeichnisse als Parameter übergeben"
  exit 1
fi

error=0

PHP_VERSION=$(php -r "echo PHP_VERSION;")

for dir in "$@"; do

# Prüfen, ob PHP8.1 oder höher installiert ist
if [[ "${PHP_VERSION}" > "8.0" ]]; then
  # Wenn PHP8.1 oder höher installiert ist, führe die Suche ohne Filter aus
  while IFS= read -r file; do
    if ! php -l "$file"; then
      error=1
    fi
  done < <(find $dir -type f -name '*.php' -print)
else
  # Loop over all PHP files in the directory (and its subdirectories)
  while IFS= read -r -d '' file; do
    if ! grep -q "@php81" "$file"; then
      php -l "$file"
      php_error=$? # store the return value of the php command in a variable
      if [ $php_error -ne 0 ]; then
        echo "Error: $file contains syntax errors."
        error=1
      fi
    fi
  done < <(find "$dir" -type f -name '*.php' -print0)

fi
done

exit $error
