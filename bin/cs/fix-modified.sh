#!/bin/bash

# ensure cwd is project root
cd "$(dirname "$(realpath "$0")")/../..";

# get all modified files of the current branch and pass them on to php-cs-fixer to check
echo -e "### Fixing modified files with php-cs-fixer ...\n"
CHANGED_FILES=($(git status --porcelain | grep -e '^[AM]\(.*\).php$' | cut -c 3-));
vendor/bin/php-cs-fixer fix --config=.php_cs.dist --verbose --using-cache=no --path-mode=intersection -- "${CHANGED_FILES[@]}"
