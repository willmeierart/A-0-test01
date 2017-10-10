<?php
/**
 * This class generates the metabox on the edit post / page as well as contains all page analysis functionality.
 */

require_once( WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-meta.php' );
require_once( WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-snippet-preview.php' );

class WPMSEO_Metabox extends WPMSEO_Meta {

    public $perc_score = 0;

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('admin_enqueue_scripts', array($this, 'metaseo_enqueue'));
        add_action('wp_insert_post', array($this, 'metaseo_save_postdata'));
        add_action('edit_attachment', array($this, 'metaseo_save_postdata'));
        add_action('add_attachment', array($this, 'metaseo_save_postdata'));
        add_action('admin_init', array($this, 'translate_meta_boxes'));
    }
    
    /*
     * Update post meta data
     */
    function metaseo_save_postdata($post_id) {

        if ($post_id === null) {
            return false;
        }

        if (wp_is_post_revision($post_id)) {
            $post_id = wp_is_post_revision($post_id);
        }

        clean_post_cache($post_id);
        $post = get_post($post_id);

        if (!is_object($post)) {
            return false;
        }

        do_action('wpmseo_save_compare_data', $post);

        $meta_boxes = apply_filters('wpmseo_save_metaboxes', array());
        $meta_boxes = array_merge($meta_boxes, $this->get_meta_field_defs('general', $post->post_type), $this->get_meta_field_defs('advanced'), $this->get_meta_field_defs('social'));
        foreach ($meta_boxes as $key => $meta_box) {
            $data = null;
            if ('checkbox' === $meta_box['type']) {
                $data = isset($_POST[self::$form_prefix . $key]) ? 'on' : 'off';
            } else {
                if (isset($_POST[self::$form_prefix . $key])) {
                    $data = $_POST[self::$form_prefix . $key];
                }
            }
            if (isset($data)) {
                self::set_value($key, $data, $post_id);
            }
        }
        do_action('wpmseo_saved_postdata');
    }

    /**
    * Translate text strings for use in the meta box
    */
    public static function translate_meta_boxes() {
        self::$meta_fields['general']['snippetpreview']['title'] = __('Results preview', 'wp-meta-seo');
        self::$meta_fields['general']['snippetpreview']['help'] = sprintf(__('This is a preview of what your content will looks like in search engine results: title, description and URL', 'wp-meta-seo'), '<a target="_blank" href="https://www.joomunited.com/wordpress-products/wpms">', '</a>');

        self::$meta_fields['general']['title']['title'] = __('Search engine title', 'wp-meta-seo');
        self::$meta_fields['general']['title']['description'] = sprintf('<span id="metaseo_wpmseo_title-length">%s</span>', self::$meta_length_reason);
        self::$meta_fields['general']['title']['help'] = __('This is the title of your content that may be displayed in search engine results (meta title). By default it’s the content title (page title, post title…). 69 characters max allowed.', 'wp-meta-seo');

        $settings = get_option('_metaseo_settings');
        if (isset($settings['metaseo_showkeywords']) && $settings['metaseo_showkeywords'] == 1) {
            self::$meta_fields['general']['keywords']['title'] = __('Search engine keywords', 'wp-meta-seo');
            self::$meta_fields['general']['keywords']['description'] = '<span id="metaseo_wpmseo_keywords-length"></span>';
            self::$meta_fields['general']['keywords']['help'] = __('This is the keywords of your content that may be displayed in search engine results (meta keywords).', 'wp-meta-seo');
        } else {
            unset(self::$meta_fields['general']['keywords']);
        }


        self::$meta_fields['general']['desc']['title'] = __('Search engine description', 'wp-meta-seo');
        self::$meta_fields['general']['desc']['description'] = sprintf('<span id="metaseo_wpmseo_desc-length">%s</span>', self::$meta_length_reason);
        self::$meta_fields['general']['desc']['help'] = __('The description of your content that may be displayed in search engine results aka meta description. By default search engine take an excerpt from your content (depending on the search query). 156 characters max allowed.', 'wp-meta-seo');

        self::$meta_fields['social']['opengraph-title']['title'] = __('Facebook Title', 'wp-meta-seo');
        self::$meta_fields['social']['opengraph-title']['description'] = __('Custom title to display when sharing this content on facebook, content title override', 'wp-meta-seo');

        self::$meta_fields['social']['opengraph-desc']['title'] = __('Facebook Description', 'wp-meta-seo');
        self::$meta_fields['social']['opengraph-desc']['description'] = __('Custom description to display when sharing this content on facebook, content description override', 'wp-meta-seo');

        self::$meta_fields['social']['opengraph-image']['title'] = __('Facebook Image', 'wp-meta-seo');
        self::$meta_fields['social']['opengraph-image']['description'] = __('Custom image to display when sharing this content on facebook, content description override, recommended size is 1200px x 630px', 'wp-meta-seo');

        self::$meta_fields['social']['twitter-title']['title'] = __('Twitter Title', 'wp-meta-seo');
        self::$meta_fields['social']['twitter-title']['description'] = __('Custom title to display when sharing this content on twitter, content title override', 'wp-meta-seo');

        self::$meta_fields['social']['twitter-desc']['title'] = __('Twitter Description', 'wp-meta-seo');
        self::$meta_fields['social']['twitter-desc']['description'] = __('Custom description to display when sharing this content on twitter, content description override', 'wp-meta-seo');

        self::$meta_fields['social']['twitter-image']['title'] = __('Twitter Image', 'wp-meta-seo');
        self::$meta_fields['social']['twitter-image']['description'] = __('Custom image to display when sharing this content on facebook, content description override, recommended min size 440px X 220px', 'wp-meta-seo');

        do_action('wpmseo_tab_translate');
    }
    
    /* load script and style */
    public function metaseo_enqueue() {
        global $pagenow;
        if ((!in_array($pagenow, array(
                    'post-new.php',
                    'post.php',
                    'edit.php',
                        ), true) && apply_filters('wpmseo_always_register_metaboxes_on_admin', false) === false ) || $this->is_metabox_hidden() === true
        ) {
            return;
        }


        $color = get_user_meta(get_current_user_id(), 'admin_color', true);
        if ('' == $color || in_array($color, array('classic', 'fresh'), true) === false) {
            $color = 'fresh';
        }

        if ($pagenow != 'edit.php') {
            if (0 != get_queried_object_id()) {
                wp_enqueue_media(array('post' => get_queried_object_id())); // Enqueue files needed for upload functionality.
            }
            wp_enqueue_style('m-metabox-tabs', plugins_url('css/metabox-tabs.css', WPMSEO_FILE), array(), WPMSEO_VERSION);
            wp_enqueue_style('m-style-qtip', plugins_url('css/jquery.qtip.css', WPMSEO_FILE), array(), WPMSEO_VERSION);
            wp_enqueue_script('jquery-qtip', plugins_url('js/jquery.qtip.min.js', WPMSEO_FILE), array('jquery'), '2.2.1', true);
            wp_enqueue_script('m-wp-seo-metabox', plugins_url('js/wp-metaseo-metabox.js', WPMSEO_FILE), array('jquery', 'jquery-ui-core',), WPMSEO_VERSION, true);
            wp_enqueue_script('mwpseo-admin-media', plugins_url('js/wp-metaseo-admin-media.js', WPMSEO_FILE), array('jquery', 'jquery-ui-core',), WPMSEO_VERSION, true);
            wp_enqueue_script('metaseo-cliffpyles', plugins_url('js/cliffpyles.js', WPMSEO_FILE), array('jquery'), WPMSEO_VERSION, true);
            wp_localize_script('m-wp-seo-metabox', 'wpmseoMetaboxL10n', $this->meta_seo_localize_script());
            $localize = $this->metaseo_localize_media_script();
            wp_localize_script('mwpseo-admin-media', 'wpmseoMediaL10n', $localize);
            wp_localize_script('metaseo-cliffpyles', 'wpmscliffpyles', $localize);
        }
    }
    
    /* Localize a script */
    public function metaseo_localize_media_script() {
        $this->settings = array(
            "metaseo_title_home" => "",
            "metaseo_desc_home" => "",
            "metaseo_showfacebook" => "",
            "metaseo_showtwitter" => "",
            "metaseo_twitter_card" => "summary",
            "metaseo_showkeywords" => 0,
            "metaseo_showtmetablock" => 1,
            "metaseo_showsocial" => 1,
            "metaseo_seovalidate" => 0
        );
        $settings = get_option('_metaseo_settings');

        if (is_array($settings)) {
            $this->settings = array_merge($this->settings, $settings);
        }

        return array(
            'choose_image' => __('Use Image', 'wp-meta-seo'),
            'use_validate' => $this->settings['metaseo_seovalidate']
        );
    }
    
    /* Localize a script */
    public function meta_seo_localize_script() {
        $post = $this->get_metabox_post();

        if ((!is_object($post) || !isset($post->post_type) ) || $this->is_metabox_hidden($post->post_type) === true) {
            return array();
        }

        $options = get_option('wpmseo_titles');

        $date = '';
        if (isset($options['showdate-' . $post->post_type]) && $options['showdate-' . $post->post_type] === true) {
            $date = $this->get_post_date($post);

            self::$meta_length = ( self::$meta_length - ( strlen($date) + 5 ) );
            self::$meta_length_reason = __(' (because of date display)', 'wp-meta-seo');
        }

        self::$meta_length_reason = apply_filters('wpmseo_desc_length_reason', self::$meta_length_reason, $post);
        self::$meta_length = apply_filters('wpmseo_desc_length', self::$meta_length, $post);

        unset($date);

        $title_template = '';
        if (isset($options['title-' . $post->post_type]) && $options['title-' . $post->post_type] !== '') {
            $title_template = $options['title-' . $post->post_type];
        }

        // If there's no title template set, use the default, otherwise title preview won't work.
        if ($title_template == '') {
            $title_template = '%%title%% - %%sitename%%';
        }

        $desc_template = '';
        if (isset($options['desc-' . $post->post_type]) && $options['desc-' . $post->post_type] !== '') {
            $desc_template = $options['desc-' . $post->post_type];
        }

        $sample_permalink = get_sample_permalink($post->ID);
        $sample_permalink = str_replace('%page', '%post', $sample_permalink[0]);

        $cached_replacement_vars = array();

        $vars_to_cache = array(
            'sitedesc',
            'sep',
            'page',
        );

        foreach ($vars_to_cache as $var) {
            $cached_replacement_vars[$var] = $var;
        }

        $cached_replacement_vars['sitename'] = get_option('blogname');
        $array_keyword = array(
            'field_prefix' => self::$form_prefix,
            'choose_image' => __('Use Image', 'wp-meta-seo'),
            'wpmseo_meta_desc_length' => self::$meta_length,
            'wpmseo_meta_title_length' => self::$meta_title_length,
            'wpmseo_meta_keywords_length' => self::$meta_keywords_length,
            'wpmseo_title_template' => $title_template,
            'wpmseo_desc_template' => $desc_template,
            'wpmseo_permalink_template' => $sample_permalink,
            'wpmseo_keyword_suggest_nonce' => wp_create_nonce('wpmseo-get-suggest'),
            'wpmseo_replace_vars_nonce' => wp_create_nonce('wpmseo-replace-vars'),
            'no_parent_text' => __('(no parent)', 'wp-meta-seo'),
            'show_keywords' => 0
        );
        $settings = get_option('_metaseo_settings');
        if (isset($settings['metaseo_showkeywords']) && $settings['metaseo_showkeywords'] == 1) {
            $array_keyword['show_keywords'] = 1;
        }

        return array_merge($cached_replacement_vars, $array_keyword);
    }
    
    /**
    * Test whether the metabox should be hidden either by choice of the admin or because
    * the post type is not a public post type
    *
    * @since 1.5.0
    *
    * @param  string $post_type (optional) The post type to test, defaults to the current post post_type.
    *
    * @return  bool        Whether or not the meta box (and associated columns etc) should be hidden
    */
    function is_metabox_hidden($post_type = null) {
        if (!isset($post_type)) {
            if (isset($GLOBALS['post']) && ( is_object($GLOBALS['post']) && isset($GLOBALS['post']->post_type) )) {
                $post_type = $GLOBALS['post']->post_type;
            } elseif (isset($_GET['post_type']) && $_GET['post_type'] !== '') {
                $post_type = sanitize_text_field($_GET['post_type']);
            }
        }

        if (isset($post_type)) {
            // Don't make static as post_types may still be added during the run.
            $cpts = get_post_types(array('public' => true), 'names');
            $options = get_option('wpmseo_titles');

            return ( ( isset($options['hideeditbox-' . $post_type]) && $options['hideeditbox-' . $post_type] === true ) || in_array($post_type, $cpts) === false );
        }
        return false;
    }
    
    /**
    * Adds the Meta SEO meta box to the edit boxes in the edit post / page  / cpt pages.
    */
    public function add_meta_box() {
        $post_types = get_post_types(array('public' => true));

        if (is_array($post_types) && $post_types !== array()) {
            foreach ($post_types as $post_type) {
                if ($this->is_metabox_hidden($post_type) === false) {
                    add_meta_box('wpmseo_meta', __('WP Meta SEO - Page optimization', 'wp-meta-seo'), array(
                        $this,
                        'meta_box',
                            ), $post_type, 'normal', apply_filters('wpmseo_metabox_prio', 'core'));
                }
            }
        }
    }
    
    /**
    * Output the meta box
    */
    function meta_box() {
        $post = $this->get_metabox_post();
        $default_settings = array(
            "metaseo_title_home" => "",
            "metaseo_desc_home" => "",
            "metaseo_showfacebook" => "",
            "metaseo_showtwitter" => "",
            "metaseo_twitter_card" => "summary",
            "metaseo_showtmetablock" => 1,
            "metaseo_showsocial" => 1
        );
        $settings = get_option('_metaseo_settings');
        if (is_array($settings)) {
            $default_settings = array_merge($default_settings, $settings);
        }
        ?>
        <div class="wpmseo-metabox-tabs-div">
            <ul class="wpmseo-metabox-tabs" id="wpmseo-metabox-tabs">
                <li class="general">
                    <a class="wpmseo_tablink" data-link="wpmseo_general"><?php _e('SEO Page optimization', 'wp-meta-seo'); ?></a>
                </li>
                <?php
                if(is_plugin_active(WPMSEO_ADDON_FILENAME)){
                    $service = $this->wpms_server_webmaster();
                    $check_connected = false;
                    if(!empty($service)){
                        // get domain
                        $option = get_option(WPMS_GSC, array('profile' => ''));
                        if (!empty($option['profile'])) {
                            $check_connected = true;
                        }
                    }
                    if($check_connected){
                        echo '<li class="gsckeywords">';
                        echo '<a class="wpmseo_tablink" data-link="wpmseo_gsc_keywords">'.__('Search console keywords', 'wp-meta-seo').'</a>';
                        echo '</li>';
                    }
                }
                ?>


                <?php if ((isset($default_settings['metaseo_showsocial']) && $default_settings['metaseo_showsocial'] == 1)): ?>
                <li class="social">
                    <a class="wpmseo_tablink" data-link="wpmseo_social"><?php _e('Social for search engine', 'wp-meta-seo') ?></a>
                </li>
                <?php endif; ?>
                <?php do_action('wpmseo_tab_header'); ?>
            </ul>
            <?php
            $content = '';
            if (is_object($post) && isset($post->post_type)) {
                foreach ($this->get_meta_field_defs('general', $post->post_type) as $key => $meta_field) {
                    $content .= $this->do_meta_box($meta_field, $key);
                }
                unset($key, $meta_field);
            }
            $this->do_tab('general', __('General', 'wp-meta-seo'), $content);
            if(is_plugin_active(WPMSEO_ADDON_FILENAME)){
                if($check_connected){
                    apply_filters('wpmsaddon_google_search_keywords' , '' ,$post->post_content,$service);
                }
            }


            $content = '';
            foreach ($this->get_meta_field_defs('social') as $meta_key => $meta_field) {
                $content .= $this->do_meta_box($meta_field, $meta_key);
            }

            if ((isset($default_settings['metaseo_showsocial']) && $default_settings['metaseo_showsocial'] == 1)) {
                $this->do_tab('social', __('Social', 'wp-meta-seo'), $content);
            }

            do_action('wpmseo_tab_content');

        echo '</div>';
    }

    public function wpms_server_webmaster() {
        try {
            if (!class_exists('Wpms_Api_Google', false)) {
                require_once WPMETASEO_ADDON_PLUGIN_DIR . '/inc/google-api/' . 'class-api-google.php';
                new Wpms_Api_Google();
            }
        } catch (Exception $exception) {

        }

        $conn = get_option('wpms_gsc_connect');
        if (!empty($conn['googleClientId']) && !empty($conn['googleClientSecret'])) {
            $client_id = $conn['googleClientId'];
            $client_secret = $conn['googleClientSecret'];
        } else {
            $client_id = WPMSEO_ADDON_CLIENT_ID;
            $client_secret = WPMSEO_ADDON_CLIENT_SECRET;
        }

        $gsc = array(
            'application_name' => 'WP Meta SEO',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => 'urn:ietf:wg:oauth:2.0:oob',
            'scopes' => array('https://www.googleapis.com/auth/webmasters'),
        );

        $this->client = new Wpms_Api_Google_Client($gsc, 'wpms-gsc', 'https://www.googleapis.com/webmasters/v3/');
        if($this->client->getAccessToken() != null) {
            $service = new Wpms_Google_Service_Webmasters ($this->client);
            return $service;
        }
        return false;
    }
    
    /*
     * return html field settings
     */
    function metaseo_create_field($data_title, $alt, $dashicon, $label, $value_hidden) {
        $output = '<div class="metaseo_analysis metaseo_tool" data-title="' . $data_title . '" alt="' . $alt . '"><i class="metaseo-dashicons material-icons dashicons-before" style="' . ($dashicon == 'done' ? 'color:#46B450' : 'color:#FFB900') . '">' . $dashicon . '</i>' . $label . '</div>';
        $output .= '<input type="hidden" class="wpms_analysis_hidden" name="wpms[' . $data_title . ']" value="' . $value_hidden . '">';
        return $output;
    }

    /* load param heading title */
    function metaseo_check_headingtitle($post, $meta_title, $tooltip_page, $meta_analysis) {
        $words_post_title = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', strtolower($post->post_title), -1, PREG_SPLIT_NO_EMPTY);
        if ($post->post_content == '') {
            $output = $this->metaseo_create_field('heading_title', $tooltip_page['title_in_heading'], 'warning', __('Page title word in content heading', 'wp-meta-seo'), 0);
            return $output;
        }
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $content = apply_filters('the_content', $post->post_content);
        if ($dom->loadHTML($content)) {
            // Extracting the specified elements from the web page
            $tags_h1 = $dom->getElementsByTagName('h1');
            $tags_h2 = $dom->getElementsByTagName('h2');
            $tags_h3 = $dom->getElementsByTagName('h3');
            $tags_h4 = $dom->getElementsByTagName('h4');
            $tags_h5 = $dom->getElementsByTagName('h5');
            $tags_h6 = $dom->getElementsByTagName('h6');

            $test = false;
            if (count($tags_h1) == 0 && count($tags_h2) == 0 && count($tags_h3) == 0 && count($tags_h4) == 0 && count($tags_h5) == 0 && count($tags_h6) == 0) {
                $output = $this->metaseo_create_field('heading_title', $tooltip_page['title_in_heading'], 'warning', __('Page title word in content heading', 'wp-meta-seo'), 0);
            } else {
                if (!empty($tags_h1)) {
                    foreach ($tags_h1 as $order => $tagh1) {
                        $words_tagh1 = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', utf8_decode(strtolower($tagh1->nodeValue)), -1, PREG_SPLIT_NO_EMPTY);
                        if (is_array($words_tagh1) && is_array($words_post_title)) {
                            foreach ($words_tagh1 as $mh) {
                                if (in_array($mh, $words_post_title) && $mh != '') {
                                    $test = true;
                                    $output = $this->metaseo_create_field('heading_title', $tooltip_page['title_in_heading'], 'done', __('Page title word in content heading', 'wp-meta-seo'), 1);
                                    $this->perc_score++;
                                    return $output;
                                }
                            }
                        }
                    }
                }

                if (!empty($tags_h2)) {
                    foreach ($tags_h2 as $order => $tagh2) {
                        $words_tagh2 = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', utf8_decode(strtolower($tagh2->nodeValue)), -1, PREG_SPLIT_NO_EMPTY);
                        if (is_array($words_tagh2) && is_array($words_post_title)) {
                            foreach ($words_tagh2 as $mh) {
                                if (in_array($mh, $words_post_title) && $mh != '') {
                                    $test = true;
                                    $output = $this->metaseo_create_field('heading_title', $tooltip_page['title_in_heading'], 'done', __('Page title word in content heading', 'wp-meta-seo'), 1);
                                    $this->perc_score++;
                                    return $output;
                                }
                            }
                        }
                    }
                }

                if (!empty($tags_h3)) {
                    foreach ($tags_h3 as $order => $tagh3) {
                        $words_tagh3 = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', utf8_decode(strtolower($tagh3->nodeValue)), -1, PREG_SPLIT_NO_EMPTY);
                        if (is_array($words_tagh3) && is_array($words_post_title)) {
                            foreach ($words_tagh3 as $mh) {
                                if (in_array($mh, $words_post_title) && $mh != '') {
                                    $test = true;
                                    $output = $this->metaseo_create_field('heading_title', $tooltip_page['title_in_heading'], 'done', __('Page title word in content heading', 'wp-meta-seo'), 1);
                                    $this->perc_score++;
                                    return $output;
                                }
                            }
                        }
                    }
                }

                if (!empty($tags_h4)) {
                    foreach ($tags_h4 as $order => $tagh4) {
                        $words_tagh4 = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', utf8_decode(strtolower($tagh4->nodeValue)), -1, PREG_SPLIT_NO_EMPTY);
                        if (is_array($words_tagh4) && is_array($words_post_title)) {
                            foreach ($words_tagh4 as $mh) {
                                if (in_array($mh, $words_post_title) && $mh != '') {
                                    $test = true;
                                    $output = $this->metaseo_create_field('heading_title', $tooltip_page['title_in_heading'], 'done', __('Page title word in content heading', 'wp-meta-seo'), 1);
                                    $this->perc_score++;
                                    return $output;
                                }
                            }
                        }
                    }
                }

                if (!empty($tags_h5)) {
                    foreach ($tags_h5 as $order => $tagh5) {
                        $words_tagh5 = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', utf8_decode(strtolower($tagh5->nodeValue)), -1, PREG_SPLIT_NO_EMPTY);
                        if (is_array($words_tagh5) && is_array($words_post_title)) {
                            foreach ($words_tagh5 as $mh) {
                                if (in_array($mh, $words_post_title) && $mh != '') {
                                    $test = true;
                                    $output = $this->metaseo_create_field('heading_title', $tooltip_page['title_in_heading'], 'done', __('Page title word in content heading', 'wp-meta-seo'), 1);
                                    $this->perc_score++;
                                    return $output;
                                }
                            }
                        }
                    }
                }

                if (!empty($tags_h6)) {
                    foreach ($tags_h6 as $order => $tagh6) {
                        $words_tagh6 = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', utf8_decode(strtolower($tagh6->nodeValue)), -1, PREG_SPLIT_NO_EMPTY);
                        if (is_array($words_tagh6) && is_array($words_post_title)) {
                            foreach ($words_tagh6 as $mh) {
                                if (in_array($mh, $words_post_title) && $mh != '') {
                                    $test = true;
                                    $output = $this->metaseo_create_field('heading_title', $tooltip_page['title_in_heading'], 'done', __('Page title word in content heading', 'wp-meta-seo'), 1);
                                    $this->perc_score++;
                                    return $output;
                                }
                            }
                        }
                    }
                }
            }

            if ($test || (!empty($meta_analysis) && !empty($meta_analysis['heading_title']))) {
                $output = $this->metaseo_create_field('heading_title', $tooltip_page['title_in_heading'], 'done', __('Page title word in content heading', 'wp-meta-seo'), 1);
                $this->perc_score++;
            } else {
                $output = $this->metaseo_create_field('heading_title', $tooltip_page['title_in_heading'], 'warning', __('Page title word in content heading', 'wp-meta-seo'), 0);
            }
        } else {
            $output = $this->metaseo_create_field('heading_title', $tooltip_page['title_in_heading'], 'warning', __('Page title word in content heading', 'wp-meta-seo'), 0);
        }

        return $output;
    }

    /* load param word content */
    function metaseo_check_content($post, $tooltip_page, $meta_analysis) {
        $content = apply_filters('the_content', $post->post_content);
        $words_meta_title = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', strtolower($post->post_title), -1, PREG_SPLIT_NO_EMPTY);
        $words_post_content = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', strtolower(strip_tags($content)), -1, PREG_SPLIT_NO_EMPTY);
        $test1 = false;
        if (is_array($words_meta_title) && is_array($words_post_content)) {
            foreach ($words_meta_title as $mtitle) {
                if (in_array($mtitle, $words_post_content) && $mtitle != '') {
                    $test1 = true;
                    break;
                }
            }
        } else {
            $test1 = false;
        }

        if ($test1 || (!empty($meta_analysis) && !empty($meta_analysis['content_title']))) {
            $output = $this->metaseo_create_field('content_title', $tooltip_page['title_in_content'], 'done', __('Page title word in content', 'wp-meta-seo'), 1);
            $this->perc_score++;
        } else {
            $output = $this->metaseo_create_field('content_title', $tooltip_page['title_in_content'], 'warning', __('Page title word in content', 'wp-meta-seo'), 0);
        }

        return $output;
    }
    
    /* load param page url */
    function metaseo_check_pageurl($post, $tooltip_page, $meta_analysis) {
        $pageurl = get_permalink($post->ID);
        $info = pathinfo($pageurl);

        $page_on_front = get_option('page_on_front');
        if(!empty($page_on_front) && $page_on_front == $post->ID){
            $output = $this->metaseo_create_field('pageurl', $tooltip_page['page_url'], 'done', __('Page url matches with page title', 'wp-meta-seo'), 1);
            $this->perc_score++;
        }else{
            if ($info['filename'] == sanitize_title($post->post_title) || (!empty($meta_analysis) && !empty($meta_analysis['pageurl']))) {
                $output = $this->metaseo_create_field('pageurl', $tooltip_page['page_url'], 'done', __('Page url matches with page title', 'wp-meta-seo'), 1);
                $this->perc_score++;
            } else {
                $output = $this->metaseo_create_field('pageurl', $tooltip_page['page_url'], 'warning', __('Page url matches with page title', 'wp-meta-seo'), 0);
            }
        }

        return $output;
    }
    
    /* load param meta title */
    function metaseo_check_metatitle($meta_title, $tooltip_page, $meta_analysis) {
        if (($meta_title != '' && strlen($meta_title) <= self::$meta_title_length) || (!empty($meta_analysis) && !empty($meta_analysis['metatitle']))) {
            $output = $this->metaseo_create_field('metatitle', $tooltip_page['meta_title'], 'done', __('Meta title filled', 'wp-meta-seo'), 1);
            $this->perc_score++;
        } else {
            $output = $this->metaseo_create_field('metatitle', $tooltip_page['meta_title'], 'warning', __('Meta title filled', 'wp-meta-seo'), 0);
        }
        return $output;
    }

    /* load param meta description */
    function metaseo_check_metadesc($meta_desc, $tooltip_page, $meta_analysis) {
        if (($meta_desc != '' && strlen($meta_desc) <= self::$meta_length) || (!empty($meta_analysis) && !empty($meta_analysis['metadesc']))) {
            $output = $this->metaseo_create_field('metadesc', $tooltip_page['meta_desc'], 'done', __('Meta description filled', 'wp-meta-seo'), 1);
            $this->perc_score++;
        } else {
            $output = $this->metaseo_create_field('metadesc', $tooltip_page['meta_desc'], 'warning', __('Meta description filled', 'wp-meta-seo'), 0);
        }
        return $output;
    }
    
    /* load param image resize */
    function metaseo_check_imageresize($content, $tooltip_page, $meta_analysis) {
        if ($content == '') {
            $output = $this->metaseo_create_field('imgresize', $tooltip_page['image_resize'], 'done', __('Wrong image resize', 'wp-meta-seo'), 1);
            $this->perc_score++;
            return $output;
        }
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $content = apply_filters('the_content', $content);
        if ($dom->loadHTML($content)) {
            // Extracting the specified elements from the web page
            $tags = $dom->getElementsByTagName('img');
            $img_wrong = false;
            foreach ($tags as $order => $tag) {
                $src = $tag->getAttribute('src');
                $b = strrpos($src, '-');
                $e = strrpos($src, '.');
                $string_wh = substr($src, $b + 1, $e - $b - 1);
                $array_wh = explode('x', $string_wh);
                if (!empty($array_wh[0]) && !empty($array_wh[1])) {
                    if (((int) $array_wh[0] != (int) $tag->getAttribute('width')) || ((int) $array_wh[1] != (int) $tag->getAttribute('height'))) {
                        $img_wrong = true;
                    }
                }
            }

            if ($img_wrong == false || (!empty($meta_analysis) && !empty($meta_analysis['imgresize']))) {
                $output = $this->metaseo_create_field('imgresize', $tooltip_page['image_resize'], 'done', __('Wrong image resize', 'wp-meta-seo'), 1);
                $this->perc_score++;
            } else {
                $output = $this->metaseo_create_field('imgresize', $tooltip_page['image_resize'], 'warning', __('Wrong image resize', 'wp-meta-seo'), 0);
            }
        } else {
            $output = $this->metaseo_create_field('imgresize', $tooltip_page['image_resize'], 'warning', __('Wrong image resize', 'wp-meta-seo'), 0);
        }
        return $output;
    }
    
    /* load param image alt */
    function metaseo_check_imagealt($content, $tooltip_page, $meta_analysis) {
        if ($content == '') {
            $output = $this->metaseo_create_field('imgalt', $tooltip_page['image_alt'], 'done', __('Image have meta title or alt', 'wp-meta-seo'), 1);
            $this->perc_score++;
            return $output;
        }
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        if ($dom->loadHTML($content)) {
            // Extracting the specified elements from the web page
            $tags = $dom->getElementsByTagName('img');
            $img_wrong = false;
            foreach ($tags as $order => $tag) {
                $image_title = $tag->getAttribute('title');
                $image_alt = $tag->getAttribute('alt');
                if ($image_title == '' || $image_alt == '') {
                    $img_wrong = true;
                }
            }

            if ($img_wrong == false || (!empty($meta_analysis) && !empty($meta_analysis['imgalt']))) {
                $output = $this->metaseo_create_field('imgalt', $tooltip_page['image_alt'], 'done', __('Image have meta title or alt', 'wp-meta-seo'), 1);
                $this->perc_score++;
            } else {
                $output = $this->metaseo_create_field('imgalt', $tooltip_page['image_alt'], 'warning', __('Image have meta title or alt', 'wp-meta-seo'), 0);
            }
        } else {
            $output = $this->metaseo_create_field('imgalt', $tooltip_page['image_alt'], 'warning', __('Image have meta title or alt', 'wp-meta-seo'), 0);
        }
        return $output;
    }
    
    /* 
     * Load page analysis 
     */
    function metaseo_page_analysis($post) {
        $tooltip_page = array();
        $tooltip_page['title_in_heading'] = __('Check if a word of this content title is also in a title heading (h1, h2...)', 'wp-meta-seo');
        $tooltip_page['title_in_content'] = __('Check if a word of this content title is also in the text', 'wp-meta-seo');
        $tooltip_page['page_url'] = __('Does the page title match with the permalink (URL structure)', 'wp-meta-seo');
        $tooltip_page['meta_title'] = __('Is the meta title of this page filled?', 'wp-meta-seo');
        $tooltip_page['meta_desc'] = __('Is the meta description of this page filled?', 'wp-meta-seo');
        $tooltip_page['image_resize'] = __('Check for image HTML resizing in content (usually image resized using handles)', 'wp-meta-seo');
        $tooltip_page['image_alt'] = __('Check for image Alt text and title', 'wp-meta-seo');

        $meta_title = get_post_meta($post->ID, '_metaseo_metatitle', true);
        $meta_desc = get_post_meta($post->ID, '_metaseo_metadesc', true);
        $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
        if (empty($meta_analysis))
            $meta_analysis = array();
        $headingtitle = $this->metaseo_check_headingtitle($post, $meta_title, $tooltip_page, $meta_analysis);
        $contenttitle = $this->metaseo_check_content($post, $tooltip_page, $meta_analysis);
        $output_pageurl = $this->metaseo_check_pageurl($post, $tooltip_page, $meta_analysis);
        $output_metatitle = $this->metaseo_check_metatitle($meta_title, $tooltip_page, $meta_analysis);
        $output_metadesc = $this->metaseo_check_metadesc($meta_desc, $tooltip_page, $meta_analysis);
        $output_imagewrong = $this->metaseo_check_imageresize($post->post_content, $tooltip_page, $meta_analysis);
        $output_imagealt = $this->metaseo_check_imagealt($post->post_content, $tooltip_page, $meta_analysis);

        $output = '';
        $circliful = ceil(100 * ($this->perc_score) / 7);
        $output .= '<div style="width:100%;float:left;">';
        $output .= '<div class="metaseo_left">
            <div class="metaseo-progress-bar" data-post_id="' . $post->ID . '">
                      <canvas id="inactiveProgress" class="metaseo-progress-inactive" height="275px" width="275px"></canvas>
              <canvas id="activeProgress" class="metaseo-progress-active"  height="275px" width="275px"></canvas>
              <p>0%</p>
            </div>
            <input type="hidden" id="progressController" value="' . $circliful . '" />
                <input type="hidden" id="metaseo_alanysis_ok" value="' . $this->perc_score . '" />
          </div>';

        $output .= '<div class="metaseo_right">';
        $output .= $headingtitle . $contenttitle . $output_pageurl . $output_metatitle . $output_metadesc . $output_imagewrong . $output_imagealt;
        $output .= '</div>';
        $output .= '</div>';
        return $output;
    }
    
    /**
    * Get a sample permalink based off of the post name.
    */
    function get_sample_permalink($post) {
        if (!function_exists('get_sample_permalink')) {
            include_once( ABSPATH . 'wp-admin/includes/post.php' );
        }

        return get_sample_permalink($post);
    }
    
    /* Display html content for current tab */
    public function do_tab($id, $heading, $content) {
        global $post;
        ?>
        <div class="wpmseotab <?php echo esc_attr($id) ?>">
            <?php if ($id == 'general'): ?>
                <p class="reload_analysis"><span class="spinner" style="float: left;"></span><input type="button" name="reload_analysis" id="reload_analysis" class="button button-primary" value="<?php _e('Reload analysis', 'wp-meta-seo'); ?>"></p>
            <?php endif; ?>
            <table class="form-table">
                <?php echo $content; ?>
            </table>
            <?php
            if ($id == 'general') {
                echo $this->metaseo_page_analysis($post);
                $settings = get_option('_metaseo_settings');
                if (!empty($settings['metaseo_follow'])) {
                    $page_follow = get_post_meta($post->ID, '_metaseo_metafollow', true);
                    $select_follow = '<select class="metaseo_metabox_follow" data-post_id="' . $post->ID . '">';
                    if ($page_follow == 'nofollow') {
                        $select_follow .= '<option value="follow">' . __('Follow', 'wp-meta-seo') . '</option>';
                        $select_follow .= '<option selected value="nofollow">' . __('Nofollow', 'wp-meta-seo') . '</option>';
                    } else {
                        $select_follow .= '<option selected value="follow">' . __('Follow', 'wp-meta-seo') . '</option>';
                        $select_follow .= '<option value="nofollow">' . __('Nofollow', 'wp-meta-seo') . '</option>';
                    }
                    $select_follow .= '</select>';
                    echo '<p><span class="wpmslabel">' . __('Follow', 'wp-meta-seo') . '<i class="material-icons alignright metaseo_help" id="deschelp" alt="' . __('Nofollow provides a way for webmasters to tell search engines: don\'t follow this link. So it may influence the link target’s ranking', 'wp-meta-seo') . '" style="color:#32373C" data-hasqtip="2">chat_bubble</i></span>' . $select_follow . '</p>';
                }

                if (!empty($settings['metaseo_index'])) {
                    $page_index = get_post_meta($post->ID, '_metaseo_metaindex', true);
                    $select_index = '<select class="metaseo_metabox_index" data-post_id="' . $post->ID . '">';
                    if ($page_index == 'noindex') {
                        $select_index .= '<option value="index">' . __('Index', 'wp-meta-seo') . '</option>';
                        $select_index .= '<option selected value="noindex">' . __('Noindex', 'wp-meta-seo') . '</option>';
                    } else {
                        $select_index .= '<option selected value="index">' . __('Index', 'wp-meta-seo') . '</option>';
                        $select_index .= '<option value="noindex">' . __('Noindex', 'wp-meta-seo') . '</option>';
                    }

                    $select_index .= '</select>';
                    echo '<p><span class="wpmslabel">' . __('Index', 'wp-meta-seo') . '<i class="material-icons alignright metaseo_help" id="deschelp" alt="' . __('Allow search engines robots to index this content, as default your content is indexed', 'wp-meta-seo') . '" style="color:#32373C" data-hasqtip="2">chat_bubble</i></span>' . $select_index . '</p>';
                }
            }
            ?>
        </div>
        <?php
    }
    
    /* Display html content for current tab */
    function do_meta_box($meta_field_def, $key = '') {
        wp_enqueue_style('metaseo-google-icon');
        $content = '';
        $esc_form_key = esc_attr(self::$form_prefix . $key);
        $post = $this->get_metabox_post();
        $meta_value = self::get_value($key, $post->ID);

        $class = '';
        if (isset($meta_field_def['class']) && $meta_field_def['class'] !== '') {
            $class = ' ' . $meta_field_def['class'];
        }

        $placeholder = '';
        if (isset($meta_field_def['placeholder']) && $meta_field_def['placeholder'] !== '') {
            $placeholder = $meta_field_def['placeholder'];
        }

        switch ($meta_field_def['type']) {
            case 'snippetpreview':
                $content .= $this->snippet();
                break;

            case 'text':
                $ac = '';
                if (isset($meta_field_def['autocomplete']) && $meta_field_def['autocomplete'] === false) {
                    $ac = 'autocomplete="off" ';
                }
                if ($placeholder !== '') {
                    $placeholder = ' placeholder="' . esc_attr($placeholder) . '"';
                }
                $content .= '<input type="text"' . $placeholder . ' id="' . $esc_form_key . '" ' . $ac . 'name="' . $esc_form_key . '" value="' . esc_attr($meta_value) . '" class="large-text' . $class . '"/><br />';
                break;

            case 'textarea':
                $rows = 3;
                if (isset($meta_field_def['rows']) && $meta_field_def['rows'] > 0) {
                    $rows = $meta_field_def['rows'];
                }
                $content .= '<textarea class="large-text' . $class . '" rows="' . esc_attr($rows) . '" id="' . $esc_form_key . '" name="' . $esc_form_key . '">' . esc_textarea($meta_value) . '</textarea>';
                break;

            case 'select':
                if (isset($meta_field_def['options']) && is_array($meta_field_def['options']) && $meta_field_def['options'] !== array()) {
                    $content .= '<select name="' . $esc_form_key . '" id="' . $esc_form_key . '" class="metaseo' . $class . '">';
                    foreach ($meta_field_def['options'] as $val => $option) {
                        $selected = selected($meta_value, $val, false);
                        $content .= '<option ' . $selected . ' value="' . esc_attr($val) . '">' . esc_html($option) . '</option>';
                    }
                    unset($val, $option, $selected);
                    $content .= '</select>';
                }
                break;

            case 'multiselect':
                if (isset($meta_field_def['options']) && is_array($meta_field_def['options']) && $meta_field_def['options'] !== array()) {
                    $selected_arr = $meta_value;
                    if ('meta-robots-adv' === $key) {
                        $selected_arr = explode(',', $meta_value);
                    }

                    if (!is_array($selected_arr)) {
                        $selected_arr = (array) $selected_arr;
                    }

                    $options_count = count($meta_field_def['options']);

                    $content .= '<select multiple="multiple" size="' . esc_attr($options_count) . '" style="height: ' . esc_attr(( $options_count * 20 ) + 4) . 'px;" name="' . $esc_form_key . '[]" id="' . $esc_form_key . '" class="metaseo' . $class . '">';
                    foreach ($meta_field_def['options'] as $val => $option) {
                        $selected = '';
                        if (in_array($val, $selected_arr)) {
                            $selected = ' selected="selected"';
                        }
                        $content .= '<option ' . $selected . ' value="' . esc_attr($val) . '">' . esc_html($option) . '</option>';
                    }
                    $content .= '</select>';
                    unset($val, $option, $selected, $selected_arr, $options_count);
                }
                break;

            case 'checkbox':
                $checked = checked($meta_value, 'on', false);
                $expl = ( isset($meta_field_def['expl']) ) ? esc_html($meta_field_def['expl']) : '';
                $content .= '<label for="' . $esc_form_key . '"><input type="checkbox" id="' . $esc_form_key . '" name="' . $esc_form_key . '" ' . $checked . ' value="on" class="metaseo' . $class . '"/> ' . $expl . '</label><br />';
                unset($checked, $expl);
                break;

            case 'radio':
                if (isset($meta_field_def['options']) && is_array($meta_field_def['options']) && $meta_field_def['options'] !== array()) {
                    foreach ($meta_field_def['options'] as $val => $option) {
                        $checked = checked($meta_value, $val, false);
                        $content .= '<input type="radio" ' . $checked . ' id="' . $esc_form_key . '_' . esc_attr($val) . '" name="' . $esc_form_key . '" value="' . esc_attr($val) . '"/> <label for="' . $esc_form_key . '_' . esc_attr($val) . '">' . esc_html($option) . '</label> ';
                    }
                    unset($val, $option, $checked);
                }
                break;

            case 'upload':
                $content .= '<input id="' . $esc_form_key . '" type="text" size="36" class="' . $class . '" name="' . $esc_form_key . '" value="' . esc_attr($meta_value) . '" />';
                $content .= '<input id="' . $esc_form_key . '_button" class="wpmseo_image_upload_button button" type="button" value="' . __('Upload Image', 'wp-meta-seo') . '" />';
                break;
        }


        $html = '';
        if ($content === '') {
            $content = apply_filters('wpmseo_do_meta_box_field_' . $key, $content, $meta_value, $esc_form_key, $meta_field_def, $key);
        }

        if ($content !== '') {

            $label = esc_html($meta_field_def['title']);
            if (in_array($meta_field_def['type'], array(
                        'snippetpreview',
                        'radio',
                        'checkbox',
                            ), true) === false
            ) {
                $label = '<label for="' . $esc_form_key . '">' . $label . ':</label>';
            }

            $help = '';
            if (isset($meta_field_def['help']) && $meta_field_def['help'] !== '') {
                $help = '<i class="material-icons alignright metaseo_help" id="' . esc_attr($key . 'help') . '" alt="' . esc_attr($meta_field_def['help']) . '" style="color:#32373C">chat_bubble</i>';
            }

            $html = '
                            <tr>
                                    <th scope="row">' . $label . $help . '</th>
                                    <td>';

            $html .= $content;

            if (isset($meta_field_def['description'])) {
                $html .= '<div>' . $meta_field_def['description'] . '</div>';
            }

            $html .= '
                                    </td>
                            </tr>';
        }
        return $html;
    }

    private function get_metabox_post() {
        if (isset($_GET['post'])) {
            $post_id = (int) $_GET['post'];
            $post = get_post($post_id);
        } else {
            $post = $GLOBALS['post'];
        }

        return $post;
    }

    function snippet() {
        $post = $this->get_metabox_post();
        $title = self::get_value('title', $post->ID);
        $description = self::get_value('desc', $post->ID);

        $snippet_preview = new WPMSEO_Snippet_Preview($post, $title, $description);

        return $snippet_preview->get_content();
    }
}

/* End of class */
