<?php
/**
 * AdhocGroup class.
 *
 * @since 1.0.0
 * @package ubc-h5p-adhoc-group
 */

namespace UBC\H5P\AdhocGroup;

/**
 * Class to initiate Adhoc Group functionalities
 */
class AdhocGroup {

	/**
	 * Taxonomy name.
	 *
	 * @since 2.5.6
	 *
	 * @var String $tax
	 */
	private $tax = 'ubc_h5p_content_group';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'create_taxonomy' ) );
		add_action( 'admin_menu', array( $this, 'create_taxonomy_menus' ), 51 );
		add_filter( 'manage_edit-ubc_h5p_content_group_columns', array( $this, 'manage_group_admin_user_column' ) );
		add_filter( 'manage_ubc_h5p_content_group_custom_column', array( $this, 'manage_group_admin_user_count_column' ), 10, 3 );
		add_action( 'ubc_h5p_content_group_edit_form', array( $this, 'add_user_form' ), 10, 2 );

		add_action( 'wp_ajax_ubc_h5p_add_user_to_group', array( $this, 'add_user_to_group' ) );
		add_action( 'wp_ajax_ubc_h5p_delete_user_from_group', array( $this, 'delete_user_from_group' ) );

		add_action( 'load-h5p-content_page_h5p_new', array( $this, 'enqueue_add_new_content_script' ), 11 );
		add_action( 'ubc_h5p_content_taxonomy_save_content', array( $this, 'h5p_content_save' ), 10, 1 );

		add_action( 'toplevel_page_h5p', array( $this, 'enqueue_listing_view_script' ), 99 );

		add_filter( 'h5p_content_taxonomy_context_query', array( $this, 'query_data_context_query' ), 10, 2 );
		add_filter( 'h5p_content_taxonomy_terms', array( $this, 'query_data_term_ids' ), 10, 2 );
	}

	/**
	 * Create and attach group taxonomy on users.
	 *
	 * @return void
	 */
	public function create_taxonomy() {

		register_taxonomy(
			$this->tax,
			array( 'user' ),
			array(
				'label'        => __( 'Group', 'ubc-h5p-adhoc-group' ),
				'public'       => true,
				'rewrite'      => false,
				'hierarchical' => true,
				'capabilities' => array( 'edit_others_h5p_contents' ),
			)
		);

	}//end create_taxonomy()

	/**
	 * Create submenus for custom taxonomies we created in create_post_type_and_taxonomies() and put them under H5P plugin main menu.
	 *
	 * @return void
	 */
	public function create_taxonomy_menus() {

		// Editors are able to access Groups.
		add_submenu_page(
			'h5p',
			__( 'Group', 'ubc-h5p-taxonomy' ),
			__( 'Group', 'ubc-h5p-taxonomy' ),
			'edit_others_h5p_contents',
			'edit-tags.php?taxonomy=ubc_h5p_content_group'
		);

	}//end create_taxonomy_menus()

	/**
	 * Unset the post column and add a 'user' column in the group admin page.
	 *
	 * @param array $columns Existing admin columns on group admin page.
	 * @return array
	 */
	public function manage_group_admin_user_column( $columns ) {
		unset( $columns['posts'] );
		$columns['users'] = __( 'Users' );
		return $columns;
	}//end manage_group_admin_user_column()

	/**
	 * Add the user count column.
	 *
	 * @param string $display WP just passes an empty string here.
	 * @param string $column The name of the custom column.
	 * @param int    $term_id The ID of the term being displayed in the table.
	 */
	public function manage_group_admin_user_count_column( $display, $column, $term_id ) {
		if ( 'users' === $column ) {
			$term = get_term( $term_id, $this->tax );
			echo '<a href="term.php?taxonomy=ubc_h5p_content_group&tag_ID=' . (int) $term_id . '">' . (int) $term->count . '</a>';
		}
	}

	/**
	 * Create add user form.
	 *
	 * @param WP_Term $tag      Current taxonomy term object.
	 * @param string  $taxonomy Current taxonomy slug.
	 */
	public function add_user_form( $tag, $taxonomy ) {
		global $taxnow;

		if ( ! $taxnow || empty( $_GET['tag_ID'] ) ) {
			return null;
		}

		if ( ! \UBC\H5P\Taxonomy\ContentTaxonomy\Helper::is_role_editor() && ! \UBC\H5P\Taxonomy\ContentTaxonomy\Helper::is_role_administrator() ) {
			return;
		}

		$term_id = absint( $_GET['tag_ID'] );

		$users = get_objects_in_term( $term_id, $this->tax );
		if ( ! is_wp_error( $users ) ) {
			$users = array_map(
				function( $user ) {
					return get_user_by( 'ID', $user );
				},
				$users
			);
		}

		// Enqueue script and style.
		wp_enqueue_script(
			'ubc-h5p-group-term-edit-js',
			H5P_ADHOCGROUP_PLUGIN_URL . 'assets/dist/js/term_edit.js',
			array(),
			filemtime( H5P_ADHOCGROUP_PLUGIN_DIR . 'assets/dist/js/term_edit.js' ),
			true
		);

		wp_localize_script(
			'ubc-h5p-group-term-edit-js',
			'ubc_h5p_adhocgroup',
			array(
				'security_nonce' => wp_create_nonce( 'security' ),
				'term_id'        => $term_id,
			)
		);

		wp_register_style(
			'ubc-h5p-group-term-edit-css',
			H5P_ADHOCGROUP_PLUGIN_URL . '/assets/dist/css/term_edit.css',
			array(),
			filemtime( H5P_ADHOCGROUP_PLUGIN_DIR . 'assets/dist/css/term_edit.css' )
		);
		wp_enqueue_style( 'ubc-h5p-group-term-edit-css' );
		// End enqueue script and style.
		?>
			<hr>
			<table class="form-table" role="presentation">
				<tbody>
					<tr class="form-field">
						<th scope="row"><label for="add_user_email">Add User</label></th>
						<td>
							<div class="add-user-wapper">
								<input name="add_user_email" id="user-email" type="text">
								<button id="add_user" style="min-width: 80px;" class="button button-primary">Add</button>
							</div>
							<p id="message"></p>
							<p class="description">Please enter the email address attached to the user account in order to add user to the group.</p>
						</td>
					</tr>
				</tbody>
			</table>

			<table class="form-table" role="presentation">
				<tbody>
					<tr class="form-field">
						<th scope="row"><label for="current_users">Current Users</label></th>
						<td>
							<table class="list-user-table">
								<tr>
									<th class="manage-column column-primary">Username</th>
									<th>Email</th>
									<th class="table-action">Action</th>
								</tr>
								<?php foreach ( $users as $key => $user ) : ?>
									<tr>
										<td><?php echo esc_textarea( $user->data->user_login ); ?></td>
										<td><?php echo esc_textarea( $user->data->user_email ); ?></td>
										<td><button class="delete_user" user_id="<?php echo (int) $user->ID; ?>">X</button></td>
									</tr>
								<?php endforeach; ?>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		<?php
	}

	/**
	 * Ajax handler to add user to a group.
	 *
	 * @return void
	 */
	public function add_user_to_group() {
		check_ajax_referer( 'security', 'nonce' );

		if ( ! \UBC\H5P\Taxonomy\ContentTaxonomy\Helper::is_role_editor() && ! \UBC\H5P\Taxonomy\ContentTaxonomy\Helper::is_role_administrator() ) {
			return;
		}

		$email   = isset( $_POST['user_email'] ) ? sanitize_text_field( wp_unslash( $_POST['user_email'] ) ) : '';
		$term_id = isset( $_POST['term_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['term_id'] ) ) : '';

		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			wp_send_json_success(
				array(
					'status'  => 'invalid',
					'message' => $email . ' is not attached to a user on this platform. No changes made.',
				)
			);
		}

		$user_terms = wp_get_object_terms( $user->ID, $this->tax );
		$term       = get_term_by( 'ID', $term_id, $this->tax );

		if ( ! is_array( $user_terms ) ) {
			return;
		}

		if ( ! in_array( $term, $user_terms ) ) {
			// Add user to the group.
			$result = wp_set_post_terms( $user->ID, array( $term_id ), $this->tax, true );
			if ( ! is_wp_error( $result ) ) {
				wp_send_json_success(
					array(
						'status'  => 'valid',
						'message' => $email . ' has been successfully added to the group.',
					)
				);
			} else {
				wp_send_json_success(
					array(
						'status'  => 'invalid',
						'message' => $email . ' is failed to add to the group. Please contact lt.hub@ubc.ca.',
					)
				);
			}
		} else {
			wp_send_json_success(
				array(
					'status'  => 'invalid',
					'message' => $email . ' is already attached to current group.',
				)
			);
		}
	}

	/**
	 * Ajax handler to remove a user from a group.
	 *
	 * @return void
	 */
	public function delete_user_from_group() {
		check_ajax_referer( 'security', 'nonce' );

		if ( \UBC\H5P\Taxonomy\ContentTaxonomy\Helper::is_role_editor() ) {
			return;
		}

		$user_id = isset( $_POST['user_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : '';
		$term_id = isset( $_POST['term_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['term_id'] ) ) : '';

		wp_remove_object_terms( $user_id, $term_id, $this->tax );
	}

	/**
	 * Load assets for h5p new content page.
	 *
	 * @return void
	 */
	public function enqueue_add_new_content_script() {

		$user_terms = wp_get_object_terms( get_current_user_id(), $this->tax );

		wp_enqueue_script(
			'ubc-h5p-group-edit-js',
			H5P_ADHOCGROUP_PLUGIN_URL . 'assets/dist/js/h5p-new.js',
			array(),
			filemtime( H5P_ADHOCGROUP_PLUGIN_DIR . 'assets/dist/js/h5p-new.js' ),
			true
		);

		wp_localize_script(
			'ubc-h5p-group-edit-js',
			'ubc_h5p_group',
			array(
				'id'            => isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null,
				// Administrator and editor should have access to all the groups when editing the h5p content. Where author only have access to their own group.
				'user_group'    => current_user_can( 'edit_others_h5p_contents' ) ? \UBC\H5P\Taxonomy\ContentTaxonomy\Helper::get_taxonomy_hierarchy( $this->tax ) : $user_terms,
				'content_group' => isset( $_GET['id'] ) ? \UBC\H5P\Taxonomy\ContentTaxonomy\ContentTaxonomyDB::get_content_terms_by_taxonomy( intval( $_GET['id'] ), 'group' ) : null,
			)
		);
	}//end enqueue_add_new_content_script()

	/**
	 * Save terms upon content save.
	 *
	 * @param int $id The ID of the h5p content.
	 * @return void
	 */
	public function h5p_content_save( $id ) {
		// phpcs:ignore
		$tax = json_decode( html_entity_decode( stripslashes( $_REQUEST['ubc-h5p-content-taxonomy-group'] ) ) );

		if ( false !== $tax && is_array( $tax->group ) ) {
			// Remove all the rows attached to current H5P content before add new ones.
			\UBC\H5P\Taxonomy\ContentTaxonomy\ContentTaxonomyDB::clear_content_terms_by_type( $id, 'group' );

			foreach ( $tax->group as $key => $group ) {
				\UBC\H5P\Taxonomy\ContentTaxonomy\ContentTaxonomyDB::insert_content_term( $id, intval( $group ), 'group' );
			}
		}
	}


	/**
	 * Enqueue necessary Javascript for listing view.
	 *
	 * @return void
	 */
	public function enqueue_listing_view_script() {
		if ( ! \UBC\H5P\Taxonomy\ContentTaxonomy\Helper::is_h5p_list_view_page() || \UBC\H5P\Taxonomy\ContentTaxonomy\Helper::is_role_administrator() ) {
			return;
		}

		wp_enqueue_script(
			'ubc-h5p-group-listing-view-js',
			H5P_ADHOCGROUP_PLUGIN_URL . 'assets/dist/js/h5p-listing-view.js',
			array(),
			filemtime( H5P_ADHOCGROUP_PLUGIN_DIR . 'assets/dist/js/h5p-listing-view.js' ),
			true
		);

		$user_terms = wp_get_object_terms( get_current_user_id(), $this->tax );

		if ( ! is_array( $user_terms ) ) {
			return;
		}

		wp_localize_script(
			'ubc-h5p-group-listing-view-js',
			'ubc_h5p_adhocgroup',
			array(
				'can_user_editor_others' => current_user_can( 'edit_others_h5p_contents' ),
				'user_groups'            => $user_terms,
			)
		);

		wp_register_style(
			'ubc-h5p-group-listing-view-css',
			H5P_ADHOCGROUP_PLUGIN_URL . '/assets/dist/css/h5p-listing-view.css',
			array(),
			filemtime( H5P_ADHOCGROUP_PLUGIN_DIR . 'assets/dist/css/h5p-listing-view.css' )
		);
		wp_enqueue_style( 'ubc-h5p-group-listing-view-css' );
	}//end enqueue_listing_view_script()

	/**
	 * Filter the context query section of the main content listing/filtering query.
	 *
	 * @param string $query context part of the main listing query.
	 * @param string $context current context from the request.
	 * @return string
	 */
	public function query_data_context_query( $query, $context ) {
		if ( 'group' !== $context ) {
			return $query;
		}

		$user_groups     = wp_get_object_terms( get_current_user_id(), $this->tax );
		$user_groups_ids = array_map(
			function( $group ) {
				return $group->term_id;
			},
			$user_groups
		);

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		return ' WHERE hc.id IN (SELECT DISTINCT content_id from ' . $wpdb->prefix . 'h5p_contents_taxonomy WHERE term_id IN (' . implode( ',', $user_groups_ids ) . ')) AND u.ID != ' . get_current_user_id();
	}

	/**
	 * Override the terms IDs that will used to filter the listing query content result.
	 *
	 * @param array  $term_ids h5p_taxonomy term ids to filter the listing query result.
	 * @param string $context current context from the request.
	 * @return string
	 */
	public function query_data_term_ids( $term_ids, $context ) {
		// phpcs:ignore
		if ( 'group' === $context && isset( $_POST['group'] ) && is_numeric( $_POST['group'] ) ) {
			// phpcs:ignore
			$group_id = (int) $_POST['group'];

			array_push( $term_ids, $group_id );
		}
		return $term_ids;
	}
}

new AdhocGroup();

