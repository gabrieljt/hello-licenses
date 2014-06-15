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

class HelloLicense {
    public $id;
    public $description;
    public $url;
    public $image;

    public function __construct($license_id, $license_description, $license_url, $license_image) {
        $this->id           = $license_id;
        $this->description  = $license_description;
        $this->url          = $license_url;
        $this->image        = $license_image;
    }
}

function hello_licenses_load_scripts($hook) {
 
    if( $hook != 'post.php' && $hook != 'post-new.php' ) 
        return;
 
    wp_enqueue_script( 'hello-licenses.js', plugins_url( 'hello-licenses/js/hello-licenses.js' , dirname(__FILE__) ) );
}
add_action('admin_enqueue_scripts', 'hello_licenses_load_scripts');


function hello_licenses_get_licenses() {
    $licenses = array( 
        new HelloLicense( 'None', 'No License Assigned.', null, null ),
        new HelloLicense( 'GPL v2', 'GNU General Public License, version 2.', 'http://www.gnu.org/licenses/old-licenses/gpl-2.0.html', 'http://www.gnu.org/graphics/heckert_gnu.small.png' ),
        new HelloLicense( 'GPL v3', 'GNU General Public License, version 3.', 'http://www.gnu.org/copyleft/gpl.html', 'http://www.gnu.org/graphics/gplv3-127x51.png' ),
        new HelloLicense( 'MIT', 'The MIT License.', 'http://opensource.org/licenses/MIT', 'http://opensource.org/trademarks/opensource/OSI-Approved-License-100x137.png' )
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
     * from the database and use the value for the License select.
     */
    $value = get_post_meta( $post->ID, '_hello_licenses_id', true );
    $licenses = hello_licenses_get_licenses();

    // Hidden Licenses information for jQuery select change and form submit.
    $licenses_info = '<div hidden="hidden" id="hello_licenses_info" name="hello_licenses_info">';
    foreach ( $licenses as $license ) {
        $licenses_info .= '<div id="hello_licenses_description_' . esc_attr( str_replace( ' ', '', $license->id ) ) . '" name="hello_licenses_description_' . esc_attr( str_replace( ' ', '', $license->id ) ) . '">' . wptexturize( $license->description ) . '</div>';
        $licenses_info .= '<div id="hello_licenses_url_' . esc_attr( str_replace( ' ', '', $license->id ) ) . '" name="hello_licenses_url_' . esc_attr( str_replace( ' ', '', $license->id ) ) . '">' . wptexturize( $license->url ) . '</div>';
        $licenses_info .= '<div id="hello_licenses_image_' . esc_attr( str_replace( ' ', '', $license->id ) ) . '" name="hello_licenses_image_' . esc_attr( str_replace( ' ', '', $license->id ) ) . '">' . wptexturize( $license->image ) . '</div>';
    }
    $licenses_info .= '</div>';
    $licenses_info .= '<input type="hidden" id="hello_licenses_description_input" name="hello_licenses_description_input">';
    $licenses_info .= '<input type="hidden" id="hello_licenses_url_input" name="hello_licenses_url_input">';
    $licenses_info .= '<input type="hidden" id="hello_licenses_image_input" name="hello_licenses_image_input">';
    echo $licenses_info;

    // License select.
    echo '<label for="hello_licenses_id_input">';
    _e( 'License:', 'hello_licenses_textdomain' );
    echo '</label> ';

    $licenses_select = '<select id="hello_licenses_id_input" name="hello_licenses_id_input">';
    foreach ( $licenses as $license ) {
        $licenses_select .= '<option ';
        if ( $license->id == $value )
            $licenses_select .= 'selected="selected" ';
        $licenses_select .= 'value="' . esc_attr( $license->id ) . '">' . $license->id . '</option>';
    }
    $licenses_select .= '</select>';
    echo $licenses_select;   
    
    // Show selected License information.
    echo '<label for="hello_licenses_description">';
    _e( 'Description:', 'hello_licenses_textdomain' );
    echo '</label> <div id="hello_licenses_description" name="hello_licenses_description"></div>';

    echo '<label for="hello_licenses_url">';
    _e( 'Website:', 'hello_licenses_textdomain' );
    echo '</label> <div id="hello_licenses_url" name="hello_licenses_url"></div>';

    echo '<label for="hello_licenses_image">';
    _e( 'Logo:', 'hello_licenses_textdomain' );
    echo '</label> <div id="hello_licenses_image" name="hello_licenses_image"></div>';
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
    
    // Make sure that id and description are set.
    /*if ( ! isset( $_POST['hello_licenses_id_input'] ) && ! isset( $_POST['hello_licenses_description_input'] ) ) {
        return;
    }*/

    // Sanitize user input.
    $license_id = sanitize_text_field( $_POST['hello_licenses_id_input'] );
    $license_description = sanitize_text_field( $_POST['hello_licenses_description_input'] );
    $license_url = sanitize_text_field( $_POST['hello_licenses_url_input'] );
    $license_image = sanitize_text_field( $_POST['hello_licenses_image_input'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_hello_licenses_id', $license_id );
    update_post_meta( $post_id, '_hello_licenses_description', $license_description );
    update_post_meta( $post_id, '_hello_licenses_url', $license_url );
    update_post_meta( $post_id, '_hello_licenses_image', $license_image );
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
