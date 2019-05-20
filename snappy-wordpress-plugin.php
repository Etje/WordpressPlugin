<?php 

/*
Plugin Name: snappy Wordpress Plugin
Plugin URI: https://wordpress.altrixmedia.nl/
Description: The ultimate plugin
Version: 1.0.0
Author: Etiën Ruhl
Author URI: https://www.linkedin.com/in/eti%C3%ABn-ruhl/
License: GPL2
License URI: https://www.gnu.org/licenses/licenses.nl.html
Text Domain: Snappy-Wordpress-plugin
*/

define('pathJS', plugins_url() . '/snappy-list-plugin/js/public'); 
define('pathCSS', plugins_url() . '/snappy-list-plugin/css/public'); 

// Hooks
// register shortcode in init function
add_action('init', 'swp_register_shortcodes');

add_filter('manage_edit-swp_subscriber_columns', 'swp_subscriber_column_heading');
add_filter('manage_edit-swp_list_columns', 'swp_list_column_heading');

add_filter('manage_swp_subscriber_posts_custom_column', 'swp_subscriber_column_data',1,2);
add_action(
        'admin_head-edit.php', 
        'swp_register_custom_admin_titles');
add_filter('manage_swp_list_posts_custom_column', 'swp_list_column_data',1,2);

//register ajax actions
add_action('wp_ajax_nopriv_swp_save_subscription', 'swp_save_subscription'); //regular website visitor
add_action('wp_ajax_swp_save_subscription', 'swp_save_subscription'); //admin user

add_action( 'wp_enqueue_scripts', 'my_swp_custom_scripts' );

add_filter( 'acf/settings/path', 'slp_acf_settings_path' );
add_filter( 'acf/settings/dir', 'slp_acf_settings_dir' );
add_filter( 'acf/settings/show_admin', 'slp_acf_show_admin' );

if(!defined('ACF_LITE')) define('ACF_LITE', true); 

// Shortcodes
// register our custom shortcodes
function swp_register_shortcodes(){

    add_shortcode( 'swp_form', 'swp_form_shortcode' );

}

//return html string for mail capture form
function swp_form_shortcode( $args, $content=""){

    //get the list id
    $list_id = 0; 
    if( isset($args['id']) ) $list_id = (int)$args['id'];

    $title = ''; 
    if( isset($args['title']) ) $title = (string)$args['title'];

    //setup output variable - the form html
    $output = '
    
    <div class="swp">

        <form id="swp_form" name="swp_form" class="swp_form" method="post" action="/wp-admin/admin-ajax.php?action=swp_save_subscription">
            
            <input type="hidden" name="swp_list" value="' . $list_id . '">';

            if(strlen($title)): 

                $output .= '<h3 class="swp-title">' . $title . '</h3>';

            endif; 

            $output .= '<div class="swp-input-container">

                <label>Volledige naam: </label><br />
                <input type="text" name="swp_fname" placeholder="Voornaam">
                <input type="text" name="swp_lname" placeholder="Achternaam">
            
            </div>

            <div class="swp-input-container">
        
                <label>Email:</label><br />
                <input type="email" name="swp_email" placeholder="ex. example@hotmail.nl">   
            </div>';


            //Including content in our form html if content is passed into the function
            if( strlen($content) > 0):

                $output .= '<div class="swp_content">' . wpautop($content) . '</div>';

            endif; 

            //completing our form with a submit
            $output .= '<div class="swp-input-container">
        
                <input type="submit" name="swp_submit" value="Meld me aan!">
        
            </div>

        </form>

    </div>

    ';

    //return our HTML output
    return $output;
}

//Filters

function swp_subscriber_column_heading( $columns ){

    //creating custom header data
    $columns = array(
        'cb'=>'<input type="chechbox" />',
        'title'=>__('Subscriber Name'),
        'email'=>__('Email Adres')
    );

    //returning new columns
    return $columns;
}

function swp_subscriber_column_data( $column, $post_id ){

    //setup our return text
    $output ='';

    switch($column){

        case 'title': 
            //get custom data
            $fname = get_field('swp_fname', $post_id);
            $lname = get_field('swp_lname', $post_id);
            $output .= $fname . ' ' . $lname;
            break; 
        case 'email': 
            //get custom data
            $email = get_field('swp_email', $post_id);
            $output .= $email;
            break;

    }

    //echo the output
    echo $output;
}

//create custom admin title column
function swp_register_custom_admin_titles(){
    add_filter( 'the_title', 'swp_custom_admin_titles', 99, 2);
}

function swp_custom_admin_titles( $title, $post_id ){

    global $post; 

    $output = $title; 

    if( isset($post->post_type) ):
 
        switch($post->post_type){
            case 'swp_subscriber':
                $fname = get_field('swp_fname', $post_id);
                $lname = get_field('swp_lname', $post_id);
                $output = $fname . ' ' . $lname;
                break;
        }

    endif; 

    return $output;
}

function swp_list_column_heading( $columns ){

    //creating custom header data
    $columns = array(
        'cb'        =>'<input type="chechbox" />',
        'title'     =>__('List Name'),
        'shortcode' =>__('Shortcode')
    );

    //returning new columns
    return $columns;
}

function swp_list_column_data( $column, $post_id ){

    //setup our return text
    $output ='';

    switch($column){

        case 'shortcode': 
            $output .= '[swp_form id="' . $post_id . '"]';
            break; 

    }

    //echo the output
    echo $output;
}

//Actions
//save subscription data to a new subscriber
function swp_save_subscription(){

    $result = array(
        'status'    => 0,
        'message'   => 'Aanmelding is mislukt. ',
        'error'     => '',
        'errors'    => array()
    );



    $errors = array();

    try {
        $list_id = (int)$_POST['swp_list'];

        $subscriber_data = array(
            'fname' => esc_attr($_POST['swp_fname']),
            'lname' => esc_attr($_POST['swp_lname']),
            'email' => esc_attr($_POST['swp_email'])
        );

        //setup errors array
        $errors = array();

        if(!strlen($subscriber_data['fname']) ) $errors['fname'] = 'Voornaam dient te worden ingevuld';
        if(!strlen($subscriber_data['email']) ) $errors['email'] = 'email dient te worden ingevuld';
        if(strlen($subscriber_data['email']) && !is_email( $subscriber_data['email'] ) ) $errors['email'] = 'Emailadres moet kloppend zijn';

        if(count($errors)):

            $result['error']    = 'Sommige velden zijn niet/ of niet correct ingevuld';
            $result['errors']   = $errors;

        else :
        //Als er geen errors zijn

        $subscriber_id = swp_save_subscriber($subscriber_data);

            if( $subscriber_id ):

                if(swp_subscriber_has_subscription( $subscriber_id, $list_id ) ):

                    $list = get_post($list_id);

                    $result['error'] = esc_attr($subscriber_data['email'] . ' is al in gebruik door ' . $list->post_title .'.');

                    else:

                    $subscription_saved = swp_add_subscription( $subscriber_id, $list_id );

                    if( $subscription_saved ):
                        $result['status'] = 1;
                        $result['message'] = 'aanmelding opgeslagen';

                    else: 
                        // return detailed error
                        $result['error'] = 'kan de aanmelding niet voltooien.';
                    
                    endif;

                endif; 

            endif; 

        endif;

    } catch( Exception $e ) {

    }

    //return result as JSON
    swp_return_json($result);

} 

function swp_save_subscriber($subscriber_data){

    //setup default describer_id
    $subscriber_id = 0; 

    try {

        $subscriber_id = swp_get_subscriber_id( $subscriber_data['email'] );

        if( !$subscriber_id ):

            $subscriber_id = wp_insert_post(
                array(
                    'post_type'     => 'swp_subscriber',
                    'post_title'    => $subscriber_data['fname'] . ' ' . $subscriber_data['lname'],
                    'post_status'   => 'publish'
                ), 
                true
            );

        endif; 

        //add / update custom data
        update_field(swp_get_acf_key('swp_fname'), $subscriber_data['fname'], $subscriber_id);
        update_field(swp_get_acf_key('swp_lname'), $subscriber_data['lname'], $subscriber_id);
        update_field(swp_get_acf_key('swp_email'), $subscriber_data['email'], $subscriber_id);

    } catch( Exception $e ) {

    }

    //return subscriber_id
    return $subscriber_id;
}

function swp_add_subscription($subscriber_id, $list_id){

    $subscription_saved = false; 

    if( !swp_subscriber_has_subscription( $subscriber_id, $list_id ) ) :

        $subscriptions = swp_get_subscriptions($subscriber_id);
        $subscriptions[] = $list_id;

        update_field(swp_get_acf_key('swp_subscriptions'), $subscriptions, $subscriber_id);

        $subscription_saved = true;

    endif; 

    return $subscription_saved;

}


//Helpers

function swp_get_acf_key($field_name){

    $field_key = $field_name; 

    switch( $field_name ) {
        case 'swp_fname':
            $field_key = 'field_5cdbc481a506a';
        break; 
        case 'swp_lname':
            $field_key = 'field_5cdbc4aca506b';
        break; 
        case 'swp_email':
            $field_key = 'field_5cdbc4c0a506c';
        break; 
        case 'swp_subscriptions':
            $field_key = 'field_5cdbc4e9a506d';
        break; 
            
    }

    return $field_key;

}

function swp_get_subscriber_data($subscriber_id){

    $subscriber_data = array();

    $subscriber = get_post( $subscriber_id );

    if( isset($subscriber->post_type) && $subscriber->post_type == 'swp_subscriber' ):

        $fname = get_field( swp_get_acf_key('swp_fname'), $subscriber_id);
        $lname = get_field( swp_get_acf_key('swp_lname'), $subscriber_id);

        $subscriber_data = array(
            'name'          => $fname . ' ' . $lname,
            'fname'         => $fname,
            'lname'         => $lname,
            'email'         => get_field( swp_get_acf_key('swp_email'), $subscriber_id),
            'subscriptions' => swp_get_subscriptions( $subscriber_id )
        );

    endif;

    return $subscriber_data;
}

function swp_subscriber_has_subscription( $subscriber_id, $list_id ){

    //setup default return value
    $has_subscription = false; 

    //get subscriber
    $subscriber = get_post($subscriber_id);

    //get subscriptions
    $subscriptions = swp_get_subscriptions( $subscriber_id );

    if( in_array($list_id, $subscriptions) ): 
        $has_subscription = true;
    else :

    endif; 
    
    return $has_subscription;
}

function swp_get_subscriber_id( $email ){

    $subscriber_id = 0;

    try {
        $subscriber_query = new WP_Query(
            array(
                'post_type'         =>  'swp_subscriber',
                'posts_per_page'    =>  1,
                'meta_key'          =>  'swp_email',
                'meta_query'        => array(
                    array(
                        'key'       => 'swp_email',
                        'value'     => $email,
                        'compare'   => '=',
                    ),
                ),
            )
        );

        if($subscriber_query->have_posts() ):

            $subscriber_query->the_post();
            $subscriber_id = get_the_ID();

        endif;

    } catch( Exception $e ){

    }

    //reset wordpress query
    wp_reset_query();

    return (int)$subscriber_id;

}

function swp_get_subscriptions($subscriber_id){

    $subscriptions = array();

    $lists = get_field(swp_get_acf_key('swp_subscriptions'), $subscriber_id);

    if($lists): 

        if(is_array($lists) && count($lists) ):

            foreach($lists as &$list):
                $subscriptions[]= (int)$list->ID;
            endforeach;

        elseif( is_numeric($lists) ) :
            $subscriptions[]= $lists;
        endif; 

    endif; 

    return (array)$subscriptions;

}

function swp_return_json($php_array){

    $json_result = json_encode($php_array);

    die($json_result);

    exit;

}

//External scripts

include_once(plugin_dir_path(__FILE__) . 'lib/advanced-custom-fields/acf.php' );

function my_swp_custom_scripts(){
 

	// add to que of scripts that get loaded into every page
    wp_register_script('snappy-wordpress-plugin-js-public', constant('pathJS') . '/snappy-wordpress-plugin.js', array('jquery'), '', true );
    wp_register_style('snappy-wordpress-plugin-css-public', constant('pathCSS') . '/snappy-wordpress-plugin.css' );

    //add to que of scripts
    wp_enqueue_script('snappy-wordpress-plugin-js-public');
    wp_enqueue_style('snappy-wordpress-plugin-css-public');
    
}

//functions for external scripts

function slp_acf_settings_path($path){
    $path = plugin_dir_path( __FILE__ ) . '/lib/advanced-custom-fields/';
    return $path;
}

function slp_acf_settings_dir($dir){
    $dir = plugin_dir_url( __FILE__ ) . '/lib/advanced-custom-fields/';
    return $dir;
}

function slp_acf_show_admin($admin){
    $admin = plugin_dir_url( __FILE__ ) . '/lib/advanced-custom-fields/';
    return $admin;
}

include_once(plugin_dir_path( __FILE__ ) . 'cpt/slp-subscriber.php');


?>