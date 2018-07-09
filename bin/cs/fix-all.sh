#!/bin/bash

# ensure cwd is project root
cd "$(dirname "$(realpath "$0")")/../..";

echo -e "### Fixing all files with php-cs-fixer ...\n"
vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --using-cache=yes
