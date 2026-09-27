#!/bin/sh
set -eu

usage() {
    echo 'Usage: sh updater/tools/setup-release-signing.sh [--apply] [--repo OWNER/REPO] [--key-dir PATH]'
    echo 'Default: check only. --apply generates missing keys and configures GitHub.'
}

setup_repo=portaldots/PortalDots
setup_key_dir=''
setup_apply=false
while [ "$#" -gt 0 ]; do
    case "$1" in
        --apply) setup_apply=true; shift ;;
        --repo|--key-dir)
            if [ "$#" -lt 2 ] || [ -z "$2" ]; then usage >&2; exit 64; fi
            case "$1" in
                --repo) setup_repo=$2 ;;
                --key-dir) setup_key_dir=$2 ;;
            esac
            shift 2
            ;;
        --help|-h) usage; exit 0 ;;
        *) usage >&2; exit 64 ;;
    esac
done

if [ -z "$setup_key_dir" ]; then
    setup_key_dir=${HOME:?HOME is required}/.portaldots-release-keys
fi

for setup_tool in php git gh; do
    if ! command -v "$setup_tool" >/dev/null 2>&1; then
        echo "Required command is missing: $setup_tool" >&2
        exit 69
    fi
done
php -r 'if (PHP_VERSION_ID < 80300 || !extension_loaded("sodium")) { fwrite(STDERR, "PHP 8.3+ with sodium is required.\n"); exit(69); }'

case "$setup_key_dir" in
    /*) ;;
    *) setup_key_dir=$PWD/$setup_key_dir ;;
esac
setup_script_dir=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
cd "$setup_script_dir/../.."
set -- --repo "$setup_repo" --key-dir "$setup_key_dir"
if [ "$setup_apply" = true ]; then
    # Never regenerate or replace an existing directory (including a dangling symlink).
    if [ ! -e "$setup_key_dir" ] && [ ! -L "$setup_key_dir" ]; then
        set -- "$@" --generate
    fi
    set -- "$@" --apply
fi
exec php "$setup_script_dir/setup-release-signing.php" "$@"
