<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              #
 * @since             1.0.0
 * @package           dsm_post_stats_plugin
 *
 * @wordpress-plugin
 * Plugin Name:       dsm_post_stats_plugin
 * Plugin URI:        #
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Dan Mobbs
 * Author URI:        #
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dsm post stats plugin
 * Domain Path:       /languages
 */



 class Dsm_WordCountPlugin
 {
    function __construct() { //Runs first at the start of the class!!
        add_action('admin_menu', array($this, 'pluginSettings'));
        add_action('admin_init', array($this, 'settings')); 
        add_filter( 'the_content', array($this, 'ifWrap'));
    }

    function ifWrap($content)
    {
        if ( is_main_query() AND is_single() AND 
        (
            get_option( 'dsm_word_count', '1') OR 
            get_option( 'dsm_char_count', '1') OR
            get_option( 'dsm_read_count', '1')
        )) {
            return $this->createHTML($content);    
        }
        return $content;
    }

    function createHTML($content)
    {
        $htmlPageContent = '<h3>' . esc_html(get_option( 'dsm_headline', 'Post Statistics' )) . '</h3><p>';

        // Get word count once!
        if ( get_option( 'dsm_word_count', '1') OR get_option( 'dsm_read_count', '1')) 
        {
            $wordCount = str_word_count( strip_tags($content));
        }   

        if ( get_option( 'dsm_word_count', '1') )
        {
            $htmlPageContent .= 'This post has: ' . $wordCount . ' Words. <br />';
        }

        if ( get_option( 'dsm_char_count', '1') )
        {
            $htmlPageContent .= 'This post has: ' . strlen(strip_tags($content)) . ' characters. <br />';
        }

        if ( get_option( 'dsm_read_count', '1') )
        {
            $htmlPageContent .= 'This post will take about: ' . round($wordCount/255) . ' minute(s) to read. <br />';
        }

        $html .= '</p>';

        if ( get_option(  'dsm_location', '0' ) == '0' ) 
        {
            return $htmlPageContent . $content;
        } 
        return $content . $htmlPageContent;
    }

    function settings() {
        add_settings_section('dsm_first_section', null, null, 'word-count-settings-page');
        
        //Location
        add_settings_field('dsm_location', 'Display Location', array($this, 'locationHTML'), 'word-count-settings-page', 'dsm_first_section');
        register_setting('wordcountplugin', 'dsm_location', array('sanitize_callback' => array($this, 'sanitizelocation'), 'default' => '0'));

        //Headline Text Field
        add_settings_field('dsm_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-settings-page', 'dsm_first_section');
        register_setting('wordcountplugin', 'dsm_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));

        //Word Count
        add_settings_field('dsm_word_count', 'Word Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'dsm_first_section', array('theName' => 'dsm_word_count'));
        register_setting('wordcountplugin', 'dsm_word_count', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        //Character Count
        add_settings_field('dsm_char_count', 'Character Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'dsm_first_section', array('theName' => 'dsm_char_count'));
        register_setting('wordcountplugin', 'dsm_char_count', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        //Read Count
        add_settings_field('dsm_read_count', 'Read Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'dsm_first_section', array('theName' => 'dsm_read_count'));
        register_setting('wordcountplugin', 'dsm_read_count', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
    }

    function sanitizelocation($input)
    {
        if ( $input != '0' AND $input != '1' ) 
        {
            add_settings_error( 'dsm_location', 'dsm_location_error', 'Display Location must be Beginning or End!' );
            return get_option( 'dsm_location' );
        }
        return $input;
    }

    function checkboxHTML($args) {
    ?>
        <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked(get_option($args['theName']), '1') ?>>
    <?php    
    }  

    function headlineHTMl() {
    ?>
        <input type="text" name="dsm_headline" value="<?php echo esc_attr(get_option('dsm_headline')) ?>">
    <?php
    }

    function locationHTML() { 
    ?>        
        <select name="dsm_location">
            <option value="0" <?php selected(get_option('dsm_location'), '0') ?>>Beginning of post</option>
            <option value="1" <?php selected(get_option('dsm_location'), '1') ?>>End of post</option>
        </select> 
    <?php
    }

    function pluginSettings() {
        add_options_page('Word Count Settings', 'Word Count', 'manage_options', 'word-count-settings-page', array($this, 'Settings_HTML'));
   }
   
   function Settings_HTML() { 
    ?>
       <div class="wrap">
           <h1>Word Count Settings</h1>
           <form action="options.php" method="POST">
            <?php
                settings_fields('wordcountplugin');
                do_settings_sections('word-count-settings-page');
                submit_button();
            ?>
           </form>
       </div>
   <?php
   }
 }

 $wordCountPlugin = new Dsm_WordCountPlugin();



 