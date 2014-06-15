<?php
/**
 * @package Hello_Licenses
 * @version 0.1
 */
/*
Plugin Name: Hello Licenses
Plugin URI: http://wordpress.org/plugins/hello-licenses/
Description: This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up for a free world.
Author: Rafael Trabasso & Gabriel Trabasso
Version: 0.1
Author URI: 
*/

function hello_licenses_load_scripts($hook) {
 
    if( $hook != 'post.php' && $hook != 'post-new.php' ) 
        return;
 
    wp_enqueue_script( 'hello-licenses.js', plugins_url( 'hello-licenses/js/hello-licenses.js' , dirname(__FILE__) ) );
}
add_action('admin_enqueue_scripts', 'hello_licenses_load_scripts');


function hello_licenses_get_licenses() {
    $licenses = array( 
        'None'      => 'No license assigned', 
        'GPL'       => 'GNU Public License', 
        'GPL v2'    => 'GNU Public License version 2', 
        'MIT'       => 'bla bla bla' 
    );

    return $licenses;
}

function hello_licenses() {
    $chosen = hello_licenses_get_licenses();
    echo "<p id='license'>$chosen</p>";
}
//add_action( 'admin_notices', 'hello_license' );

// Post & Page MetaBox 
/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function hello_licenses_add_meta_box() {

    $screens = array( 'post', 'page' );

    foreach ( $screens as $screen ) {

        add_meta_box(
            'hello_licenses_sectionid',
            __( 'Add a License to this resource', 'hello_licenses_textdomain' ),
            'hello_licenses_meta_box_callback',
            $screen
        );
    }
}
add_action( 'add_meta_boxes', 'hello_licenses_add_meta_box' );

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function hello_licenses_meta_box_callback( $post ) {

    // Add an nonce field so we can check for it later.
    wp_nonce_field( 'hello_licenses_meta_box', 'hello_licenses_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, '_hello_licenses_id', true );

    echo '<label for="hello_licenses_id">';
    _e( 'License:', 'hello_licenses_textdomain' );
    echo '</label> ';
    $licenses = hello_licenses_get_licenses();
    $license_select = '<select id="hello_licenses_id" name="hello_licenses_id">';
    foreach ( $licenses as $license => $description ) {
        $license_select .= '<option ';
        if ( $license == $value )
            $license_select .= 'selected="selected" ';
        $license_select .= 'value="' . esc_attr( $license ) . '">' . $license . '</option>';
    }
    $license_select .= '</select>';
    echo $license_select;   

    $license_descriptions = '<div id="hello_licenses_descriptions" name="hello_licenses_descriptions" hidden="hidden">';
    foreach ( $licenses as $license => $description ) {
        $license_descriptions .= '<div id="hello_licenses_descriptions_' . esc_attr( str_replace( ' ', '', $license ) ) . '" name="hello_licenses_descriptions_' . esc_attr( $license ) . '">' . wptexturize( $description ) . '</div>';
    }
    $license_descriptions .= '</div>';
    echo $license_descriptions;

    echo '<label for="hello_licenses_description">';
    _e( 'Description:', 'hello_licenses_textdomain' );
    echo '</label> <textarea type="text" readonly="readonly" id="hello_licenses_description" name="hello_licenses_description"></textarea>';
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function hello_licenses_save_meta_box_data( $post_id ) {

    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['hello_licenses_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['hello_licenses_meta_box_nonce'], 'hello_licenses_meta_box' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */
    
    // Make sure that it is set.
    if ( ! isset( $_POST['hello_licenses_id'] ) && ! isset( $_POST['hello_licenses_description'] ) ) {
        return;
    }

    // Sanitize user input.
    $license_id = sanitize_text_field( $_POST['hello_licenses_id'] );
    $license_description = sanitize_text_field( $_POST['hello_licenses_description'] );
    // Update the meta field in the database.
    update_post_meta( $post_id, '_hello_licenses_id', $license_id );
    update_post_meta( $post_id, '_hello_licenses_description', $license_description );
}
add_action( 'save_post', 'hello_licenses_save_meta_box_data' );

function license_css() {
    // This makes sure that the positioning is also good for right-to-left languages
    $x = is_rtl() ? 'left' : 'right';

    echo "
    <style type='text/css'>
    #license {
        float: $x;
        padding-$x: 15px;
        padding-top: 5px;       
        margin: 0;
        font-size: 11px;
    }
    </style>
    ";
}
//add_action( 'admin_head', 'license_css' );

?>
