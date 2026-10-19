#!/bin/bash

#######################################################################################
#
# Update the PHPCS_VERSION GitHub Actions variable in each repo which uses wp-phpcs
# to match the version in this repository's VERSION file.
#
# @version 1.0.0
#
# @notice Requires the GitHub CLI (gh) to be authenticated.
#
# @usage `./dev/bin/github-actions-versions.sh <owner/repo> [<owner/repo> ...]`
#
# @example
#   `./dev/bin/github-actions-versions.sh lipemat/matlipe lipemat/natures-canvas lipemat/onpoint`
#
#####################################################################################

GREEN="$(tput setaf 2)"
WHITE="$(tput setaf 7)"
YELLOW="$(tput setaf 3)"
RESET_COLOR="$(tput sgr0)"

PROJECT=$(php -r "echo dirname(realpath('$0'), 3);")

## Fix windows paths
PROJECT=${PROJECT//\\//}

VERSION=$(tr -d '[:space:]' <"${PROJECT}/VERSION")

if [[ 0 -eq $# ]]; then
    echo "${YELLOW}[phpcs-version]${WHITE} Usage: $0 <owner/repo> [<owner/repo> ...]${RESET_COLOR}"
    exit 1
fi

for repo in "$@"; do
    gh variable set PHPCS_VERSION --body "${VERSION}" --repo "${repo}"
    echo "${YELLOW}[phpcs-version]${GREEN} ${repo} PHPCS_VERSION set to ${VERSION}.${WHITE}"
done

echo "${RESET_COLOR}"

exit 0
