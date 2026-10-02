#!/bin/sh
set -eu

setup_script_dir=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
if ! command -v node >/dev/null 2>&1; then
    echo 'Required command is missing: node' >&2
    exit 69
fi
if ! node -e 'process.exit(Number(process.versions.node.split(".")[0]) >= 22 ? 0 : 1)'; then
    echo 'Node.js 22 or newer is required.' >&2
    exit 69
fi
exec node "$setup_script_dir/setup-release-signing.mjs" "$@"
