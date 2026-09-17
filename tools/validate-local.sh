#!/bin/sh

set -eu

ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
PLUGIN="$ROOT/plugin/base-conhecimento-inteligencia-integrada"

php_lint() {
	find "$PLUGIN" -type f -name '*.php' -print0 | xargs -0 -n1 php -l >/tmp/bdc-kb-php-lint.log
}

php_lint
php "$ROOT/tests/unit/spec001-summary-store.php"
php "$ROOT/tests/unit/spec003-review-store.php"
php "$ROOT/tests/unit/spec004-content-extractor.php"
php "$ROOT/tests/unit/spec004-knowledge-document.php"
php "$ROOT/tests/unit/spec004-projection-plan.php"
php "$ROOT/tests/unit/spec004-elementor-gateway.php"
php "$ROOT/tests/unit/spec004-stale-source-guard.php"
php "$ROOT/tests/unit/spec004-elementor-projection-builder.php"

if command -v node >/dev/null 2>&1; then
	node --check "$PLUGIN/assets/js/workspace.js"
else
	echo 'NOT_VERIFIED: node ausente; execute em container Node para validar workspace.js.'
fi

echo 'LOCAL_VALIDATION_PASS'