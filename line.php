<?php
/**
 * Plugin Name: Line Emotic
 * Plugin URI: http://www.momokoro.com
 * Description: Display Line's sticker as emoticon
 * Version: 1.0.0
 * Author: Chin Chayut
 * Author URI: http://www.momokoro.com
 * Text Domain: Optional. Plugin's text domain for localization. Example: mytextdomain
 * Domain Path: Optional. Plugin's relative directory path to .mo files. Example: /locale/
 * Network: Optional. Whether the plugin can only be activated network wide. Example: true
 * License: GPL2
 */
add_action( 'wp_enqueue_scripts', 'line_emotic_admin_scripts' );
add_action( 'admin_menu', 'line_emotic_menu');
add_action( 'admin_init', 'line_emotic_settings' );

add_filter( 'the_content', 'line_emotic_content_filter' );

register_activation_hook( __FILE__, 'line_emotic__install' );
register_activation_hook( __FILE__, 'line_emotic_insert_data' );
register_activation_hook( __FILE__, 'line_emotic_get_data' );

add_action( 'plugins_loaded', 'myplugin_update_db_check' );

$max_num_line = 5;
$error_line = "";
$jal_db_version = 1.0;
$default_text_line = "Express your feeling! ";

function line_emotic_admin_scripts() {
    /* Link our already registered script to a page */

	  //wp_register_script( 'jquery', '//code.jquery.com/jquery-1.11.2.min.js', array(), '1.0.0', true );
    wp_register_script( 'jquery', plugins_url() . '/line-emotic/js/jquery-1.11.2.min.js', array(), '1.0.0', true );
    wp_enqueue_script( "jquery");

    wp_register_script( 'script-line', plugins_url() . '/line-emotic/js/script.js', array(), '1.0.0', true );
    wp_enqueue_script( "script-line");

    wp_register_style( 'line-emotic-Stylesheet', plugins_url() . '/line-emotic/css/line_emotic_style.css' );
    wp_enqueue_style( 'line-emotic-Stylesheet');
}

function line_emotic_content_filter($content) {
  // Add Line Emotic 
  global $max_num_line;

	$uploaddir = plugins_url() . '/line-emotic/images/';
  $added = '<table class="line-table"><tr>';
  $added.= '<td colspan="4" ><span style="font-weight:bold;" >'.esc_attr( get_option('line_text') ).'</span></td>';
  $added.= '</tr><tr>';
  
  $result_percent = line_emotic_get_data(get_the_ID());
  $sum = 1;
  if(sizeof($result_percent) > 0){
    foreach ($result_percent as $key => $value) {
      $sum += $value;
    }
  }

  for ($i=1; $i <= $max_num_line; $i++) { 
    $added .= '<td><div class="plus" id="plus'.$i.'">+1</div><div class="line-clickable" onclick="setvote('.get_the_ID().','.$i.')" >';
    $added .= '<img id="pic-'.$i.'" class="imgicon" src="'.$uploaddir . esc_attr( get_option('line_'.$i)) . '.png" />';
    $added .= '<div class="txt-line" id="txtline_'.$i.'" > ';
    $added .=  isset($result_percent['column'.$i])?number_format($result_percent['column'.$i]*100/$sum, 0, '.', '').'%':0;
    $added .= ' </div>';
    $added .= "</div></td>";
  }
  $added .= "</tr></table>";

  $script_ext = "<script>";
  $script_ext.= 'var admin_ajax = "http://localhost/wordpress/wp-admin/admin-ajax.php"; ';
  $script_ext.= "</script>";

  if(is_single()){
    if(  esc_attr( get_option('line_position') )=="top" ){
      return $added.$content.$script_ext;
    }else if( esc_attr( get_option('line_position') )=="buttom" ){
      return $content.$added.$script_ext;
    }else{
      // Default = top
      return $added.$content.$script_ext;
    }
  }

  // otherwise returns the database content
  return $content.$script_ext;
}

function line_emotic_settings() {

	line_emotic_admin_scripts();

	register_setting( 'line-emotic-settings-group', 'line_url' );
	register_setting( 'line-emotic-settings-group', 'line_name' );
  register_setting( 'line-emotic-settings-group', 'line_refresh' );
  register_setting( 'line-emotic-settings-group', 'line_position' );
  register_setting( 'line-emotic-settings-group', 'line_text' );

  global $max_num_line;
  global $error_line;

  // Get Sticker
  if( isset($_GET['settings-updated']) && $_GET['settings-updated'] == true){
  $url = esc_attr( get_option('line_url') );
    if($url != ""){
      if(  esc_attr( get_option('line_refresh') )=="refresh" ){
      $image = null;//@file_get_contents($url);
      if($image != null){ 
          coppyimage($url, $image);
        }
      }
    }else{
      $error_line .= "Please input StickerURL<br/>";
    }
  }

  // Specific each emoticon
  for ($i=1; $i <= $max_num_line; $i++) { 
    register_setting( 'line-emotic-settings-group', 'line_'.$i );
    if(esc_attr( get_option('line_'.$i) ) == ""){
      $error_line .= "Please input emo-".$i."<br/>";
    } 
  }


}


function coppyimage($url, $image){
	$width = 562;
	$height = 1500;
	$sliceimg = imagecreatefromstring($image);
  	for ($i=0; $i < 10; $i++) { 
  		for ($j=0; $j < 4; $j++) { 
		// Copy
  		$dest = imagecreatetruecolor($width/4-40, $height/10-40);
  		imagealphablending( $dest, false );
		imagesavealpha( $dest, true );

  			
		imagecopyresampled($dest, $sliceimg , 0, 0, ($j*($width/4))+20, ($i*($height/10))+20, 100, 110, 100, 110);
  		UploadPNG('emo-'.$i.'-'.$j, $dest );
  		}
  	}
}

function UploadPNG($imgname, $img)
{
	$uploaddir = plugin_dir_path( __FILE__ );
	$uploadfile = $uploaddir . '/' . 'images/' . $imgname .'.png';

	// start buffering
	ob_start();
	imagepng($img);
	$contents =  ob_get_contents();
	ob_end_clean();

	$savefile = fopen($uploadfile, 'w');
	fwrite($savefile, $contents);
	fclose($savefile);
}

function line_emotic_menu() {
	add_menu_page('Line Emotic', 'Line Emotic', 'administrator', 'line-emotic-settings', 'line_emotic_settings_page', 'dashicons-admin-generic');
}


function line_emotic_settings_page() {
  
  ?>
<div class="wrap">
<h2>Line Emotic Details</h2>
 <span style="color:red;"><?php global $error_line; echo $error_line; ?></span>
<form method="post" action="options.php">
    <?php settings_fields( 'line-emotic-settings-group' ); ?>
    <?php do_settings_sections( 'line-emotic-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Sticker URL</th>
        <td><input type="text" name="line_url" value="<?php echo esc_attr( get_option('line_url') ); ?>" /></td>
        <td><input type="checkbox" name="line_refresh" value="refresh" <?php if (esc_attr( get_option('line_refresh') )!="") echo "checked"; ?> />
          <label for="line_refresh">reload image</label></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Sticker Name</th>
        <td><input type="text" name="line_name" value="<?php echo esc_attr( get_option('line_name') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Display Text</th>
          <td><input type="text" name="line_text" value="<?php echo esc_attr( get_option('line_text') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Position</th>
        <td>
          <select name="line_position" >
            <option value="top" <?php if (esc_attr( get_option('line_position') )=="top") echo "selected"; ?> > Top of content </option>
            <option value="buttom" <?php if (esc_attr( get_option('line_position') )=="buttom") echo "selected"; ?> > Follow content </option>
          </select>
        </td>
        </tr>

        <tr>
        <th scope="row">Emoticon</th>
        <?php
        global $max_num_line;
        for ($i=1; $i <= $max_num_line; $i++) { 
          $val = esc_attr( get_option("line_".$i));
          echo '<td>emo-'.$i.':<input type="text" name="line_'.$i.'" value="'.$val.'" /></td>';
        }
        ?>
        </tr>
        <tr>
          <td><p class="submit"><input type="button" class="button button-primary" id="set-default" value="Set Default" /></p></td>
          <td><?php submit_button(); ?></td>  
          <td colspan="3" > * Clear cache after save change (shift+f5) to display latest sticker</td>  
        </tr>
    </table>

    
    
</form>
<?php

	$uploaddir = plugins_url() . '/line-emotic/images';
	for ($i=0; $i < 10; $i++) { 
  		for ($j=0; $j < 4; $j++) { 
  			$uploadfile = $uploaddir . '/emo-' .$i.'-'.$j .'.png';
  			echo '<div style="display:inline;"><img src="'.$uploadfile.'" >emo-'.$i.'-'.$j.'</div>';
  		}
  	}
?>
</div>
  <?php
}
// Ajax


// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
wp_localize_script( 'my-ajax-request', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

// this hook is fired if the current viewer is not logged in
do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );

// if logged in:
do_action( 'wp_ajax_' . $_POST['action'] );


// if both logged in and not logged in users can send this AJAX request,
// add both of these actions, otherwise add only the appropriate one
add_action( 'wp_ajax_nopriv_myajax-submit', 'myajax_submit' );
add_action( 'wp_ajax_myajax-submit', 'myajax_submit' );

function myajax_submit() {
  // get the submitted parameters
  if(isset($_POST['post_id']) && isset($_POST['position'])){

    line_emotic_install();
    line_emotic_insert_data($_POST['post_id'], $_POST['position']);

    $result = line_emotic_get_data($_POST['post_id']);
    echo json_encode($result);
  }else{
    echo "error";
  }
  // IMPORTANT: don't forget to "exit"
  exit;
}

?>

<?php

// Database

function myplugin_update_db_check() {
    global $jal_db_version;
    global $wpdb;
    $table_name = $wpdb->prefix . "line_emotic";

    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        line_emotic_install();
    }

    if ( get_site_option( 'jal_db_version' ) != $jal_db_version ) {
        var_dump("Update");
        line_emotic_install();
    }
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

function line_emotic_get_data( $postid ){
  global $wpdb;
  
  $table_name = $wpdb->prefix . "line_emotic";

  $meta_key = $postid;
  $result = $wpdb->get_row( $wpdb->prepare( 
    "
      SELECT column1, column2, column3, column4, column5
      FROM $table_name
      WHERE id = %s
    ", 
    $meta_key
  ) ,ARRAY_A );

  return $result;
}
function line_emotic_insert_data($postid , $position){
  global $wpdb;
  
  $table_name = $wpdb->prefix . "line_emotic";

  $meta_key = $postid;
  $result = $wpdb->get_row( $wpdb->prepare( 
    "
      SELECT column1, column2, column3, column4, column5
      FROM $table_name
      WHERE id = %s
    ", 
    $meta_key
  ),ARRAY_A );

  if($result != null){
    
    // Added vote count 
    $result['column'.$position] = $result['column'.$position] + 1;
    // Update
    $wpdb->update(
      $table_name, 
      array(
        'id'               => $postid, 
        'time'             => current_time( 'mysql' ), 
        'column'.$position => $result['column'.$position],
        ), 
      array(
        'id'               => $postid
        )
      );

  }else{

      //insert
      $wpdb->insert( 
        $table_name, 
        array( 
          'id'      => $postid,
          'time'    => current_time( 'mysql' ), 
          'column1' => $position==1? 1 : 0, 
          'column2' => $position==2? 1 : 0, 
          'column3' => $position==3? 1 : 0, 
          'column4' => $position==4? 1 : 0, 
          'column5' => $position==5? 1 : 0, 
        ) 
      );
  }


}

?>