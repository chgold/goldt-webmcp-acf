<?php
/**
 * ACF (Advanced Custom Fields) module — exposes ACF field groups, field
 * definitions and field values (posts, users, terms, options pages) as
 * read-only AI tools. The write tools live in Goldnat for ACF — Pro
 * (goldt-webmcp-acf-pro) since 1.2.0, under the same `acf.*` names.
 *
 * @package GoldtWebMCP\ACF
 */

namespace GoldtWebMCP\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF tools module (`acf.*`).
 */
class ACF_Module extends Module_Base {

	/**
	 * Module name — the tool-name prefix in the manifest.
	 *
	 * @var string
	 */
	protected $module_name = 'acf';

	/**
	 * Tool handler for `acf.tool`.
	 *
	 * @param string $tool_name Tool name (without module prefix).
	 * @param array  $params    Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_tool( $tool_name, $params = array() ) {
		if ( is_array( $params ) ) {
			foreach ( $params as $k => $v ) {
				if ( is_string( $v ) && '' !== $v && isset( $v[0] ) && ( '[' === $v[0] || '{' === $v[0] ) ) {
					$decoded = json_decode( $v, true );
					if ( is_array( $decoded ) ) {
						$params[ $k ] = $decoded;
					}
				}
			}
		}
		return parent::execute_tool( $tool_name, $params );
	}

	/**
	 * Register this module's tools.
	 *
	 * @return void
	 */
	protected function register_tools() {
		// ── Reads (free) ──
		$this->register_tool(
			'listFieldGroups',
			array(
				'description'    => 'List all ACF field groups (key, title, active, location rules).',
				'input_schema'   => array(
					'type'       => 'object',
					'properties' => (object) array(),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getFieldGroup',
			array(
				'description'    => 'Get one ACF field group with its fields.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'group_key' ),
					'properties' => array(
						'group_key' => array(
							'type'        => 'string',
							'description' => 'e.g. group_abc123',
						),
					),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'listFields',
			array(
				'description'    => 'List the field definitions inside a field group.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'group_key' ),
					'properties' => array( 'group_key' => array( 'type' => 'string' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getFieldValue',
			array(
				'description'    => 'Get the value of one ACF field on a post (by field name or key).',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id', 'selector' ),
					'properties' => array(
						'post_id'  => array( 'type' => 'integer' ),
						'selector' => array(
							'type'        => 'string',
							'description' => 'Field name or field_ key',
						),
					),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getFieldsForPost',
			array(
				'description'    => 'Get all ACF field values for a post (name => value map).',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id' ),
					'properties' => array( 'post_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getFieldObject',
			array(
				'description'    => 'Get an ACF field object (config + current value) on a post.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id', 'selector' ),
					'properties' => array(
						'post_id'  => array( 'type' => 'integer' ),
						'selector' => array( 'type' => 'string' ),
					),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'searchByFieldValue',
			array(
				'description'    => 'Find posts where an ACF field equals a value.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'field_name', 'value' ),
					'properties' => array(
						'field_name' => array( 'type' => 'string' ),
						'value'      => array( 'type' => 'string' ),
						'post_type'  => array( 'type' => 'string' ),
						'limit'      => array( 'type' => 'integer' ),
					),
				),
				'required_scope' => 'read',
			)
		);

		$this->register_tool(
			'getUserFields',
			array(
				'description'    => 'Get all ACF field values for a WordPress user (key, name, label, type, value per field). Requires edit_user on that user.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'user_id' ),
					'properties' => array(
						'user_id' => array(
							'type'        => 'integer',
							'description' => 'WordPress user ID',
						),
					),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getTermFields',
			array(
				'description'    => 'Get all ACF field values for a taxonomy term (category, tag or custom taxonomy). Requires edit_term on that term.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'term_id' ),
					'properties' => array(
						'term_id'  => array(
							'type'        => 'integer',
							'description' => 'Term ID',
						),
						'taxonomy' => array(
							'type'        => 'string',
							'description' => 'Optional taxonomy slug; when given, the term must belong to it',
						),
					),
				),
				'required_scope' => 'read',
			)
		);
		// Capability-Aware Manifest v1 (#803): options pages are an ACF PRO
		// feature. The tool is always dispatchable (so a direct call returns
		// 424) but is only advertised in the manifest when ACF PRO is active.
		$options_tool = array(
			'description'    => 'Get all ACF field values on an ACF PRO options page by its menu slug (key, name, label, type, value per field). Requires the options page capability.',
			'input_schema'   => array(
				'type'       => 'object',
				'required'   => array( 'page_slug' ),
				'properties' => array(
					'page_slug' => array(
						'type'        => 'string',
						'description' => 'Options page menu_slug, e.g. theme-general-settings',
					),
				),
			),
			'required_scope' => 'read',
		);
		if ( $this->has_capability( 'acf_pro_options_pages' ) ) {
			$this->register_tool( 'getOptionsPageFields', $options_tool );
		} else {
			$manifest       = $this->manifest;
			$this->manifest = null;
			$this->register_tool( 'getOptionsPageFields', $options_tool );
			$this->manifest = $manifest;
		}
	}

	/**
	 * Read the optional `limit` parameter (absint), falling back to a default when empty.
	 *
	 * @param array $params   Validated tool input.
	 * @param int   $fallback Limit used when the parameter is missing or 0.
	 * @return int
	 */
	private function limit_param( $params, $fallback ) {
		$limit = \absint( $params['limit'] ?? $fallback );
		return $limit ? $limit : $fallback;
	}

	/**
	 * Deny unless the current user has a capability.
	 *
	 * @param string $cap Capability name.
	 * @return \WP_Error|null
	 */
	private function require_cap( $cap ) {
		if ( ! \current_user_can( $cap ) ) {
			return new \WP_Error( 'forbidden', "{$cap} capability required", array( 'status' => 403 ) );
		}
		return null;
	}

	/**
	 * Resolve and validate the post_id parameter.
	 *
	 * @param array $params Validated tool input.
	 * @return int|\WP_Error
	 */
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

	/**
	 * Field-group / field-definition reads use ACF's own admin capability
	 * (`acf_get_setting( 'capability' )`, default manage_options) — SEC-00.
	 *
	 * @return \WP_Error|null
	 */
	private function require_acf_admin() {
		return $this->require_cap( \acf_get_setting( 'capability' ) );
	}

	/**
	 * Capability-Aware Manifest v1 (#803) — is an optional dependency present?
	 *
	 * @param string $key Stable capability key (`acf_pro_options_pages`).
	 * @return bool
	 */
	public function has_capability( $key ) {
		if ( 'acf_pro_options_pages' === $key ) {
			return \function_exists( 'acf_is_pro' ) && \acf_is_pro()
				&& \function_exists( 'acf_get_options_page' );
		}
		return false;
	}

	/**
	 * Normalise ACF field objects to a list of key/name/label/type/value rows.
	 *
	 * @param array|false $objects Result of get_field_objects() or a list of field objects.
	 * @return array
	 */
	private function field_rows( $objects ) {
		$out = array();
		foreach ( (array) $objects as $obj ) {
			if ( ! is_array( $obj ) ) {
				continue;
			}
			$out[] = array(
				'key'   => $obj['key'] ?? '',
				'name'  => $obj['name'] ?? '',
				'label' => $obj['label'] ?? '',
				'type'  => $obj['type'] ?? '',
				'value' => $obj['value'] ?? null,
			);
		}
		return $out;
	}

	/**
	 * Tool handler for `acf.getUserFields`.
	 *
	 * SEC-00: ACF shows user fields on the user-edit screen, so the ceiling is
	 * the native per-user `edit_user` meta capability (own profile included).
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getUserFields( $params ) {
		$id = \absint( $params['user_id'] ?? 0 );
		if ( ! $id || ! \get_userdata( $id ) ) {
			return new \WP_Error( 'user_not_found', 'User not found', array( 'status' => 404 ) );
		}
		if ( ! \current_user_can( 'edit_user', $id ) ) {
			return new \WP_Error( 'forbidden', 'edit_user capability required for this user', array( 'status' => 403 ) );
		}
		$rows = $this->field_rows( \get_field_objects( 'user_' . $id ) );
		return $this->success_response(
			array(
				'user_id' => $id,
				'fields'  => $rows,
				'count'   => count( $rows ),
			)
		);
	}

	/**
	 * Tool handler for `acf.getTermFields`.
	 *
	 * SEC-00: ACF shows term fields on the term-edit screen, so the ceiling is
	 * the native per-term `edit_term` meta capability.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getTermFields( $params ) {
		$id       = \absint( $params['term_id'] ?? 0 );
		$taxonomy = ! empty( $params['taxonomy'] ) ? \sanitize_key( $params['taxonomy'] ) : '';
		$term     = $id ? \get_term( $id, $taxonomy ) : null;
		if ( ! $term || \is_wp_error( $term ) ) {
			return new \WP_Error( 'term_not_found', 'Term not found', array( 'status' => 404 ) );
		}
		if ( ! \current_user_can( 'edit_term', $term->term_id ) ) {
			return new \WP_Error( 'forbidden', 'edit_term capability required for this term', array( 'status' => 403 ) );
		}
		$rows = $this->field_rows( \get_field_objects( 'term_' . $term->term_id ) );
		return $this->success_response(
			array(
				'term_id'  => (int) $term->term_id,
				'taxonomy' => $term->taxonomy,
				'name'     => $term->name,
				'fields'   => $rows,
				'count'    => count( $rows ),
			)
		);
	}

	/**
	 * Tool handler for `acf.getOptionsPageFields` (ACF PRO only).
	 *
	 * SEC-00: the ceiling is the options page's own `capability` setting —
	 * the same check ACF applies before rendering that admin page.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getOptionsPageFields( $params ) {
		if ( ! $this->has_capability( 'acf_pro_options_pages' ) ) {
			return new \WP_Error( 'missing_dependency', 'ACF PRO (options pages) is not active on this site', array( 'status' => 424 ) );
		}
		$slug = \sanitize_text_field( $params['page_slug'] ?? '' );
		$page = '' !== $slug ? \acf_get_options_page( $slug ) : false;
		if ( ! $page || ! is_array( $page ) ) {
			return new \WP_Error( 'options_page_not_found', 'Options page not found', array( 'status' => 404 ) );
		}
		$cap  = ! empty( $page['capability'] ) ? $page['capability'] : 'edit_posts';
		$deny = $this->require_cap( $cap );
		if ( $deny ) {
			return $deny;
		}
		$post_id = ! empty( $page['post_id'] ) ? $page['post_id'] : 'options';
		$objects = array();
		foreach ( (array) \acf_get_field_groups() as $group ) {
			if ( ! $this->group_targets_options_page( $group, $slug ) ) {
				continue;
			}
			foreach ( (array) \acf_get_fields( $group ) as $field ) {
				if ( empty( $field['key'] ) ) {
					continue;
				}
				$obj = \get_field_object( $field['key'], $post_id );
				if ( $obj ) {
					$objects[] = $obj;
				}
			}
		}
		$rows = $this->field_rows( $objects );
		return $this->success_response(
			array(
				'page_slug'  => $slug,
				'page_title' => $page['page_title'] ?? '',
				'post_id'    => $post_id,
				'fields'     => $rows,
				'count'      => count( $rows ),
			)
		);
	}

	/**
	 * Does a field group have an `options_page == $slug` location rule?
	 *
	 * @param array  $group Field group array.
	 * @param string $slug  Options page menu slug.
	 * @return bool
	 */
	private function group_targets_options_page( $group, $slug ) {
		foreach ( (array) ( $group['location'] ?? array() ) as $and_rules ) {
			foreach ( (array) $and_rules as $rule ) {
				if ( isset( $rule['param'], $rule['operator'], $rule['value'] )
					&& 'options_page' === $rule['param'] && '==' === $rule['operator'] && $slug === $rule['value'] ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Tool handler for `acf.listFieldGroups`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_listFieldGroups( $params ) {
		$deny = $this->require_acf_admin();
		if ( $deny ) {
			return $deny;
		}
		$groups = \acf_get_field_groups();
		$out    = array();
		foreach ( (array) $groups as $g ) {
			$out[] = array(
				'key'      => $g['key'] ?? '',
				'title'    => $g['title'] ?? '',
				'active'   => (bool) ( $g['active'] ?? true ),
				'location' => $g['location'] ?? array(),
			);
		}
		return $this->success_response(
			array(
				'field_groups' => $out,
				'count'        => count( $out ),
			)
		);
	}

	/**
	 * Tool handler for `acf.getFieldGroup`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getFieldGroup( $params ) {
		$deny = $this->require_acf_admin();
		if ( $deny ) {
			return $deny;
		}
		$key   = \sanitize_text_field( $params['group_key'] ?? '' );
		$group = \acf_get_field_group( $key );
		if ( ! $group ) {
			return new \WP_Error( 'group_not_found', 'Field group not found', array( 'status' => 404 ) );
		}
		$fields = \acf_get_fields( $key );
		$flist  = array();
		foreach ( (array) $fields as $f ) {
			$flist[] = array(
				'key'   => $f['key'] ?? '',
				'label' => $f['label'] ?? '',
				'name'  => $f['name'] ?? '',
				'type'  => $f['type'] ?? '',
			);
		}
		return $this->success_response(
			array(
				'key'    => $group['key'] ?? '',
				'title'  => $group['title'] ?? '',
				'active' => (bool) ( $group['active'] ?? true ),
				'fields' => $flist,
			)
		);
	}

	/**
	 * Tool handler for `acf.listFields`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_listFields( $params ) {
		$deny = $this->require_acf_admin();
		if ( $deny ) {
			return $deny;
		}
		$key    = \sanitize_text_field( $params['group_key'] ?? '' );
		$fields = \acf_get_fields( $key );
		if ( false === $fields ) {
			return new \WP_Error( 'group_not_found', 'Field group not found', array( 'status' => 404 ) );
		}
		$out = array();
		foreach ( (array) $fields as $f ) {
			$out[] = array(
				'key'      => $f['key'] ?? '',
				'label'    => $f['label'] ?? '',
				'name'     => $f['name'] ?? '',
				'type'     => $f['type'] ?? '',
				'required' => (bool) ( $f['required'] ?? false ),
			);
		}
		return $this->success_response(
			array(
				'fields' => $out,
				'count'  => count( $out ),
			)
		);
	}

	/**
	 * Tool handler for `acf.getFieldValue`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getFieldValue( $params ) {
		$deny = $this->require_cap( 'edit_posts' );
		if ( $deny ) {
			return $deny;
		}
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_read_post( $id );
		if ( $deny ) {
			return $deny;
		}
		$selector = \sanitize_text_field( $params['selector'] ?? '' );
		return $this->success_response(
			array(
				'post_id'  => $id,
				'selector' => $selector,
				'value'    => \get_field( $selector, $id ),
			)
		);
	}

	/**
	 * Tool handler for `acf.getFieldsForPost`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getFieldsForPost( $params ) {
		$deny = $this->require_cap( 'edit_posts' );
		if ( $deny ) {
			return $deny;
		}
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_read_post( $id );
		if ( $deny ) {
			return $deny;
		}
		$fields = \get_fields( $id );
		return $this->success_response(
			array(
				'post_id' => $id,
				'fields'  => is_array( $fields ) ? $fields : array(),
			)
		);
	}

	/**
	 * Tool handler for `acf.getFieldObject`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getFieldObject( $params ) {
		$deny = $this->require_cap( 'edit_posts' );
		if ( $deny ) {
			return $deny;
		}
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_read_post( $id );
		if ( $deny ) {
			return $deny;
		}
		$selector = \sanitize_text_field( $params['selector'] ?? '' );
		$obj      = \get_field_object( $selector, $id );
		if ( ! $obj ) {
			return new \WP_Error( 'field_not_found', 'Field not found', array( 'status' => 404 ) );
		}
		return $this->success_response(
			array(
				'post_id' => $id,
				'key'     => $obj['key'] ?? '',
				'name'    => $obj['name'] ?? '',
				'label'   => $obj['label'] ?? '',
				'type'    => $obj['type'] ?? '',
				'value'   => $obj['value'] ?? null,
			)
		);
	}

	/**
	 * Tool handler for `acf.searchByFieldValue`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_searchByFieldValue( $params ) {
		$deny = $this->require_cap( 'edit_posts' );
		if ( $deny ) {
			return $deny;
		}
		$field = \sanitize_text_field( $params['field_name'] ?? '' );
		$value = $params['value'] ?? '';
		$limit = $this->limit_param( $params, 20 );
		$out   = array();
		$page  = 1;
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
				$out[] = array(
					'post_id' => (int) $p->ID,
					'title'   => \get_the_title( $p ),
					'type'    => $p->post_type,
				);
				if ( count( $out ) >= $limit ) {
					break 2;
				}
			}
			++$page;
			$fetched = count( $posts );
		} while ( $fetched === $limit && $page <= 20 );
		return $this->success_response(
			array(
				'results' => $out,
				'count'   => count( $out ),
			)
		);
	}
}
