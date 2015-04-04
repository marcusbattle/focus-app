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

		// Ajax
		add_action( 'wp_ajax_submit_task_form', array( $this, 'submit_task_form' ) );
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

}

$f_o_c_u_s = new FOCUS_App();