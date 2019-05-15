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


// Shortcodes
// register our custom shortcodes
function swp_register_shortcodes(){

    add_shortcode( 'swp_form', 'swp_form_shortcode' );

}

//return html string for mail capture form
function swp_form_shortcode( $args, $content=""){

    //setup output variable - the form html

    $output = '
    
    <div class="swp">

        <form id="swp_form" name="swp_form" class="swp_form" method="post">
            <p class="swp-input-container">

                <label>Volledige naam: </label><br />
                <input type="text" name="swp_fname" placeholder="Voornaam">
                <input type="text" name="swp_lname" placeholder="Achternaam">
            
            <p>

            <p class="swp-input-container">
        
                <label>Email:</label><br />
                <input type="email" name="swp_email" placeholder="ex. example@hotmail.nl">
        
            <p';

            //Including content in our form html if content is passed into the function
            if( strlen($content) > 0):

                $output .= '<div class="swp_content">' . wpautop($content) . '</div>';

            endif; 

            //completing our form with a submit
            $output .= '<p class="swp-input-container">
        
                <input type="submit" name="swp_submit" value="verzend dit form">
        
            <p>

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
        'cb'=>'<input type="chechbox" />',
        'title'=>__('List Name')
    );

    //returning new columns
    return $columns;
}

function swp_list_column_data( $column, $post_id ){

    //setup our return text
    $output ='';

    switch($column){

        case 'example': 
            //get custom data
            // $fname = get_field('swp_fname', $post_id);
            // $lname = get_field('swp_lname', $post_id);
            // $output .= $fname . ' ' . $lname;
            break; 

    }

    //echo the output
    echo $output;
}

?>