<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ProductMetadataTest extends TestCase {
	private string $bootstrap;

	protected function setUp(): void {
		$path = BDC_TEST_ROOT . '/plugin/base-conhecimento-inteligencia-integrada/base-conhecimento-inteligencia-integrada.php';
		$content = file_get_contents( $path );
		self::assertIsString( $content );
		$this->bootstrap = $content;
	}

	public function test_product_version_is_canonical_spec006_version(): void {
		self::assertStringContainsString( 'Version: 0.6.0-dev', $this->bootstrap );
		self::assertStringContainsString( "define( 'BDC_KB_VERSION', '0.6.0-dev' );", $this->bootstrap );
	}

	public function test_license_and_update_identity_are_declared(): void {
		self::assertStringContainsString( 'License: GPL-2.0-or-later', $this->bootstrap );
		self::assertStringContainsString( 'Update URI: https://github.com/R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada', $this->bootstrap );
	}
}
