<?php
/**
 * Class that holds most of the admin functionality for Meta SEO.
 */
include_once ( WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmstools.php' );
include_once ( WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmsgapi.php' );

class MetaSeo_Front {
    function __construct() {
        $this->ga_tracking = array(
            'wpmsga_dash_tracking' => 1,
            'wpmsga_dash_tracking_type' => 'universal',
            'wpmsga_dash_anonim' => 0,
            'wpmsga_dash_remarketing' => 0,
            'wpmsga_event_tracking' => 0,
            'wpmsga_event_downloads' => 'zip|mp3*|mpe*g|pdf|docx*|pptx*|xlsx*|rar*',
            'wpmsga_aff_tracking' => 0,
            'wpmsga_event_affiliates' => '/out/',
            'wpmsga_hash_tracking' => 0,
            'wpmsga_author_dimindex' => 0,
            'wpmsga_pubyear_dimindex' => 0,
            'wpmsga_category_dimindex' => 0,
            'wpmsga_user_dimindex' => 0,
            'wpmsga_tag_dimindex' => 0,
            'wpmsga_speed_samplerate' => 1,
            'wpmsga_event_bouncerate' => 0,
            'wpmsga_enhanced_links' => 0,
            'wpmsga_dash_adsense' => 0,
            'wpmsga_crossdomain_tracking' => 0,
            'wpmsga_crossdomain_list' => '',
            'wpmsga_cookiedomain' => '',
            'wpmsga_cookiename' => '',
            'wpmsga_cookieexpires' => '',
            'wpmsga_track_exclude' => array(),
            'wpmsga_cookiename' => ''
        );
        
        $ga_tracking = get_option('_metaseo_ggtracking_settings');
        if (is_array($ga_tracking)) {
            $this->ga_tracking = array_merge($this->ga_tracking, $ga_tracking);
        }

        $this->ga_tracking_disconnect = array(
            'wpms_ga_uax_reference' => '',
            'wpmsga_dash_tracking_type' => 'classic',
            'wpmsga_code_tracking' => ''
        );
        $ga_tracking_disconnect = get_option('_metaseo_ggtracking_disconnect_settings');
        if (is_array($ga_tracking_disconnect)) {
            $this->ga_tracking_disconnect = array_merge($this->ga_tracking_disconnect, $ga_tracking_disconnect);
        }
        
        add_action( 'wp_head', array( $this, 'tracking_code' ), 99 );
    }
    
    public function tracking_code() {
        if(!empty($this->ga_tracking['wpmsga_code_tracking'])){
            require_once 'google_analytics/tracking/custom.php';
            return;
        }
        
        if (WPMSGA_Tools::check_roles($this->ga_tracking['wpmsga_track_exclude'], true)) {
            return;
        }
        
        $google_alanytics = get_option('wpms_google_alanytics');
        $traking_mode = $this->ga_tracking['wpmsga_dash_tracking'];
        $traking_type = $this->ga_tracking['wpmsga_dash_tracking_type'];
        if ($traking_mode > 0) {
            if (empty($google_alanytics['tableid_jail'])) {
                if(!empty($this->ga_tracking_disconnect['wpmsga_code_tracking'])){
                    echo '<script type="text/javascript">';
                    echo strip_tags($this->ga_tracking_disconnect['wpmsga_code_tracking'],'</script>');
                    echo '</script>';
                }else{
                    if(empty($this->ga_tracking_disconnect['wpms_ga_uax_reference'])){
                        return;
                    }
                    if ($traking_type == "classic") {
                        echo "\n<!-- BEGIN WPMSGA v" . WPMSEO_VERSION . " Classic Tracking - https://wordpress.org/plugins/wp-meta-seo/ -->\n";
                        require_once 'google_analytics/tracking/classic_disconnect.php';
                        echo "\n<!-- END WPMSGA Classic Tracking -->\n\n";
                    } else {
                        echo "\n<!-- BEGIN WPMSGA v" . WPMSEO_VERSION . " Universal Tracking - https://wordpress.org/plugins/wp-meta-seo/ -->\n";
                        require_once 'google_analytics/tracking/universal_disconnect.php';
                        echo "\n<!-- END WPMSGA Universal Tracking -->\n\n";
                    }
                }
            }else{
                if ($traking_type == "classic") {
                    echo "\n<!-- BEGIN WPMSGA v" . WPMSEO_VERSION . " Classic Tracking - https://wordpress.org/plugins/wp-meta-seo/ -->\n";
                    if ($this->ga_tracking['wpmsga_event_tracking']) {
                        require_once 'google_analytics/tracking/events-classic.php';
                    }
                    require_once 'google_analytics/tracking/code-classic.php';
                    echo "\n<!-- END WPMSGA Classic Tracking -->\n\n";
                } else {
                    echo "\n<!-- BEGIN WPMSGA v" . WPMSEO_VERSION . " Universal Tracking - https://wordpress.org/plugins/wp-meta-seo/ -->\n";
                    if ($this->ga_tracking['wpmsga_event_tracking'] || $this->ga_tracking['wpmsga_aff_tracking'] || $this->ga_tracking['wpmsga_hash_tracking']) {
                        require_once 'google_analytics/tracking/events-universal.php';
                    }
                    require_once 'google_analytics/tracking/code-universal.php';
                    echo "\n<!-- END WPMSGA Universal Tracking -->\n\n";
                }
            }
        }
    }
    
}
