<?php
/**
 * Uninstall da Base de Conhecimento — retenção v1.
 *
 * Search Projection e seu estado são derivados, porém o contrato G-580 determina
 * retenção por default. Limpeza definitiva exige gate futuro explícito.
 *
 * @package BDC_Knowledge_Base
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Intencionalmente não destrutivo:
// - não remove bdc_kb_search_documents;
// - não remove bdc_kb_search_projection_state;
// - não remove posts, metadata ou taxonomias;
// - não executa DROP/TRUNCATE/DELETE.
