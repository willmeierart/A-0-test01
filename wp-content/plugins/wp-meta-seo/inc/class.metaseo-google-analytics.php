<?php

/**
 * Base class for displaying your google analytics.
 *
 */
class MetaSeo_Google_Analytics {
    
    /*
     * ajax display google analytics
     */
    public static function ajax_item_reports() {
        include_once ( WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmstools.php' );
        include_once ( WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmsgapi.php' );
        $google_alanytics = get_option('wpms_google_alanytics');

        if (!isset($_POST['wpms_security_backend_item_reports']) || !wp_verify_nonce($_POST['wpms_security_backend_item_reports'], 'wpms_backend_item_reports')) {
            wp_die(- 30);
        }

        if (isset($_POST['projectId']) && $_POST['projectId'] !== 'false') {
            $projectId = $_POST['projectId'];
        } else {
            $projectId = false;
        }
        $from = $_POST['from'];
        $to = $_POST['to'];
        $query = $_POST['query'];
        if (isset($_POST['filter'])) {
            $filter_id = $_POST['filter'];
        } else {
            $filter_id = false;
        }
        if (ob_get_length()) {
            ob_clean();
        }
        
        if (!empty($google_alanytics['tableid_jail'])) {
            if (empty($wpmsga_controller)) {
                $wpmsga_controller = new WPMS_GAPI_Controller();
            }
        } else {
            wp_die(- 99);
        }

        if (!empty($google_alanytics['googleCredentials']) && !empty($google_alanytics['tableid_jail']) && isset($from) && isset($to)) {
            if (empty($wpmsga_controller)) {
                $wpmsga_controller = new WPMS_GAPI_Controller();
            }
        } else {
            wp_die(- 24);
        }

        if ($projectId == false) {
            $projectId = $google_alanytics['tableid_jail'];
        }
        $profile_info = WPMSGA_Tools::get_selected_profile($google_alanytics['profile_list'], $projectId);
        if (isset($profile_info[4])) {
            $wpmsga_controller->timeshift = $profile_info[4];
        } else {
            $wpmsga_controller->timeshift = (int) current_time('timestamp') - time();
        }

        if ($filter_id) {
            $uri_parts = explode('/', get_permalink($filter_id), 4);

            if (isset($uri_parts[3])) {
                $uri = '/' . $uri_parts[3];
            } else {
                wp_die(- 25);
            }

            // allow URL correction before sending an API request
            $filter = apply_filters('wpmsga_backenditem_uri', $uri);
            $lastchar = substr($filter, - 1);

            if (isset($profile_info[6]) && $profile_info[6] && $lastchar == '/') {
                $filter = $filter . $profile_info[6];
            }

            // Encode URL
            $filter = rawurlencode(rawurldecode($filter));
        } else {
            $filter = false;
        }

        $queries = explode(',', $query);

        $results = array();

        foreach ($queries as $value) {
            $results[] = $wpmsga_controller->get($projectId, $value, $from, $to, $filter);
        }

        wp_send_json($results);
    }

    public static function wpmsga_update_option() {
        $options = get_option('wpms_google_alanytics');
        if (isset($_POST['userapi'])) {
            $options['wpmsga_dash_userapi'] = $_POST['userapi'];
            update_option('wpms_google_alanytics', $options);
        }
        wp_send_json(true);
    }
   
    /*
    * ajax clear author
    */
    public static function wpmsga_wpmsClearauthor() {
        delete_option('wpms_google_alanytics');
        wp_send_json(true);
    }

    public static function map($map) {
        $map = explode('.', $map);
        if (isset($map[1])) {
            $map[0] += ord('map');
            return implode('.', $map);
        } else {
            return str_ireplace('map', chr(112), $map[0]);
        }
    }

}
