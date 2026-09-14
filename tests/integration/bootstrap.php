<?php
/**
 * Bootstrap da integração WordPress da SPEC-001.
 *
 * Requer o WordPress Core Test Suite instalado localmente.
 */

declare(strict_types=1);

$tests_dir = getenv('WP_TESTS_DIR');
if (!is_string($tests_dir) || '' === $tests_dir) {
    $tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

$functions = $tests_dir . '/includes/functions.php';
$bootstrap = $tests_dir . '/includes/bootstrap.php';

if (!is_file($functions) || !is_file($bootstrap)) {
    fwrite(
        STDERR,
        "WordPress Core Test Suite não encontrado. Defina WP_TESTS_DIR apontando para wordpress-tests-lib.\n"
    );
    exit(1);
}

require_once $functions;

tests_add_filter(
    'muplugins_loaded',
    static function (): void {
        $plugin = dirname(__DIR__, 2) . '/plugin/base-conhecimento-inteligencia-integrada/base-conhecimento-inteligencia-integrada.php';
        if (!is_file($plugin)) {
            throw new RuntimeException('Plugin da SPEC-001 não encontrado: ' . $plugin);
        }
        require_once $plugin;
    }
);

require $bootstrap;
