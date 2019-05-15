<?php 

/*
Plugin Name: snappy List Plugin
Plugin URI: https://wordpress.altrixmedia.nl/
Description: The ultimate plugin
Version: 1.0.0
Author: Etiën Ruhl
Author URI: https://www.linkedin.com/in/eti%C3%ABn-ruhl/
License: GPL2
License URI: https://www.gnu.org/licenses/licenses.nl.html
Text Domain: Snappy-list-plugin
*/

// Hooks
// register shortcode in init function
add_action('init', 'swp_register_shortcodes');

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

                <label>Your name</label><br />
                <input type="text" name="swp_fname" placeholder="First name">
                <input type="text" name="swp_lname" placeholder="Last name">
            
            <p>

            <p class="swp-input-container">
        
                <label>Your email</label><br />
                <input type="email" name="swp_email" placeholder="ex. etien_ruhl@live.nl">
        
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
?>