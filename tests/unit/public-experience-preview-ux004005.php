<?php

declare(strict_types=1);

$root = '/mnt/data/ux3build/base-conhecimento-inteligencia-integrada/';
$read = static fn(string $rel): string => (string) file_get_contents($root . $rel);

$bootstrap = $read('base-conhecimento-inteligencia-integrada.php');
$experience = $read('includes/class-public-experience.php');
$auth = $read('includes/class-public-auth-bridge.php');
$navigation = $read('includes/class-public-navigation.php');
$article_content = $read('includes/class-public-article-content.php');
$home = $read('includes/class-public-home-read-model.php');
$article = $read('includes/class-public-article-read-model.php');
$tips = $read('includes/class-helpful-tips-store.php');
$home_template = $read('templates/public-home-preview.php');
$article_template = $read('templates/public-article-preview.php');
$foundation_css = $read('assets/css/public-foundation.css');
$header_css = $read('assets/css/public-header.css');
$home_css = $read('assets/css/public-home.css');
$article_css = $read('assets/css/public-article.css');

$strip_comments = static function ( string $source ): string {
    $tokens = token_get_all( $source );
    $out = '';
    foreach ( $tokens as $token ) {
        if ( is_array( $token ) && in_array( $token[0], array( T_COMMENT, T_DOC_COMMENT ), true ) ) continue;
        $out .= is_array( $token ) ? $token[1] : $token;
    }
    return $out;
};

$runtime = implode("\n", array_map($strip_comments, [$experience,$auth,$navigation,$article_content,$home,$article,$tips]));
$write_pattern = '/update_(?:post|option|site_option|post_meta)\s*\(|add_(?:option|post_meta)\s*\(|delete_(?:option|post_meta)\s*\(|wp_update_post\s*\(|wp_insert_post\s*\(|wp_set_object_terms\s*\(/i';
$network_pattern = '/wp_remote_[A-Za-z0-9_]*\s*\(|curl_[A-Za-z0-9_]+\s*\(|fsockopen\s*\(|stream_socket_client\s*\(/i';

$checks = [
    'version_preview3' => str_contains($bootstrap, 'Version: 0.5.0-ux004005.3'),
    'preview_build_on' => str_contains($bootstrap, "BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD', true"),
    'auth_bridge_loaded' => str_contains($bootstrap, 'class-public-auth-bridge.php'),
    'navigation_loaded' => str_contains($bootstrap, 'class-public-navigation.php'),
    'article_content_loaded' => str_contains($bootstrap, 'class-public-article-content.php'),
    'manage_options_nonce' => str_contains($experience, "current_user_can( 'manage_options' )") && str_contains($experience, 'wp_verify_nonce'),
    'no_default_takeover' => str_contains($experience, 'null === $kind') && str_contains($experience, 'return $template;'),
    'no_editorial_writes' => 1 !== preg_match($write_pattern, $runtime),
    'no_network' => 1 !== preg_match($network_pattern, $runtime),
    'entra_shortcode_bridge' => str_contains($auth, "'bdc_entra_login'") && str_contains($auth, 'do_shortcode'),
    'auth_wp_fallback' => str_contains($auth, 'wp_logout_url') && str_contains($auth, 'wp_login_url'),
    'auth_primed_enqueue' => str_contains($experience, 'Public_Auth_Bridge::prime()'),
    'navigation_menu_first' => str_contains($navigation, 'get_nav_menu_locations') && str_contains($navigation, 'wp_get_nav_menu_items'),
    'navigation_page_fallback' => str_contains($navigation, 'get_page_by_path') && str_contains($navigation, "'title' => \$title"),
    'custom_logo_support' => str_contains($experience, "get_theme_mod( 'custom_logo'") && str_contains($experience, 'wp_get_attachment_image_url'),
    'home_search_preserved' => str_contains($home, 'Search_Service::search'),
    'home_category_icons' => str_contains($home, 'dashicons-email') && str_contains($home, 'dashicons-shield'),
    'home_count_read_model' => str_contains($home, 'published_count'),
    'home_redesign_copy' => str_contains($home_template, 'O que você precisa encontrar?') && str_contains($home_template, 'Explorar a Base'),
    'home_no_technical_cloud_copy' => ! str_contains($home_template, 'Quality/telemetry/vocabulary') && ! str_contains($home_template, 'content-only'),
    'article_uses_compat_stage' => str_contains($article_template, 'Public_Article_Content::render'),
    'article_no_direct_the_content' => ! str_contains($article_template, 'the_content();'),
    'article_pipeline_preserved' => str_contains($article_content, "apply_filters( 'the_content'"),
    'legacy_chrome_strong_evidence' => str_contains($article_content, "'responsavel'") && str_contains($article_content, "'publicado'") && str_contains($article_content, "'atualizado'") && str_contains($article_content, '$hits < 2'),
    'legacy_chrome_fail_safe' => str_contains($article_content, 'return $html;') && str_contains($article_content, 'class_exists'),
    'gre_suppressed_preview_only' => str_contains($experience, 'Helpful_Tips_Renderer') && str_contains($experience, 'Frontend_Renderer'),
    'gac_wpui_not_removed' => ! str_contains($experience, 'GAC\\') && ! str_contains($experience, 'WPUI_Frontend'),
    'tips_autofit' => str_contains($article_css, 'repeat(auto-fit,minmax(220px,1fr))'),
    'summary_wider_sticky' => str_contains($article_css, '330px') && str_contains($article_css, 'position:sticky'),
    'print_contract' => str_contains($article_css, '@media print'),
    'responsive_782_520' => str_contains($home_css, '@media(max-width:782px)') && str_contains($home_css, '@media(max-width:520px)') && str_contains($article_css, '@media(max-width:782px)') && str_contains($article_css, '@media(max-width:520px)'),
    'public_css_scoped' => str_contains($foundation_css, '.bdc-public') && str_contains($header_css, '.bdc-public-header') && str_contains($home_css, '.bdc-home-') && str_contains($article_css, '.bdc-reader-'),
    'global_search_on_article' => str_contains($experience, 'render_global_search') && str_contains($experience, 'bdc_global_q'),
    'global_search_keeps_preview_reader' => str_contains($experience, 'article_preview_url( $post_id )'),
    'home_search_first' => str_contains($home_template, 'bdc-home-search-stage') && str_contains($home_template, 'data-bdc-primary-search'),
    'home_explore_collapsed' => str_contains($home_template, '<details class="bdc-home-explore"'),
    'preview_links_use_new_reader' => str_contains($home_template, 'Public_Experience::article_preview_url'),
    'keyboard_search_asset' => is_file($root . 'assets/js/public-search.js') && str_contains((string) file_get_contents($root . 'assets/js/public-search.js'), "event.key.toLowerCase() === 'k'"),
    'article_clean_heading' => str_contains($article_template, 'bdc-reader-heading') && ! str_contains($article_template, 'bdc-reader-hero__subtitle'),
    'no_asi_markup_dependency' => ! str_contains($runtime . $home_template . $article_template, 'asi_search_form') && ! str_contains($runtime . $home_template . $article_template, 'bdc_word_cloud'),
];

$failed = 0;
foreach ($checks as $name => $pass) {
    echo ($pass ? 'PASS ' : 'FAIL ') . $name . PHP_EOL;
    if (!$pass) ++$failed;
}
echo "\nRESULT passed=" . (count($checks)-$failed) . " failed={$failed}\n";
exit($failed ? 1 : 0);