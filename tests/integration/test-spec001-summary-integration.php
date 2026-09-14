<?php
/**
 * Integração WordPress real da SPEC-001.
 *
 * @package BDC_Knowledge_Base
 */

declare(strict_types=1);

use BDC\KnowledgeBase\Admin_Page;
use BDC\KnowledgeBase\Meta_Contract;
use BDC\KnowledgeBase\Summary_Store;

final class BDC_SPEC001_Redirect_Intercept extends RuntimeException {
    public function __construct(public readonly string $location) {
        parent::__construct($location);
    }
}

final class BDC_SPEC001_WP_Die_Intercept extends RuntimeException {}

final class BDC_SPEC001_Summary_Integration_Test extends WP_UnitTestCase {
    private int $admin_id;
    private int $author_id;
    private int $contributor_id;
    private int $post_id;
    private int $write_attempt = 0;
    /** @var int[] */
    private array $fail_write_attempts = array();

    public function set_up(): void {
        parent::set_up();

        Meta_Contract::register();

        $this->admin_id = self::factory()->user->create(array('role' => 'administrator'));
        $this->author_id = self::factory()->user->create(array('role' => 'author'));
        $this->contributor_id = self::factory()->user->create(array('role' => 'contributor'));

        wp_set_current_user($this->admin_id);

        $this->post_id = self::factory()->post->create(
            array(
                'post_type'    => 'post',
                'post_status'  => 'draft',
                'post_title'   => 'Artigo de integração',
                'post_content' => "Conteúdo editorial\ninalterável",
                'post_author'  => $this->author_id,
            )
        );

        update_post_meta($this->post_id, '_elementor_data', '[{"id":"abc","elType":"container"}]');

        $_GET = array();
        $_POST = array();
        $_SERVER['REQUEST_METHOD'] = 'GET';

        add_filter('wp_redirect', array($this, 'intercept_redirect'), PHP_INT_MAX, 2);
        add_filter('wp_die_handler', array($this, 'provide_wp_die_handler'), PHP_INT_MAX, 1);
    }

    public function tear_down(): void {
        remove_filter('wp_redirect', array($this, 'intercept_redirect'), PHP_INT_MAX);
        remove_filter('wp_die_handler', array($this, 'provide_wp_die_handler'), PHP_INT_MAX);
        $this->disable_fault_injection();
        $_GET = array();
        $_POST = array();
        unset($_SERVER['REQUEST_METHOD']);
        parent::tear_down();
    }

    public function intercept_redirect(string $location, int $status): string {
        unset($status);
        throw new BDC_SPEC001_Redirect_Intercept($location);
    }

    public function provide_wp_die_handler(callable|string $handler): callable {
        unset($handler);
        return static function ($message = '', $title = '', $args = array()): void {
            unset($title, $args);
            $text = is_scalar($message) ? (string) $message : 'wp_die';
            throw new BDC_SPEC001_WP_Die_Intercept($text);
        };
    }

    public function filter_metadata_write(mixed $check, int $object_id, string $meta_key, mixed $meta_value, mixed $extra = null): mixed {
        unset($check, $meta_value, $extra);

        if ($object_id !== $this->post_id || !in_array($meta_key, $this->summary_meta_keys(), true)) {
            return null;
        }

        ++$this->write_attempt;
        if (in_array($this->write_attempt, $this->fail_write_attempts, true)) {
            return false;
        }

        return null;
    }

    public function test_registered_contract_is_exact_and_not_rest_exposed(): void {
        $registered = get_registered_meta_keys('post', 'post');

        foreach (Meta_Contract::fields() as $definition) {
            $this->assertArrayHasKey($definition['key'], $registered);
            $this->assertFalse($registered[$definition['key']]['show_in_rest']);
            $this->assertTrue($registered[$definition['key']]['single']);
            $this->assertSame('string', $registered[$definition['key']]['type']);
        }
    }

    public function test_g001_summary_write_preserves_editorial_and_elementor(): void {
        $before_title = get_post_field('post_title', $this->post_id, 'raw');
        $before_content = get_post_field('post_content', $this->post_id, 'raw');
        $before_elementor = get_post_meta($this->post_id, '_elementor_data', true);

        $result = Summary_Store::update($this->post_id, array('objective' => 'Objetivo validado'));

        $this->assertFalse(is_wp_error($result));
        $this->assertSame($before_title, get_post_field('post_title', $this->post_id, 'raw'));
        $this->assertSame($before_content, get_post_field('post_content', $this->post_id, 'raw'));
        $this->assertSame($before_elementor, get_post_meta($this->post_id, '_elementor_data', true));
    }

    public function test_g020_read_missing_meta_is_empty_and_side_effect_free(): void {
        $this->enable_fault_injection(array());

        $result = Summary_Store::read($this->post_id);

        $this->assertFalse(is_wp_error($result));
        $this->assertSame('', $result['objective']);
        $this->assertSame('', $result['escalation']);
        $this->assertSame('', $result['important']);
        $this->assertSame(0, $this->write_attempt);
    }

    public function test_g020_partial_update_preserves_omitted_fields_and_rereads(): void {
        update_post_meta($this->post_id, '_bdc_es_objective', 'O0');
        update_post_meta($this->post_id, '_bdc_es_escalation', 'E0');
        update_post_meta($this->post_id, '_bdc_es_important', 'I0');
        $this->enable_fault_injection(array());

        $result = Summary_Store::update($this->post_id, array('objective' => 'O1'));

        $this->assertFalse(is_wp_error($result));
        $this->assertSame(Summary_Store::STATUS_SUCCESS, $result['status']);
        $this->assertSame('O1', $result['state']['objective']);
        $this->assertSame('E0', $result['state']['escalation']);
        $this->assertSame('I0', $result['state']['important']);
        $this->assertSame(1, $this->write_attempt);
    }

    public function test_g020_empty_deletes_meta(): void {
        update_post_meta($this->post_id, '_bdc_es_objective', 'Remover');

        $result = Summary_Store::update($this->post_id, array('objective' => " \n\t "));

        $this->assertFalse(is_wp_error($result));
        $this->assertSame('', get_post_meta($this->post_id, '_bdc_es_objective', true));
        $this->assertFalse(metadata_exists('post', $this->post_id, '_bdc_es_objective'));
    }

    public function test_g020_unknown_field_and_oversize_are_zero_write(): void {
        update_post_meta($this->post_id, '_bdc_es_objective', 'O0');
        $this->enable_fault_injection(array());

        $unknown = Summary_Store::update(
            $this->post_id,
            array('objective' => 'O1', 'nao_autorizado' => 'x')
        );
        $this->assertTrue(is_wp_error($unknown));
        $this->assertSame('bdc_kb_unknown_field', $unknown->get_error_code());
        $this->assertSame(0, $this->write_attempt);
        $this->assertSame('O0', get_post_meta($this->post_id, '_bdc_es_objective', true));

        $oversize = Summary_Store::update(
            $this->post_id,
            array('objective' => str_repeat('a', Meta_Contract::MAX_BYTES + 1))
        );
        $this->assertTrue(is_wp_error($oversize));
        $this->assertSame('bdc_kb_value_too_large', $oversize->get_error_code());
        $this->assertSame(0, $this->write_attempt);
    }

    public function test_g020_sanitizes_html_preserves_multiline_unicode_and_backslash(): void {
        $raw = "Linha 1 <script>alert('x')</script>\nLinha 2 çãõ \\ caminho";
        $expected = Meta_Contract::sanitize_text($raw);

        $result = Summary_Store::update($this->post_id, array('important' => $raw));

        $this->assertFalse(is_wp_error($result));
        $this->assertSame($expected, $result['state']['important']);
        $this->assertStringNotContainsString('<script', $result['state']['important']);
        $this->assertStringContainsString('çãõ', $result['state']['important']);
    }

    public function test_g020_no_change_does_not_write(): void {
        update_post_meta($this->post_id, '_bdc_es_objective', 'Mesmo');
        $this->enable_fault_injection(array());

        $result = Summary_Store::update($this->post_id, array('objective' => 'Mesmo'));

        $this->assertFalse(is_wp_error($result));
        $this->assertSame(array(), $result['changed_fields']);
        $this->assertSame(0, $this->write_attempt);
    }

    public function test_g070_store_rejects_user_without_object_capability(): void {
        wp_set_current_user($this->contributor_id);

        $result = Summary_Store::update($this->post_id, array('objective' => 'Não pode'));

        $this->assertTrue(is_wp_error($result));
        $this->assertSame('bdc_kb_forbidden', $result->get_error_code());
        $this->assertSame('', get_post_meta($this->post_id, '_bdc_es_objective', true));
    }

    public function test_g070_rejects_page_even_for_admin(): void {
        $page_id = self::factory()->post->create(array('post_type' => 'page', 'post_status' => 'draft'));

        $result = Summary_Store::read($page_id);

        $this->assertTrue(is_wp_error($result));
        $this->assertSame('bdc_kb_unsupported_post_type', $result->get_error_code());
    }

    public function test_g070_handler_valid_nonce_saves_and_redirects(): void {
        wp_set_current_user($this->admin_id);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'action'       => Admin_Page::ACTION,
            'post_id'      => (string) $this->post_id,
            'bdc_kb_nonce' => wp_create_nonce('bdc_kb_save_summary_' . $this->post_id),
            'summary'      => array('objective' => 'Via handler'),
        );

        try {
            Admin_Page::handle_save();
            $this->fail('O handler deveria redirecionar via PRG.');
        } catch (BDC_SPEC001_Redirect_Intercept $redirect) {
            $this->assertStringContainsString('bdc_summary_status=saved', $redirect->location);
        }

        $this->assertSame('Via handler', get_post_meta($this->post_id, '_bdc_es_objective', true));
    }

    public function test_g070_handler_invalid_nonce_does_not_write(): void {
        wp_set_current_user($this->admin_id);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'action'       => Admin_Page::ACTION,
            'post_id'      => (string) $this->post_id,
            'bdc_kb_nonce' => 'nonce-invalido',
            'summary'      => array('objective' => 'Não gravar'),
        );

        try {
            Admin_Page::handle_save();
            $this->fail('Nonce inválido deveria redirecionar com erro.');
        } catch (BDC_SPEC001_Redirect_Intercept $redirect) {
            $this->assertStringContainsString('bdc_summary_status=invalid_nonce', $redirect->location);
        }

        $this->assertSame('', get_post_meta($this->post_id, '_bdc_es_objective', true));
    }

    public function test_g070_handler_idor_is_blocked_even_when_menu_capability_exists(): void {
        wp_set_current_user($this->contributor_id);
        $this->assertTrue(current_user_can('edit_posts'));
        $this->assertFalse(current_user_can('edit_post', $this->post_id));

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'action'       => Admin_Page::ACTION,
            'post_id'      => (string) $this->post_id,
            'bdc_kb_nonce' => wp_create_nonce('bdc_kb_save_summary_' . $this->post_id),
            'summary'      => array('objective' => 'IDOR'),
        );

        try {
            Admin_Page::handle_save();
            $this->fail('IDOR deveria ser bloqueado no handler.');
        } catch (BDC_SPEC001_Redirect_Intercept $redirect) {
            $this->assertStringContainsString('bdc_summary_status=forbidden', $redirect->location);
        }

        $this->assertSame('', get_post_meta($this->post_id, '_bdc_es_objective', true));
    }

    public function test_g070_get_method_cannot_mutate_handler(): void {
        wp_set_current_user($this->admin_id);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = array(
            'post_id' => (string) $this->post_id,
            'summary' => array('objective' => 'Não gravar'),
        );

        $this->expectException(BDC_SPEC001_WP_Die_Intercept::class);
        try {
            Admin_Page::handle_save();
        } finally {
            $this->assertSame('', get_post_meta($this->post_id, '_bdc_es_objective', true));
        }
    }

    public function test_b006_real_metadata_failure_on_second_write_restores_snapshot(): void {
        update_post_meta($this->post_id, '_bdc_es_objective', 'O0');
        update_post_meta($this->post_id, '_bdc_es_escalation', 'E0');
        $this->enable_fault_injection(array(2));

        $result = Summary_Store::update(
            $this->post_id,
            array('objective' => 'O1', 'escalation' => 'E1')
        );

        $this->assertTrue(is_wp_error($result));
        $this->assertSame(Summary_Store::STATUS_FAIL_SAFE, $result->get_error_data()['status']);
        $this->assertSame('O0', get_post_meta($this->post_id, '_bdc_es_objective', true));
        $this->assertSame('E0', get_post_meta($this->post_id, '_bdc_es_escalation', true));
    }

    public function test_b006_real_metadata_compensation_failure_is_critical(): void {
        update_post_meta($this->post_id, '_bdc_es_objective', 'O0');
        update_post_meta($this->post_id, '_bdc_es_escalation', 'E0');
        $this->enable_fault_injection(array(2, 3));

        $result = Summary_Store::update(
            $this->post_id,
            array('objective' => 'O1', 'escalation' => 'E1')
        );

        $this->assertTrue(is_wp_error($result));
        $this->assertSame(Summary_Store::STATUS_PARTIAL_FAILURE_CRITICAL, $result->get_error_data()['status']);
        $this->assertSame('O1', get_post_meta($this->post_id, '_bdc_es_objective', true));
        $this->assertSame('E0', get_post_meta($this->post_id, '_bdc_es_escalation', true));
    }

    private function enable_fault_injection(array $fail_write_attempts): void {
        $this->write_attempt = 0;
        $this->fail_write_attempts = $fail_write_attempts;
        add_filter('update_post_metadata', array($this, 'filter_metadata_write'), PHP_INT_MAX, 5);
        add_filter('delete_post_metadata', array($this, 'filter_metadata_write'), PHP_INT_MAX, 5);
    }

    private function disable_fault_injection(): void {
        remove_filter('update_post_metadata', array($this, 'filter_metadata_write'), PHP_INT_MAX);
        remove_filter('delete_post_metadata', array($this, 'filter_metadata_write'), PHP_INT_MAX);
        $this->write_attempt = 0;
        $this->fail_write_attempts = array();
    }

    /** @return string[] */
    private function summary_meta_keys(): array {
        return array_map(
            static fn(array $definition): string => $definition['key'],
            Meta_Contract::fields()
        );
    }
}
