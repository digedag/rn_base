#!/bin/bash

error=0

# Prüfen, ob PHP8.1 oder höher installiert ist
php -v | grep -q 'PHP 8\.[1-9]\|PHP 9\.' && filter='' || filter='-not -name "*@php81*"'

# Schleife über alle Verzeichnisse
for dir in "$@"
do
  # Suche nach PHP-Dateien und prüfe Syntax
  while IFS= read -r -d '' file; do
    if ! php -l "$file"; then
      error=1
    fi
  done < <(find "$dir" -type f -name '*.php' ${filter} -print0)
done

exit $error
