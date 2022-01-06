<?php 
/*
Plugin Name: Action Guide Checklist
Description: This plugin provides the ability to add todo list to posts / pages / widgets. Use the shortcode [mytodolist] to include it in your posts. You can also include three attributes: (1) 'time' to show with clock, (2) 'effect' to show how beneficial task is and (3) 'items' is the number of items in todo list. You can use it like this: [mytodolist time="Complete in <1 hour" effect="Increase sales by 20%" items=3]
Author: Anthony Hayes
Author URI: https://leadblasta.com
Version: 1.0.0
*/
add_shortcode('mytodolist', 'my_custom_todo_list_code_MB');
function my_custom_todo_list_code_MB($atts){
	global $post;
setup_postdata( $post );
	$data = "<script>
	var postID = '" .  get_the_ID() . "';
	var userID = '" .  get_current_user_id() . "';
	</script>";
	$data .= '<div class="metrics">';
	if(isset($atts["time"])){
          $data .= '<div class="metric-wrapper">
            <img src="' . plugin_dir_url( __FILE__ )  . 'images/clock.svg" alt="Clock icon">
            <div class="metric">' . $atts["time"] . '</div>
          </div>';
	}
	if(isset($atts["effect"])){
          $data .= '<div class="metric-wrapper">
            <img src="' . plugin_dir_url( __FILE__ )  . 'images/up.svg" alt="Up icon">
            <div class="metric">' . $atts["effect"] . '</div>
          </div>';
	}
	if(isset($atts["items"])){
          $data .= data_getter_todo($atts["items"]);
	}else{
		$data .= data_getter_todo(5);
	}
	$data .=   '</div>';

    return $data;
}

// Including Bootstrap 4
add_action('wp_enqueue_scripts', 'wp_enqueue_myfiles');
function wp_enqueue_myfiles() {
    wp_enqueue_style( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' );
    wp_enqueue_script( 'boot3','//maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array( 'jquery' ),'',true );
	
	wp_enqueue_style('main-styles', plugins_url( 'css/style.css' , __FILE__ ), array(), rand(), false);
    wp_enqueue_style('todostyler');
    wp_register_style('fontawesome', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css", array(), '5.13.0', 'all');
    wp_enqueue_style('fontawesome');
    wp_enqueue_script( 'frontend-ajax', plugins_url( 'js/demo.js?x=' . rand(), __FILE__ ), array('jquery'), null, true );
    wp_localize_script( 'frontend-ajax', 'frontend_ajax_object',
        array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))
    	);
}

// Retrieve Todo Information
add_action( 'wp_ajax_data_getter_todo', 'data_getter_todo' );
function data_getter_todo($nitems){
		global $post;
setup_postdata( $post );
    global $wpdb;
	
	$user = wp_get_current_user();
	$roles = ( array ) $user->roles;
	$role = $roles[0];
	
	$pid = $_POST['pid'];
	$postID = get_the_ID() ;
	$userID = get_current_user_id() ;
	$table1 = $wpdb->prefix . "todo_list_items_status";
    $table2 = $wpdb->prefix . "todo_list_items";

	$query = 'SELECT * FROM '.$table1.' t1 JOIN '.$table2.' t2 ON t1.todo_id=t2.id where post_id=' . $postID . ' AND user_id='. $userID;
		$rows = $wpdb->get_results($query);
		
		if($rows) {
			echo "<script>";
			foreach($rows as $row) {				
				if($row->todo_status=='true') {
					echo "	document.getElementById('".$row->todo_id."').checked = true;
							jQuery('#".$row->todo_id."').parent().parent().parent().addClass('completed');
							jQuery('#".$row->todo_id."').parent().parent().parent().find('.status').html('Completed');	
						  ";
				}
			}
			echo "</script>";
		}
	
	
	
	
	$query = 'SELECT * FROM '.$table2.' where post_id=' . $postID . ' ORDER BY id ASC';
	//echo $query;
	$rows = $wpdb->get_results($query);

	if ( $rows ) {
	   $list= json_encode($rows, JSON_FORCE_OBJECT );
		
		$list = '
		
		<section class="action-items" style="width: 100%;">
    <h2 class="card-title">To-do list</h2>
    
    <div class="progress">
      <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
        <span class="sr-only">0% Complete</span>
      </div>
    </div>
    <div>
      <ul class="to-do-list">';
		
		foreach($rows as $key => $row) {
    		//echo  $row->post_id ;
			//echo  $row->todo_content ;
		$list .= '
        <li class="to-do-item  d-flex align-items-center">
          <div>
            <label class="container"><input type="checkbox" onclick="checkMark(this)" id="'.$row->id.'" data-id="' . $row->id . '">
			<span class="checkmark" style="margin-top: 12px"></span></label>
          </div>
          <p class="text">' . $row->todo_content . '</p>
          <!-- Status Indicators -->
          <p class="status">Pending</p>
          <!-- Replaces p.status node on hover if item is not completed -->
          <p class="status-on-hover">Mark</p>
        </li>';
		}

		$list .= '</ul>
    </div>
      
    </div>
  </section>';
		
		
    } else {
		if($role == 'administrator') {
			$list .= "<p style='width:100%;padding:20px 5px 5px 5px' id='todoform'>Enter Todo List Items: <br>";
			for ($x = 1; $x <= $nitems; $x++) {
  			$list .= "<input type='text' class='form-control' placeholder='Todo Content' name='todo-" . $x . "' id='todo-" . $x . "'  /><br>";
			}
		$list .= "<button data-post-id='" . $postID . "' data-user-id='". $userID ."' class='btn btn-primary' id='todo-form-btn'>
		Add Todo List</button></p>";	
		}
		else {
			$list = "";
		}

	}
	return $list;
}

// Add Todo Content
add_action( 'wp_ajax_todo_data_handler', 'todo_data_handler' );
function todo_data_handler(){
    global $wpdb;
    $count = 0;
    $table = $wpdb->prefix . "todo_list_items";
	
	

	

    $post_id = $_POST['post-id'];
    $user_id = get_current_user_id();
	
	foreach($_POST as $key => $value) {
    	if (strpos($key, 'todo-') === 0) {
        	$wpdb->replace($table, array(
   			"post_id" => $post_id,
			"todo_content" => $value
			));
    	}
	}
	
    $content .= 'Todo Data added successfully.' ; 
	return  $content;
	die();
}



// Todo Status Changer
// add_action('wp_ajax_nopriv_ajaxlogin','ajax_login');
add_action( 'wp_ajax_change_status', 'change_status' );
function change_status() {
    global $wpdb;
	$table = $wpdb->prefix . "todo_list_items_status";

    $id = $_POST['id'];
    $status = $_POST['status'];
	echo $wpdb->delete( $table, array(   'todo_id' => $id,
									"user_id" => get_current_user_id()
								) );
	$wpdb->replace($table, array(
   "todo_id" => $id,
	"user_id" => get_current_user_id(),
   "todo_status" => $status
	));

	echo $wpdb->insert_id . " " . $status;
	die();
}



// Check initial table 
function SMART_AMR_Table_Check(){
    global $wpdb;
    
    $my_products_db_version = '1.0.0';
    $charset_collate = $wpdb->get_charset_collate();
        
    $table_name = $wpdb->prefix . "todo_list_items";
    if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name ) {
        $sql = "CREATE TABLE  $table_name ( 
            `id`  int NOT NULL AUTO_INCREMENT,
            `post_id`  varchar(256)   NOT NULL,
            `todo_content`  varchar(256)   NOT NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        add_option('my_db_version', $my_products_db_version);
        }

    $table_name = $wpdb->prefix . "todo_list_items_status";
    if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name ) {
        $sql = "CREATE TABLE  $table_name ( 
            `id`  int NOT NULL AUTO_INCREMENT,
            `todo_id`  varchar(256)   NOT NULL,
            `user_id`  varchar(256)   NOT NULL,
            `todo_status`  varchar(256)   NOT NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        add_option('my_db_version', $my_products_db_version);
    }
}

register_activation_hook( __FILE__, 'SMART_AMR_Table_Check' );

?>