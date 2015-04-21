<?php


register_activation_hook( __FILE__, 'line_emotic__install' );
register_activation_hook( __FILE__, 'line_emotic_insert_data' );

//$_POST['post_id'] = 20;
//$_POST['position'] = 1;
if(isset($_POST['post_id']) && isset($_POST['position']) ){

	line_emotic_install();
	line_emotic_insert_data($_POST['post_id'], $_POST['position']);
}


function line_emotic_install () {
    global $wpdb;

    $table_name = $wpdb->prefix . "line_emotic"; 

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
	  id mediumint(9) NOT NULL ,
	  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  column1 varchar(7) DEFAULT '' NOT NULL,
	  column2 varchar(7) DEFAULT '' NOT NULL,
	  column3 varchar(7) DEFAULT '' NOT NULL,
	  column4 varchar(7) DEFAULT '' NOT NULL,
	  column5 varchar(7) DEFAULT '' NOT NULL,
	  UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );																							

	add_option( "jal_db_version", "1.0" );
}

function line_emotic_insert_data($postid , $position){
	global $wpdb;
	
	$table_name = $wpdb->prefix . "line_emotic";

	$meta_key = $postid;
	$result = $wpdb->get_var( $wpdb->prepare( 
		"
			SELECT column1, column2, column3, column4, column5
			FROM $table_name
			WHERE id = %s
		", 
		$meta_key
	) );

	var_dump($result); exit;

	$wpdb->insert( 
		$table_name, 
		array( 
			'time' => current_time( 'mysql' ), 
			'name' => $welcome_name, 
			'text' => $welcome_text, 
		) 
	);
}

?>