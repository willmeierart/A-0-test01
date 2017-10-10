<?php

/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Modified by Joomunited
 */

// Exit if accessed directly

if (!defined('ABSPATH'))
    exit();

if (!class_exists('WPMSGA_Tools')) {

    class WPMSGA_Tools {
        
        /*
         * get google analytics client
         */
        public static function setClient($client , $access , $access_default) {
            if(isset($access['wpmsga_dash_userapi']) && $access['wpmsga_dash_userapi'] == 1){
                if(!empty($access['wpmsga_dash_clientid']) && !empty($access['wpmsga_dash_clientsecret'])){
                    $client->setClientId($access['wpmsga_dash_clientid']);
                    $client->setClientSecret($access['wpmsga_dash_clientsecret']);
                }else{
                    $client->setClientId($access_default[0]);
                    $client->setClientSecret($access_default[1]);
                }
            }else{
                $client->setClientId($access_default[0]);
                $client->setClientSecret($access_default[1]);
            }

            return $client;
        }
        
        /*
         * get country code
         */
        public static function get_countrycodes() {
            include_once 'iso3166.php';
            return $country_codes;
        }
        
        /*
         * get_default domain
         */
        public static function guess_default_domain($profiles) {
            $domain = get_option('siteurl');
            $domain = str_ireplace(array('http://', 'https://'), '', $domain);
            if (!empty($profiles)) {
                foreach ($profiles as $items) {
                    if (strpos($items[3], $domain)) {
                        return $items[1];
                    }
                }
                return $profiles[0][1];
            } else {
                return '';
            }
        }
        
        /*
         * get_selected_profile
         */
        public static function get_selected_profile($profiles, $profile) {
            if (!empty($profiles)) {
                foreach ($profiles as $item) {
                    if ($item[1] == $profile) {
                        return $item;
                    }
                }
            }
        }
        
        /*
         * get color
         */
        public static function colourVariator($colour, $per) {
            $colour = substr($colour, 1);
            $rgb = '';
            $per = $per / 100 * 255;
            if ($per < 0) {
                // Darker
                $per = abs($per);
                for ($x = 0; $x < 3; $x++) {
                    $c = hexdec(substr($colour, ( 2 * $x), 2)) - $per;
                    $c = ( $c < 0 ) ? 0 : dechex($c);
                    $rgb .= ( strlen($c) < 2 ) ? '0' . $c : $c;
                }
            } else {
                // Lighter
                for ($x = 0; $x < 3; $x++) {
                    $c = hexdec(substr($colour, ( 2 * $x), 2)) + $per;
                    $c = ( $c > 255 ) ? 'ff' : dechex($c);
                    $rgb .= ( strlen($c) < 2 ) ? '0' . $c : $c;
                }
            }
            return '#' . $rgb;
        }

        public static function variations($base) {
            $variations[] = $base;
            $variations[] = self::colourVariator($base, - 10);
            $variations[] = self::colourVariator($base, + 10);
            $variations[] = self::colourVariator($base, + 20);
            $variations[] = self::colourVariator($base, - 20);
            $variations[] = self::colourVariator($base, + 30);
            $variations[] = self::colourVariator($base, - 30);
            return $variations;
        }
        
        /*
         * check roles
         */
        public static function check_roles($access_level, $tracking = false) {
            if (is_user_logged_in() && isset($access_level)) {
                $current_user = wp_get_current_user();
                $roles = (array) $current_user->roles;
                if (( current_user_can('manage_options') ) && !$tracking) {
                    return true;
                }
                if (count(array_intersect($roles, $access_level)) > 0) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        
        /*
         * remove cookie
         */
        public static function unset_cookie($name) {
            $name = 'wpmsga_wg_' . $name;
            setcookie($name, '', time() - 3600, '/');
            $name = 'wpmsga_ir_' . $name;
            setcookie($name, '', time() - 3600, '/');
        }
        
        /*
         * set cache
         */
        public static function set_cache($name, $value, $expiration = 0) {
            $option = array('value' => $value, 'expires' => time() + (int) $expiration);
            update_option('wpmsga_cache_' . $name, $option);
        }
        
        /*
         * remove cache
         */
        public static function delete_cache($name) {
            delete_option('wpmsga_cache_' . $name);
        }
        
        /*
         * get cache
         */
        public static function get_cache($name) {
            $option = get_option('wpmsga_cache_' . $name);

            if (false === $option || !isset($option['value']) || !isset($option['expires'])) {
                return false;
            }

            if ($option['expires'] < time()) {
                delete_option('wpmsga_cache_' . $name);
                return false;
            } else {
                return $option['value'];
            }
        }
        
        /*
         * get site cache
         */
        public static function set_site_cache($name, $value, $expiration = 0) {
            $option = array('value' => $value, 'expires' => time() + (int) $expiration);
            update_site_option('wpmsga_cache_' . $name, $option);
        }
        
        /*
         * remove site cache
         */
        public static function delete_site_cache($name) {
            delete_site_option('wpmsga_cache_' . $name);
        }
        
        /*
         * get site cache
         */
        public static function get_site_cache($name) {
            $option = get_site_option('wpmsga_cache_' . $name);

            if (false === $option || !isset($option['value']) || !isset($option['expires'])) {
                return false;
            }

            if ($option['expires'] < time()) {
                delete_option('wpmsga_cache_' . $name);
                return false;
            } else {
                return $option['value'];
            }
        }
        
        /*
         * clear cache
         */
        public static function clear_cache() {
            global $wpdb;
            $sqlquery = $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'wpmsga_cache_qr%%'");
        }
        
        public static function get_root_domain( $domain ) {
            $root = explode( '/', $domain );
            preg_match( "/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", str_ireplace( 'www', '', isset( $root[2] ) ? $root[2] : $domain ), $root );
            return $root;
        }
        
        public static function strip_protocol( $domain ) {
            return str_replace( array( "https://", "http://", " " ), "", $domain );
        }

    }

}
