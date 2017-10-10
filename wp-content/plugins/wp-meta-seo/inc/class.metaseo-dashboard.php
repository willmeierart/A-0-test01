<?php
/*
 * This class implements the statistical criteria of Meta SEO
 *
 */

class MetaSeo_Dashboard {

    public static $meta_title_length = 69;
    public static $meta_desc_length = 156;
    public static $metatitle_filled = 0;
    public static $metadesc_filled = 0;
    public static $imageresizing_filled = 0;
    public static $imagemeta_filled = 0;
    public static $image_in_post = 0;
    public static $mpostname_inurl = 0;
    public static $mcategory_inurl = 0;
    public static $mpermalink = 50;
    public static $mlink_complete = 0;
    public static $mcount_link = 0;

    /*
     * Get image optimize 
     */
    public static function moptimizationChecking() {
        global $wpdb;
        $imgs = 0;
        $imgs_metas = array('alt' => 0, 'title' => 0);
        $imgs_are_good = 0;
        $imgs_metas_are_good = array();
        $meta_keys = array('alt', 'title');
        $response = array(
            'imgs_statis' => array(0, 0 ,100),
            'imgs_metas_statis' => array(0, 0 ,100),
        );
        foreach ($meta_keys as $meta_key) {
            $imgs_metas_are_good[$meta_key] = 0;
            $imgs_metas_are_not_good[$meta_key] = 0;
        }

        $post_types = MetaSeo_Content_List_Table::get_post_types();
        $query = "SELECT `ID`,`post_content`
					FROM $wpdb->posts
					WHERE `post_type` IN ($post_types)
					AND `post_content` <> ''
					AND `post_content` LIKE '%<img%>%' 
					ORDER BY ID";

        $posts = $wpdb->get_results($query);
        if (count($posts) > 0) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $upload_dir = wp_upload_dir();

            foreach ($posts as $post) {
                $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
                if (empty($meta_analysis))
                    $meta_analysis = array();
                $dom = $doc->loadHTML($post->post_content);
                $tags = $doc->getElementsByTagName('img');
                foreach ($tags as $tag) {
                    $img_src = $tag->getAttribute('src');

                    if (!preg_match('/\.(jpg|png|gif)$/i', $img_src, $matches)) {
                        continue;
                    }

                    $img_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $img_src);
                    if (!file_exists($img_path)) {
                        continue;
                    }

                    $width = $tag->getAttribute('width');
                    $height = $tag->getAttribute('height');
                    if (list($real_width, $real_height) = @getimagesize($img_path)) {
                        $ratio_origin = $real_width / $real_height;
                        //Check if img tag is missing with/height attribute value or not
                        if (!$width && !$height) {
                            $width = $real_width;
                            $height = $real_height;
                        } elseif ($width && !$height) {
                            $height = $width * (1 / $ratio_origin);
                        } elseif ($height && !$width) {
                            $width = $height * ($ratio_origin);
                        }

                        if (($real_width <= $width && $real_height <= $height) || (!empty($meta_analysis) && !empty($meta_analysis['imgresize']))) {
                            $imgs_are_good++;
                        }
                        foreach ($meta_keys as $meta_key) {

                            if (trim($tag->getAttribute($meta_key)) || (!empty($meta_analysis) && !empty($meta_analysis['imgalt']))) {
                                $imgs_metas_are_good[$meta_key] ++;
                            }
                        }
                    }

                    $imgs++;
                }
            }

            //Report analytic of images optimization
            $imgs_metas = ceil(($imgs_metas_are_good['alt'] + $imgs_metas_are_good['title']) / 2);
            $response['imgs_statis'][0] = $imgs_are_good;
            $response['imgs_statis'][1] = $imgs;
            $response['imgs_metas_statis'][0] = $imgs_metas;
            $response['imgs_metas_statis'][1] = $imgs;

            if (!empty($imgs)) {
                $percent_iresizing = ceil($imgs_are_good / $imgs * 100);
            } else {
                $percent_iresizing = 100;
            }
            $response['imgs_statis'][2] = $percent_iresizing;
            if (!empty($imgs)) {
                $percent_imeta = ceil($imgs_metas / $imgs * 100);
            } else {
                $percent_imeta = 100;
            }
            $response['imgs_metas_statis'][2] = $percent_imeta;
        }

        return $response;
    }
    
    /*
     * Display rank of site
     */
    public function displayRank($url) {
        $rank = $this->getRank($url);
        if ($rank !== '') {
            echo $rank;
        } else {
            echo __('We can\'t get rank of this site from Alexa.com!', 'wp-meta-seo');
        }
    }
    
    /*
     * Get rank of site
     */
    public function getRank($url) {
        if (!function_exists('curl_version')) {
            if (!$content = @file_get_contents($url)) {
                return '';
            }
        } else {
            if (!is_array($url)) {
                $url = array($url);
            }
            $contents = $this->get_contents($url);
            $content = $contents[0];
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        @$doc->loadHTML($content);
        $doc->preserveWhiteSpace = false;

        $finder = new DOMXPath($doc);
        $classname = 'note-no-data';
        $nodes = $finder->query("//section[contains(@class, '$classname')]");
        if ($nodes->length < 1) {
            $classname = 'rank-row';
            $nodes = $finder->query("//div[contains(@class, '$classname')]");
        }

        $tmp_dom = new DOMDocument();
        foreach ($nodes as $key => $node) {
            $tmp_dom->appendChild($tmp_dom->importNode($node, true));
        }

        $html = trim($tmp_dom->saveHTML());
        $html = str_replace('We don\'t have', __('Alexa doesn\'t have', 'wp-meta-seo'), $html);
        $html = str_replace('Get Certified', '', $html);
        $html = str_replace('"/topsites/countries', '"http://www.alexa.com/topsites/countries', $html);
        return $html;
    }
    
    /*
     * Get content a file
     */
    public function get_contents($urls) {
        $mh = curl_multi_init();
        $curl_array = array();
        $useragent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36';
        foreach ($urls as $i => $url) {
            $curl_array[$i] = curl_init($url);
            curl_setopt($curl_array[$i], CURLOPT_URL, $url);
            curl_setopt($curl_array[$i], CURLOPT_USERAGENT, $useragent); // set user agent
            curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl_array[$i], CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl_array[$i], CURLOPT_ENCODING, "UTF-8");
            curl_multi_add_handle($mh, $curl_array[$i]);
        }

        $running = NULL;
        do {
            usleep(10000);
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $contents = array();
        foreach ($urls as $i => $url) {
            $content = curl_multi_getcontent($curl_array[$i]);
            $contents[$i] = $content;
        }

        foreach ($urls as $i => $url) {
            curl_multi_remove_handle($mh, $curl_array[$i]);
        }
        curl_multi_close($mh);
        return $contents;
    }

    /**
     * update option dashboard
     */
    public static function update_dashboard($name){
        $options_dashboard = get_option('options_dashboard');
        MetaSeo_Dashboard::_wpms_update_option_dash($options_dashboard,$name);
        $options_dashboard = get_option('options_dashboard');
        $results = $options_dashboard[$name];
        return $results;
    }

    /**
     *  get Count posts
     */
    public static function getCountPost(){
        global $wpdb;
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        $post_types = "'" . implode("', '", $post_types) . "'";
        $states = get_post_stati(array('show_in_admin_all_list' => true));
        $states['trash'] = 'trash';
        $all_states = "'" . implode("', '", $states) . "'";
        $total_posts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status IN ($all_states) AND post_type IN ($post_types)");
        return $total_posts;
    }

    /*
     * get params meta title filled for dashboard
     */
    public static function wpms_metatitle(){
        $total_posts = MetaSeo_Dashboard::getCountPost();
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        if (!empty($post_types['attachment']))
            unset($post_types['attachment']);
        $results = array(0, array(0, (int)$total_posts));
        $args = array(
            'posts_per_page' => -1,
            'post_type'  => $post_types,
            'meta_key'   => '_metaseo_metatitle',
            'meta_query' => array(
                array(
                    'key'     => '_metaseo_metatitle',
                    'value'   => '',
                    'compare' => '!=',
                ),
            ),
        );
        $query = new WP_Query( $args );
        $posts = $query->get_posts();
        $metatitle_filled = 0;
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
                if (empty($meta_analysis))
                    $meta_analysis = array();

                $meta_title = get_post_meta($post->ID, '_metaseo_metatitle', true);
                if (($meta_title != '' && strlen($meta_title) <= self::$meta_title_length ) || (!empty($meta_analysis) && !empty($meta_analysis['metatitle']))) {
                    $metatitle_filled++;
                }
            }

            $results = array(ceil($metatitle_filled / $total_posts * 100), array($metatitle_filled, (int)$total_posts));
        }

        return $results;
    }

    /*
     * get html meta title filled for dashboard
     */
    public static function wpms_dash_metatitle(){
        $results = MetaSeo_Dashboard::update_dashboard('metatitle');
        if(isset($_POST['type']) && $_POST['type'] == 'dashboard_widgets'){
            wp_send_json($results);
        }
        ob_start();
        require_once WPMETASEO_PLUGIN_DIR . 'inc/pages/dashboard/meta_title.php';
        $html = ob_get_contents();
        ob_end_clean();
        wp_send_json($html);
    }

    /*
     * get params meta description filled for dashboard
     */
    public static function wpms_metadesc(){
        $total_posts = MetaSeo_Dashboard::getCountPost();
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        if (!empty($post_types['attachment']))
            unset($post_types['attachment']);
        $results = array(0, array(0, $total_posts));
        $args = array(
            'posts_per_page' => -1,
            'post_type'  => $post_types,
            'meta_key'   => '_metaseo_metadesc',
            'meta_query' => array(
                array(
                    'key'     => '_metaseo_metadesc',
                    'value'   => '',
                    'compare' => '!=',
                ),
            ),
        );
        $query = new WP_Query( $args );
        $posts = $query->get_posts();
        $metadesc_filled = 0;
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
                if (empty($meta_analysis))
                    $meta_analysis = array();

                $meta_desc = get_post_meta($post->ID, '_metaseo_metadesc', true);
                if (($meta_desc != '' && strlen($meta_desc) <= self::$meta_desc_length ) || (!empty($meta_analysis) && !empty($meta_analysis['metadesc']))) {
                    $metadesc_filled++;
                }
            }

            $results = array(ceil($metadesc_filled / $total_posts * 100), array($metadesc_filled, $total_posts));
        }

        return $results;
    }

    /*
     * Return html description filled for dashboard
     */
    public static function wpms_dash_metadesc(){
        $results = MetaSeo_Dashboard::update_dashboard('metadesc');
        if(isset($_POST['type']) && $_POST['type'] == 'dashboard_widgets'){
            wp_send_json($results);
        }
        ob_start();
        require_once WPMETASEO_PLUGIN_DIR . 'inc/pages/dashboard/meta_desc.php';
        $html = ob_get_contents();
        ob_end_clean();
        wp_send_json($html);
    }

    /*
     * Return link_meta for dashboard
     */
    public static function wpms_linkmeta(){
        global $wpdb;
        $mcount_link = 0;
        $mlink_complete = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."wpms_links WHERE meta_title !=''");
        $mcount_link = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."wpms_links WHERE type !='404_automaticaly'");

        if ($mcount_link == 0) {
            $link_percent = 100;
        } else {
            $link_percent = ceil($mlink_complete / $mcount_link * 100);
        }

        $results = array($link_percent, array($mlink_complete, $mcount_link));
        return $results;
    }


    /*
     * Return html link_meta for dashboard
     */
    public static function wpms_dash_linkmeta(){
        $results = MetaSeo_Dashboard::wpms_linkmeta();
        if(isset($_POST['type']) && $_POST['type'] == 'dashboard_widgets'){
            wp_send_json($results);
        }
        ob_start();
        require_once WPMETASEO_PLUGIN_DIR . 'inc/pages/dashboard/link_meta.php';
        $html = ob_get_contents();
        ob_end_clean();
        wp_send_json($html);
    }

    /*
     * Return permalink for dashboard
     */
    public static function wpms_permalink(){
        $permalink = 50;
        $permalink_structure = get_option('permalink_structure');
        if (strpos($permalink_structure, 'postname') == false && strpos($permalink_structure, 'category') == false) {
            $permalink = 0;
        } else if (strpos($permalink_structure, 'postname') == true && strpos($permalink_structure, 'category') == true) {
            $permalink = 100;
        } else if (strpos($permalink_structure, 'postname') == true || strpos($permalink_structure, 'category') == true) {
            $permalink = 50;
        }
        return $permalink;
    }

    /*
     * Return html permalink for dashboard
     */
    public static function wpms_dash_permalink(){
        $permalink = MetaSeo_Dashboard::wpms_permalink();
        if(isset($_POST['type']) && $_POST['type'] == 'dashboard_widgets'){
            wp_send_json($permalink);
        }
        ob_start();
        require_once WPMETASEO_PLUGIN_DIR . 'inc/pages/dashboard/permalink.php';
        $html = ob_get_contents();
        ob_end_clean();
        wp_send_json($html);
    }

    /*
     * Return count new content updated for dashboard
     */
    public static function wpms_newcontent(){
        $total_posts = MetaSeo_Dashboard::getCountPost();
        $newcontent_args = array(
            'date_query' => array(
                array(
                    'column' => 'post_modified_gmt',
                    'after' => '30 days ago'
                )
            ),
            'posts_per_page' => -1,
            'post_type' => array('post', 'page'),
        );

        $newcontent = new WP_Query($newcontent_args);

        if (count($newcontent->get_posts()) >= $total_posts) {
            $count_new = 100;
        } else {
            $count_new = ceil(count($newcontent->get_posts()) / $total_posts * 100);
        }
        $results = array($count_new, array(count($newcontent->get_posts()), $total_posts));
        return $results;
    }

    /*
     * Return html new content updated for dashboard
     */
    public static function wpms_dash_newcontent(){
        $results = MetaSeo_Dashboard::update_dashboard('newcontent');
        if(isset($_POST['type']) && $_POST['type'] == 'dashboard_widgets'){
            wp_send_json($results);
        }
        ob_start();
        require_once WPMETASEO_PLUGIN_DIR . 'inc/pages/dashboard/new_content.php';
        $html = ob_get_contents();
        ob_end_clean();
        wp_send_json($html);
    }
    
    /*
     * Return count link 404 , count link 404 is redirected , percent
     */
    public static function get_404_link() {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpms_links WHERE (broken_internal=%d OR broken_indexed=%d) ", array(1, 1));
        $count_404 = $wpdb->get_var($sql);

        $sql = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpms_links WHERE link_url_redirect != '' AND (broken_internal=%d OR broken_indexed=%d) ", array(1, 1));
        $count_404_redirected = $wpdb->get_var($sql);
        if ($count_404 != 0) {
            $percent = ceil($count_404_redirected / $count_404 * 100);
        } else {
            $percent = 100;
        }
        return array('count_404' => $count_404, 'count_404_redirected' => $count_404_redirected, 'percent' => $percent);
    }
    
    /*
     * Return count image is optimized
     */
    public function wpms_getImages_optimizer() {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM INFORMATION_SCHEMA.TABLES
            WHERE table_name = %s AND TABLE_SCHEMA = %s",array($wpdb->prefix . 'wpio_images',$wpdb->dbname));
        $row = $wpdb->get_results($sql);
        if(!empty($row)){
            $query = 'SELECT distinct file FROM ' . $wpdb->prefix . 'wpio_images';
            $files = $wpdb->get_results($query);
            $image_optimize = 0;
            foreach ($files as $file) {
                if (file_exists(str_replace('/', DIRECTORY_SEPARATOR, ABSPATH . $file->file))) {
                    $image_optimize++;
                }
            }
            return $image_optimize;
        }else{
            return 0;
        }
    }
    
    /*
     * Get count image
     */
    public function wpms_getImages_count() {
        $wpio_settings = get_option('_wpio_settings');
        $include_folders = $wpio_settings['wpio_api_include'];
        $allowedPath = explode(',', $include_folders);
        $images = array();
        $image_optimize = $this->wpms_getImages_optimizer();

        $allowed_ext = array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'pdf');
        $min_size = (int) $wpio_settings['wpio_api_minfilesize'] * 1024;
        $max_size = (int) $wpio_settings['wpio_api_maxfilesize'] * 1024;
        if ($max_size == 0)
            $max_size = 5 * 1024 * 1024;
        $count_image = 0;
        $scan_dir = str_replace('/', DIRECTORY_SEPARATOR, ABSPATH);
        foreach (new RecursiveIteratorIterator(new IgnorantRecursiveDirectoryIterator($scan_dir)) as $filename) {
            if (!in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $allowed_ext)) {
                continue;
            }

            $count_image++;
        }

        if ($count_image == 0) {
            $precent = 0;
        } else {
            $precent = ceil($image_optimize / $count_image * 100);
        }
        return array('image_optimize' => $image_optimize, 'count_image' => $count_image, 'percent' => $precent);
    }

    /**
     * get meta data dashboard
     */
    public static function getMetaDataDashBoard($name){
        switch ($name){
            case 'metatitle':
                $results = MetaSeo_Dashboard::wpms_metatitle();
                break;
            case 'metadesc':
                $results = MetaSeo_Dashboard::wpms_metadesc();
                break;
            case 'newcontent':
                $results = MetaSeo_Dashboard::wpms_newcontent();
                break;
            case 'image_meta':
                $results = MetaSeo_Dashboard::moptimizationChecking();
                break;
        }

        return $results;
    }

    /**
     * update time dashboard update
     */
    public static function _wpms_dash_last_update($name){
        if($name == 'metadesc'){
            update_option('_wpms_dash_last_update',time());
        }
    }

    /**
     * update option dashboard
     */
    public static function _wpms_update_option_dash($options_dashboard,$name){
        if(!empty($options_dashboard) && is_array($options_dashboard)){
            if (empty($options_dashboard[$name])) {
                $results = MetaSeo_Dashboard::getMetaDataDashBoard($name);
                $options_dashboard[$name] = $results;
                update_option('options_dashboard',$options_dashboard);
                MetaSeo_Dashboard::_wpms_dash_last_update($name);
            }else{
                $option_last_update_post = get_option('wpms_last_update_post');
                $option_last_dash_update = get_option('_wpms_dash_last_update');
                if(!empty($option_last_update_post) && $option_last_update_post > $option_last_dash_update){
                    $results = MetaSeo_Dashboard::getMetaDataDashBoard($name);
                    $options_dashboard[$name] = $results;
                    update_option('options_dashboard',$options_dashboard);
                    MetaSeo_Dashboard::_wpms_dash_last_update($name);
                }
            }
        }else{
            $results = MetaSeo_Dashboard::getMetaDataDashBoard($name);
            $options_dashboard[$name] = $results;
            update_option('options_dashboard',$options_dashboard);
            MetaSeo_Dashboard::_wpms_dash_last_update($name);
        }
    }



    public static function wpms_dash_imgsmeta() {
        global $wpdb;
        $imgs = 0;
        $imgs_metas = array('alt' => 0, 'title' => 0);
        $imgs_are_good = 0;
        $imgs_metas_are_good = array();
        $meta_keys = array('alt', 'title');
        $response = array(
            'imgs_statis' => array(0, 0 ,100),
            'imgs_metas_statis' => array(0, 0 ,100),
        );

        $options_dashboard = get_option('options_dashboard');
        $option_last_update_post = get_option('wpms_last_update_post');
        $option_last_dash_update = get_option('_wpms_dash_last_update');
        if(!empty($options_dashboard) && is_array($options_dashboard) && !empty($options_dashboard['image_meta']) && $option_last_update_post < $option_last_dash_update){
            $results = $options_dashboard['image_meta'];
            if(isset($_POST['type']) && $_POST['type'] == 'dashboard_widgets'){
                $results['status'] = true;
                wp_send_json($results);
            }
            ob_start();
            require_once WPMETASEO_PLUGIN_DIR . 'inc/pages/dashboard/imgsresize.php';
            $html_imgresize = ob_get_contents();
            ob_end_clean();

            ob_start();
            require_once WPMETASEO_PLUGIN_DIR . 'inc/pages/dashboard/imgsmeta.php';
            $html_imgsmeta = ob_get_contents();
            ob_end_clean();
            wp_send_json(array('status' => true ,'html_imgresize' => $html_imgresize , 'html_imgsmeta' => $html_imgsmeta));
        }

        foreach ($meta_keys as $meta_key) {
            $imgs_metas_are_good[$meta_key] = 0;
            $imgs_metas_are_not_good[$meta_key] = 0;
        }

        $limit = 50;
        $offset = ($_POST['page']-1)*$limit;
        $post_types = MetaSeo_Content_List_Table::get_post_types();
        $query = "SELECT `ID`,`post_content`
					FROM $wpdb->posts
					WHERE `post_type` IN ($post_types)
					AND `post_content` <> ''
					AND `post_content` LIKE '%<img%>%' 
					ORDER BY ID LIMIT $limit OFFSET $offset";

        $posts = $wpdb->get_results($query);
        if (count($posts) > 0) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $upload_dir = wp_upload_dir();

            foreach ($posts as $post) {
                $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
                if (empty($meta_analysis))
                    $meta_analysis = array();
                $dom = $doc->loadHTML($post->post_content);
                $tags = $doc->getElementsByTagName('img');
                foreach ($tags as $tag) {
                    $img_src = $tag->getAttribute('src');

                    if (!preg_match('/\.(jpg|png|gif)$/i', $img_src, $matches)) {
                        continue;
                    }

                    $img_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $img_src);
                    if (!file_exists($img_path)) {
                        continue;
                    }

                    $width = $tag->getAttribute('width');
                    $height = $tag->getAttribute('height');
                    if (list($real_width, $real_height) = @getimagesize($img_path)) {
                        $ratio_origin = $real_width / $real_height;
                        //Check if img tag is missing with/height attribute value or not
                        if (!$width && !$height) {
                            $width = $real_width;
                            $height = $real_height;
                        } elseif ($width && !$height) {
                            $height = $width * (1 / $ratio_origin);
                        } elseif ($height && !$width) {
                            $width = $height * ($ratio_origin);
                        }

                        if (($real_width <= $width && $real_height <= $height) || (!empty($meta_analysis) && !empty($meta_analysis['imgresize']))) {
                            $imgs_are_good++;
                        }
                        foreach ($meta_keys as $meta_key) {

                            if (trim($tag->getAttribute($meta_key)) || (!empty($meta_analysis) && !empty($meta_analysis['imgalt']))) {
                                $imgs_metas_are_good[$meta_key] ++;
                            }
                        }
                    }

                    $imgs++;
                }
            }

            //Report analytic of images optimization
            $c_imgs_metas = ceil(($imgs_metas_are_good['alt'] + $imgs_metas_are_good['title']) / 2);
            $response['imgs_statis'][0] = $imgs_are_good + (int)$_POST['imgs_statis'];
            $response['imgs_statis'][1] = $imgs + (int)$_POST['imgs_count'];
            $response['imgs_metas_statis'][0] = $c_imgs_metas + (int)$_POST['imgs_metas_statis'];
            $response['imgs_metas_statis'][1] = $imgs + (int)$_POST['imgs_count'];
            $response['imgs_count'] = $imgs + (int)$_POST['imgs_count'];
            $response['page'] = (int)$_POST['page'];
        }else{
            if (!empty($_POST['imgs_count'])) {
                $percent_iresizing = ceil($_POST['imgs_statis'] / $_POST['imgs_count'] * 100);
            } else {
                $percent_iresizing = 100;
            }
            $response['imgs_statis'][2] = $percent_iresizing;
            if (!empty($_POST['imgs_count'])) {
                $percent_imeta = ceil($_POST['imgs_metas_statis'] / $_POST['imgs_count'] * 100);
            } else {
                $percent_imeta = 100;
            }

            $response['imgs_metas_statis'][2] = $percent_imeta;
            $options_dashboard['image_meta'] = array(
                'imgs_statis' => array($_POST['imgs_statis'],$_POST['imgs_count'],$percent_iresizing),
                'imgs_metas_statis' => array($_POST['imgs_metas_statis'],$_POST['imgs_count'],$percent_imeta)
            );

            if(!empty($options_dashboard) && is_array($options_dashboard)){
                if (empty($options_dashboard['image_meta'])) {
                    update_option('options_dashboard',$options_dashboard);
                    MetaSeo_Dashboard::_wpms_dash_last_update('image_meta');
                }else{
                    $option_last_update_post = get_option('wpms_last_update_post');
                    $option_last_dash_update = get_option('_wpms_dash_last_update');
                    if((!empty($option_last_update_post) && $option_last_update_post > $option_last_dash_update) || empty($option_last_update_post)){
                        update_option('options_dashboard',$options_dashboard);
                        MetaSeo_Dashboard::_wpms_dash_last_update('image_meta');
                    }
                }
            }else{
                update_option('options_dashboard',$options_dashboard);
                MetaSeo_Dashboard::_wpms_dash_last_update('image_meta');
            }

            $options_dashboard = get_option('options_dashboard');
            $results = $options_dashboard['image_meta'];
            if(isset($_POST['type']) && $_POST['type'] == 'dashboard_widgets'){
                $results['status'] = true;
                wp_send_json($results);
            }
            ob_start();
            require_once WPMETASEO_PLUGIN_DIR . 'inc/pages/dashboard/imgsresize.php';
            $html_imgresize = ob_get_contents();
            ob_end_clean();

            ob_start();
            require_once WPMETASEO_PLUGIN_DIR . 'inc/pages/dashboard/imgsmeta.php';
            $html_imgsmeta = ob_get_contents();
            ob_end_clean();
            wp_send_json(array('status' => true ,'html_imgresize' => $html_imgresize , 'html_imgsmeta' => $html_imgsmeta));
        }

        wp_send_json($response);
    }
}
