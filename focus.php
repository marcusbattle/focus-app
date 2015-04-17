<?php

/*
Plugin Name: F O C U S App
Plugin URI: http://marcusbattl.com
Description: 
Author: Marcus Battle
Version: 0.1.0
Author URI: http://marcusbattle.com
*/

class FOCUS_App {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts_and_styles' ) );
		add_action( 'init', array( $this, 'create_post_types' ) );
		add_action( 'init', array( $this, 'create_taxonomies' ), 0 );
		add_action( 'wp_footer', array( $this, 'create_add_task_view' ) );	

		add_action( 'pre_get_posts', array( $this, 'sort_tasks_by_due_date' ) );

		// Ajax
		add_action( 'wp_ajax_submit_task_form', array( $this, 'submit_task_form' ) );
		add_action( 'wp_ajax_complete_task', array( $this, 'complete_task' ) );
		add_action( 'wp_ajax_delete_task', array( $this, 'delete_task' ) );
		add_action( 'wp_ajax_add_note', array( $this, 'add_note' ) );

	}

	public function scripts_and_styles() {
		wp_enqueue_style( 'focus', plugin_dir_url( __FILE__ ) . 'assets/css/focus.css' );
		wp_enqueue_style( 'fintawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );

		wp_enqueue_script( 'focus', plugin_dir_url( __FILE__ ) . 'assets/js/focus.js', array('jquery'), '1.0.0', true );
		wp_localize_script( 'focus', 'focus',
            array( 
            	'ajax_url' => admin_url( 'admin-ajax.php' )
            )
        );

	}

	public function create_post_types() {

		// Generate the 'task' custom post type
		$labels = array(
			'name'               => _x( 'Tasks', 'post type general name', 'focus-app' ),
			'singular_name'      => _x( 'Task', 'post type singular name', 'focus-app' ),
			'menu_name'          => _x( 'Tasks', 'admin menu', 'focus-app' ),
			'name_admin_bar'     => _x( 'Task', 'add new on admin bar', 'focus-app' ),
			'add_new'            => _x( 'Add New', 'task', 'focus-app' ),
			'add_new_item'       => __( 'Add New Task', 'focus-app' ),
			'new_item'           => __( 'New Task', 'focus-app' ),
			'edit_item'          => __( 'Edit Task', 'focus-app' ),
			'view_item'          => __( 'View Task', 'focus-app' ),
			'all_items'          => __( 'All Tasks', 'focus-app' ),
			'search_items'       => __( 'Search Tasks', 'focus-app' ),
			'parent_item_colon'  => __( 'Parent Tasks:', 'focus-app' ),
			'not_found'          => __( 'No tasks found.', 'focus-app' ),
			'not_found_in_trash' => __( 'No tasks found in Trash.', 'focus-app' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'tasks' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'author', 'comments' )
		);

		register_post_type( 'task', $args );

	}

	public function create_taxonomies() {

		$labels = array(
			'name'              => _x( 'Projects', 'taxonomy general name' ),
			'singular_name'     => _x( 'Project', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Projects' ),
			'all_items'         => __( 'All Projects' ),
			'parent_item'       => __( 'Parent Project' ),
			'parent_item_colon' => __( 'Parent Project:' ),
			'edit_item'         => __( 'Edit Project' ),
			'update_item'       => __( 'Update Project' ),
			'add_new_item'      => __( 'Add New Project' ),
			'new_item_name'     => __( 'New Project Name' ),
			'menu_name'         => __( 'Project' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'project' ),
		);

		register_taxonomy( 'project', array( 'task' ), $args );

	}

	public function create_add_task_view() {
		
		if ( is_post_type_archive('task') ) {

			ob_start();
			include plugin_dir_path( __FILE__ ) . 'views/add-task.php';
			$add_task_view = ob_get_contents();
			ob_end_clean();

			echo $add_task_view;

		}

	}

	public function submit_task_form() {

		if ( ! empty( $_POST ) && isset( $_POST['action'] ) && ( $_POST['action'] == 'submit_task_form' ) ) {
			
			unset( $_POST['action'] );

			$task = isset( $_POST['task'] ) ? $_POST['task'] : '';
			$due_date = isset( $_POST['task_due_date'] ) ? strtotime( $_POST['task_due_date'] ) : strtotime();
			$project = isset( $_POST['task_project'] ) ? $_POST['task_project'] : '';

			if ( empty( $task ) ) {
				// return error
			}

			// Create post object
			$task = array(
				'post_title'    => $task,
				'post_content'  => '',
				'post_status'   => 'publish',
				'post_type'	  => 'task'
			);

			// Insert the post into the database
			$task_id = wp_insert_post( $task );

			if ( $task_id ) {

				wp_set_object_terms( $task_id, $project, 'project', false );
				update_post_meta( $task_id, 'due_date', $due_date );

				echo json_encode( array( 'success' => true, 'message' => 'Task added!' ) );

			}

			exit;

		}

	}

	/**
	 * Makes sure the tasks sort in ASC order by due date
	 */
	public function sort_tasks_by_due_date( $query ) {

		if ( is_post_type_archive('task') && $query->is_main_query() ) {
			
			$query->set('posts_per_page', -1 );
			$query->set('meta_key', 'due_date');
			$query->set('orderby', array( 'meta_value_num' => 'ASC' ) );

		}

		return $query;

	}

	public function complete_task() {

		if ( ! empty( $_POST ) && isset( $_POST['action'] ) && ( $_POST['action'] == 'complete_task' ) ) {
			
			$task_id = isset( $_POST['task_id'] ) ? $_POST['task_id'] : 0;

			if ( ! $task_id ) {
				// Report error message
				echo json_encode( array( 'success' => false ) );
				exit;
			}
			
			$task_status = get_post_meta( $task_id, 'status', true );

			if ( $task_status ) {
				update_post_meta( $task_id, 'status', '' );
			} else {
				update_post_meta( $task_id, 'status', 'complete' );
			}

			$task_status = get_post_meta( $task_id, 'status', true );

			echo json_encode( array( 
				'success' => true,
				'status' => $task_status
			) );

			exit;

		}

	}

	public function delete_task() {

		if ( ! empty( $_POST ) && isset( $_POST['action'] ) && ( $_POST['action'] == 'delete_task' ) ) {
			
			$task_id = isset( $_POST['task_id'] ) ? $_POST['task_id'] : 0;

			if ( ! $task_id ) {
				// Report error message
			}

			$task_deleted = wp_delete_post( $task_id ); 

			if ( $task_deleted ) {
				
				echo json_encode( array( 
					'success' => true
				) );

			}

			exit;

		}

	}

	public function add_note() {

		if ( ! empty( $_POST ) && isset( $_POST['action'] ) && ( $_POST['action'] == 'add_note' ) ) {
			
			$task_id = isset( $_POST['task_id'] ) ? $_POST['task_id'] : 0;
			$new_note = isset( $_POST['new_note'] ) ? $_POST['new_note'] : '';

			if ( ! $task_id || ! $new_note ) {
				// Report error message
				echo json_encode( array( 
					'success' => false
				) );
				
				exit;

			}

			$current_user = wp_get_current_user();

			$time = current_time('mysql');

			$data = array(
			    'comment_post_ID' => $task_id,
			    'comment_author' => $current_user->display_name,
			    'comment_author_email' => $current_user->user_email,
			    'comment_author_url' => 'http://',
			    'comment_content' => $new_note,
			    'comment_type' => '',
			    'comment_parent' => 0,
			    'user_id' => $current_user->ID,
			    'comment_date' => $time,
			    'comment_approved' => 1,
			);

			$note_inserted = wp_insert_comment($data);

			if ( ! is_wp_error( $note_inserted ) ) {

				echo json_encode( array( 
					'success' => true
				) );

			}

			exit;

		}

	}

}

$f_o_c_u_s = new FOCUS_App();