<?php
/**
 * ACF (Advanced Custom Fields) module — exposes ACF field groups, field
 * definitions and per-post field values as AI tools.
 *
 * @package GoldtWebMCP\ACF
 */

namespace GoldtWebMCP\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACF_Module extends Module_Base {

	protected $module_name = 'acf';

	public function execute_tool( $tool_name, $params = array() ) {
		if ( is_array( $params ) ) {
			foreach ( $params as $k => $v ) {
				if ( is_string( $v ) && $v !== '' && isset( $v[0] ) && ( $v[0] === '[' || $v[0] === '{' ) ) {
					$decoded = json_decode( $v, true );
					if ( is_array( $decoded ) ) {
						$params[ $k ] = $decoded;
					}
				}
			}
		}
		return parent::execute_tool( $tool_name, $params );
	}

	protected function register_tools() {
		// ── Reads (free) ──
		$this->register_tool( 'listFieldGroups', array(
			'description'  => 'List all ACF field groups (key, title, active, location rules).',
			'input_schema' => array( 'type' => 'object', 'properties' => (object) array() ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getFieldGroup', array(
			'description'  => 'Get one ACF field group with its fields.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'group_key' ), 'properties' => array( 'group_key' => array( 'type' => 'string', 'description' => 'e.g. group_abc123' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'listFields', array(
			'description'  => 'List the field definitions inside a field group.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'group_key' ), 'properties' => array( 'group_key' => array( 'type' => 'string' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getFieldValue', array(
			'description'  => 'Get the value of one ACF field on a post (by field name or key).',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id', 'selector' ), 'properties' => array(
				'post_id' => array( 'type' => 'integer' ), 'selector' => array( 'type' => 'string', 'description' => 'Field name or field_ key' ),
			) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getFieldsForPost', array(
			'description'  => 'Get all ACF field values for a post (name => value map).',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id' ), 'properties' => array( 'post_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getFieldObject', array(
			'description'  => 'Get an ACF field object (config + current value) on a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id', 'selector' ), 'properties' => array(
				'post_id' => array( 'type' => 'integer' ), 'selector' => array( 'type' => 'string' ),
			) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'searchByFieldValue', array(
			'description'  => 'Find posts where an ACF field equals a value.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'field_name', 'value' ), 'properties' => array(
				'field_name' => array( 'type' => 'string' ),
				'value'      => array( 'type' => 'string' ),
				'post_type'  => array( 'type' => 'string' ),
				'limit'      => array( 'type' => 'integer' ),
			) ),
			'required_scope' => 'read',
		) );

		// ── Writes (pro) ──
		$this->register_tool( 'updateFieldValue', array(
			'description'  => 'Set the value of an ACF field on a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id', 'selector', 'value' ), 'properties' => array(
				'post_id' => array( 'type' => 'integer' ), 'selector' => array( 'type' => 'string' ), 'value' => array(),
			) ),
			'required_scope' => 'write',
		) );
		$this->register_tool( 'deleteFieldValue', array(
			'description'  => 'Delete an ACF field value from a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id', 'selector' ), 'properties' => array(
				'post_id' => array( 'type' => 'integer' ), 'selector' => array( 'type' => 'string' ),
			) ),
			'required_scope' => 'write',
		) );
		$this->register_tool( 'createFieldGroup', array(
			'description'  => 'Create a new ACF field group.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'title' ), 'properties' => array(
				'title'    => array( 'type' => 'string' ),
				'location' => array( 'type' => 'array', 'description' => 'ACF location rules; default: all posts' ),
			) ),
			'required_scope' => 'write',
		) );
		$this->register_tool( 'addField', array(
			'description'  => 'Add a field to an existing field group.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'group_key', 'label', 'name', 'type' ), 'properties' => array(
				'group_key' => array( 'type' => 'string' ),
				'label'     => array( 'type' => 'string' ),
				'name'      => array( 'type' => 'string' ),
				'type'      => array( 'type' => 'string', 'description' => 'text | textarea | number | select | true_false | image | wysiwyg …' ),
			) ),
			'required_scope' => 'write',
		) );
		$this->register_tool( 'updateFieldGroup', array(
			'description'  => 'Update a field group title / active state.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'group_key' ), 'properties' => array(
				'group_key' => array( 'type' => 'string' ),
				'title'     => array( 'type' => 'string' ),
				'active'    => array( 'type' => 'boolean' ),
			) ),
			'required_scope' => 'write',
		) );
	}

	private function require_cap( $cap ) {
		if ( ! \current_user_can( $cap ) ) {
			return new \WP_Error( 'forbidden', "{$cap} capability required", array( 'status' => 403 ) );
		}
		return null;
	}

	private function require_post( $params ) {
		$id = \absint( $params['post_id'] ?? 0 );
		if ( ! $id || ! \get_post( $id ) ) {
			return new \WP_Error( 'post_not_found', 'Post not found', array( 'status' => 404 ) );
		}
		return $id;
	}

	/**
	 * Per-object permission ceiling (SEC-00): the token's user must be able to
	 * read this specific post natively (`read_post` — covers private posts of
	 * others via read_private_posts, and drafts/pending via edit_post).
	 *
	 * @param int $id Post ID.
	 * @return \WP_Error|null
	 */
	private function require_read_post( $id ) {
		if ( ! \current_user_can( 'read_post', $id ) ) {
			return new \WP_Error( 'forbidden', 'read_post capability required for this post', array( 'status' => 403 ) );
		}
		return null;
	}

	public function execute_listFieldGroups( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$groups = \acf_get_field_groups();
		$out = array();
		foreach ( (array) $groups as $g ) {
			$out[] = array(
				'key'      => $g['key'] ?? '',
				'title'    => $g['title'] ?? '',
				'active'   => (bool) ( $g['active'] ?? true ),
				'location' => $g['location'] ?? array(),
			);
		}
		return $this->success_response( array( 'field_groups' => $out, 'count' => count( $out ) ) );
	}

	public function execute_getFieldGroup( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$key = \sanitize_text_field( $params['group_key'] ?? '' );
		$group = \acf_get_field_group( $key );
		if ( ! $group ) return new \WP_Error( 'group_not_found', 'Field group not found', array( 'status' => 404 ) );
		$fields = \acf_get_fields( $key );
		$flist = array();
		foreach ( (array) $fields as $f ) {
			$flist[] = array( 'key' => $f['key'] ?? '', 'label' => $f['label'] ?? '', 'name' => $f['name'] ?? '', 'type' => $f['type'] ?? '' );
		}
		return $this->success_response( array(
			'key' => $group['key'] ?? '', 'title' => $group['title'] ?? '',
			'active' => (bool) ( $group['active'] ?? true ), 'fields' => $flist,
		) );
	}

	public function execute_listFields( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$key = \sanitize_text_field( $params['group_key'] ?? '' );
		$fields = \acf_get_fields( $key );
		if ( $fields === false ) return new \WP_Error( 'group_not_found', 'Field group not found', array( 'status' => 404 ) );
		$out = array();
		foreach ( (array) $fields as $f ) {
			$out[] = array(
				'key' => $f['key'] ?? '', 'label' => $f['label'] ?? '', 'name' => $f['name'] ?? '',
				'type' => $f['type'] ?? '', 'required' => (bool) ( $f['required'] ?? false ),
			);
		}
		return $this->success_response( array( 'fields' => $out, 'count' => count( $out ) ) );
	}

	public function execute_getFieldValue( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		$deny = $this->require_read_post( $id );
		if ( $deny ) {
			return $deny;
		}
		$selector = \sanitize_text_field( $params['selector'] ?? '' );
		return $this->success_response( array( 'post_id' => $id, 'selector' => $selector, 'value' => \get_field( $selector, $id ) ) );
	}

	public function execute_getFieldsForPost( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		$deny = $this->require_read_post( $id );
		if ( $deny ) {
			return $deny;
		}
		$fields = \get_fields( $id );
		return $this->success_response( array( 'post_id' => $id, 'fields' => is_array( $fields ) ? $fields : array() ) );
	}

	public function execute_getFieldObject( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		$deny = $this->require_read_post( $id );
		if ( $deny ) {
			return $deny;
		}
		$selector = \sanitize_text_field( $params['selector'] ?? '' );
		$obj = \get_field_object( $selector, $id );
		if ( ! $obj ) return new \WP_Error( 'field_not_found', 'Field not found', array( 'status' => 404 ) );
		return $this->success_response( array(
			'post_id' => $id, 'key' => $obj['key'] ?? '', 'name' => $obj['name'] ?? '',
			'label' => $obj['label'] ?? '', 'type' => $obj['type'] ?? '', 'value' => $obj['value'] ?? null,
		) );
	}

	public function execute_searchByFieldValue( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$field = \sanitize_text_field( $params['field_name'] ?? '' );
		$value = $params['value'] ?? '';
		$limit = \absint( $params['limit'] ?? 20 ) ?: 20;
		$out  = array();
		$page = 1;
		// Page through matches, dropping any post the user cannot read natively
		// (SEC-00: never return — not even the title of — a non-readable post).
		do {
			$posts = \get_posts(
				array(
					'post_type'   => ! empty( $params['post_type'] ) ? \sanitize_key( $params['post_type'] ) : 'any',
					'numberposts' => $limit,
					'paged'       => $page,
					'post_status' => 'any',
					'meta_key'    => $field, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- unchanged query, now paged.
					'meta_value'  => \sanitize_text_field( (string) $value ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			foreach ( $posts as $p ) {
				if ( ! \current_user_can( 'read_post', $p->ID ) ) {
					continue;
				}
				$out[] = array( 'post_id' => (int) $p->ID, 'title' => \get_the_title( $p ), 'type' => $p->post_type );
				if ( count( $out ) >= $limit ) {
					break 2;
				}
			}
			++$page;
			$fetched = count( $posts );
		} while ( $fetched === $limit && $page <= 20 );
		return $this->success_response( array( 'results' => $out, 'count' => count( $out ) ) );
	}

	public function execute_updateFieldValue( $params ) {
		$deny = $this->require_cap( 'edit_others_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		$selector = \sanitize_text_field( $params['selector'] ?? '' );
		if ( $selector === '' ) return new \WP_Error( 'missing_parameter', 'selector required', array( 'status' => 400 ) );
		$ok = \update_field( $selector, $params['value'] ?? '', $id );
		return $this->success_response( array( 'post_id' => $id, 'selector' => $selector, 'updated' => (bool) $ok ) );
	}

	public function execute_deleteFieldValue( $params ) {
		$deny = $this->require_cap( 'edit_others_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		$selector = \sanitize_text_field( $params['selector'] ?? '' );
		$ok = \delete_field( $selector, $id );
		return $this->success_response( array( 'post_id' => $id, 'selector' => $selector, 'deleted' => (bool) $ok ) );
	}

	public function execute_createFieldGroup( $params ) {
		$deny = $this->require_cap( 'manage_options' ); if ( $deny ) return $deny;
		$title = \sanitize_text_field( $params['title'] ?? '' );
		if ( $title === '' ) return new \WP_Error( 'missing_parameter', 'title required', array( 'status' => 400 ) );
		if ( ! function_exists( 'acf_update_field_group' ) ) return new \WP_Error( 'not_available', 'ACF write API unavailable', array( 'status' => 501 ) );
		$location = ( isset( $params['location'] ) && is_array( $params['location'] ) ) ? $params['location']
			: array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) );
		$group = \acf_update_field_group( array(
			'key'      => 'group_' . substr( md5( $title . microtime() ), 0, 13 ),
			'title'    => $title,
			'location' => $location,
			'active'   => true,
		) );
		return $this->success_response( array( 'key' => $group['key'] ?? '', 'title' => $title ) );
	}

	public function execute_addField( $params ) {
		$deny = $this->require_cap( 'manage_options' ); if ( $deny ) return $deny;
		if ( ! function_exists( 'acf_update_field' ) ) return new \WP_Error( 'not_available', 'ACF write API unavailable', array( 'status' => 501 ) );
		$group_key = \sanitize_text_field( $params['group_key'] ?? '' );
		if ( ! \acf_get_field_group( $group_key ) ) return new \WP_Error( 'group_not_found', 'Field group not found', array( 'status' => 404 ) );
		$name = \sanitize_key( $params['name'] ?? '' );
		$field = \acf_update_field( array(
			'key'    => 'field_' . substr( md5( $name . microtime() ), 0, 13 ),
			'label'  => \sanitize_text_field( $params['label'] ?? '' ),
			'name'   => $name,
			'type'   => \sanitize_key( $params['type'] ?? 'text' ),
			'parent' => $group_key,
		) );
		return $this->success_response( array( 'key' => $field['key'] ?? '', 'name' => $name, 'group_key' => $group_key ) );
	}

	public function execute_updateFieldGroup( $params ) {
		$deny = $this->require_cap( 'manage_options' ); if ( $deny ) return $deny;
		if ( ! function_exists( 'acf_update_field_group' ) ) return new \WP_Error( 'not_available', 'ACF write API unavailable', array( 'status' => 501 ) );
		$key = \sanitize_text_field( $params['group_key'] ?? '' );
		$group = \acf_get_field_group( $key );
		if ( ! $group ) return new \WP_Error( 'group_not_found', 'Field group not found', array( 'status' => 404 ) );
		if ( isset( $params['title'] ) ) $group['title'] = \sanitize_text_field( $params['title'] );
		if ( isset( $params['active'] ) ) $group['active'] = ! empty( $params['active'] );
		\acf_update_field_group( $group );
		return $this->success_response( array( 'key' => $key, 'updated' => true ) );
	}
}
