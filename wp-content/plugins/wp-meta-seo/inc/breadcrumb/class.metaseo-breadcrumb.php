<?php

class MetaSeo_Breadcrumb
{
    public $breadcrumbs = array();
    public $breadcrumb_settings = array();
    public $template_no_anchor;

    function __construct()
    {
        $home_title = get_the_title( get_option('page_on_front') );
        if(empty($home_title)) $home_title = get_bloginfo('title');
        $this->breadcrumb_settings = array(
            'separator' => ' &gt; ',
            'include_home' => 1,
            'home_text_default' => 0,
            'home_text' => $home_title,
            'clickable' => 1,
            'apost_post_root' => get_option('page_for_posts'),
            'apost_page_root' => get_option('page_on_front')
        );
        $breadcrumb_settings = get_option('_metaseo_breadcrumbs');
        if (is_array($breadcrumb_settings)) {
            $this->breadcrumb_settings = array_merge($this->breadcrumb_settings, $breadcrumb_settings);
        }
    }

    public function wpms_check_posts()
    {
        global $wp_query;
        //For the front page, as it may also validate as a page, do it first
        if (is_front_page()) {
            global $current_site;
            $site_name = get_option('blogname');
            $this->wpms_add_breadcrumb($site_name, WPMSEO_TEMPLATE_BREADCRUMB, array('home', 'current-item'));
            if (!is_main_site()) {
                $site_name = get_site_option('site_name');
                $template = __('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="Go to %title%." href="%link%" class="%type%"><span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>', 'wp-meta-seo');
                $this->wpms_add_breadcrumb($site_name, $template, array('main-home'), get_home_url($current_site->blog_id));
            }
        } else if (is_singular()) {

            if (is_attachment()) {
                // attachments
                $this->wpms_attachment();
            } else {
                // other post types
                $this->wpms_post(get_post());
            }
        } else if (is_search()) {
            $this->wpms_search();
        } else if (is_author()) {
            $this->wpms_author();
        } else if (is_archive()) {
            $type = $wp_query->get_queried_object();
            $type_str = get_query_var('post_type');
            if (is_array($type_str)) {
                $type_str = reset($type_str);
            }
            //For date based archives
            if (is_date()) {
                $this->wpms_archive_by_date($this->wpms_get_type());
            } else if (is_post_type_archive() && !isset($type->taxonomy) && (!is_numeric($this->breadcrumb_settings['apost_' . $type_str . '_root']))) {
                $this->wpms_archive_by_post_type();
            } else if (is_category() || is_tag() || is_tax()) {
                $this->wpms_archive_by_term();
            }
        } else if (is_404()) {
            $this->wpms_add_breadcrumb('404', WPMSEO_TEMPLATE_BREADCRUMB, array('404', 'current-item'));
        } else {
            $type = $wp_query->get_queried_object();
            if (isset($type->taxonomy)) {
                $this->wpms_archive_by_term();
            }
        }
        // home
        if (!is_front_page()) {
            if(!empty($this->breadcrumb_settings['include_home'])){
                $this->wpms_home();
            }
        }
    }

    public function wpms_breadcrumb_display($return = false, $reverse = false)
    {
        // order breadcrumb
        if ($reverse) {
            ksort($this->breadcrumbs);
        } else {
            krsort($this->breadcrumbs);
        }

        $html = '';
        $position = 1;
        //The main compiling loop
        foreach ($this->breadcrumbs as $key => $breadcrumb) {
            // for reverse has true
            if ($reverse) {
                if ($key > 0) {
                    $html .= $this->breadcrumb_settings['separator'];
                }
            } else {
                if ($position > 1) {
                    $html .= $this->breadcrumb_settings['separator'];
                }
            }

            $html .= $this->wpms_breadcrumb_createhtml($breadcrumb,$position);
            $position++;
        }

        if ($return) {
            return $html; // for return has true
        } else {
            echo $html; // for return has false
        }
    }

    /**
     * create html string
     */
    public function wpms_breadcrumb_createhtml($breadcrumb, $position)
    {
        $params = array(
            '%title%' => esc_attr(strip_tags($breadcrumb['name'])),
            '%link%' => esc_url($breadcrumb['url']),
            '%htitle%' => $breadcrumb['name'],
            '%type%' => $breadcrumb['type'],
            '%ftitle%' => esc_attr(strip_tags($breadcrumb['name'])),
            '%fhtitle%' => $breadcrumb['name'],
            '%position%' => $position
        );
        //The type may be an array, implode it if that is the case
        if (is_array($params['%type%'])) {
            $params['%type%'] = implode(' ', $params['%type%']);
        }

        if(empty($this->breadcrumb_settings['clickable'])){
            return str_replace(array_keys($params), $params, $this->template_no_anchor);
        }else{
            if ($breadcrumb['click']) {
                //Return template
                return str_replace(array_keys($params), $params, $breadcrumb['template']);
            } else {
                //Return template
                return str_replace(array_keys($params), $params, $this->template_no_anchor);
            }
        }


    }

    /**
     * breadcrumb for front page
     */
    public function wpms_front_page()
    {
        global $current_site;
        $site_name = get_option('blogname');
        $this->wpms_add_breadcrumb($site_name, WPMSEO_TEMPLATE_BREADCRUMB, array('home', 'current-item'));
        if (!is_main_site()) {
            $site_name = get_site_option('site_name');
            // Add to breadcrumbs list
            $template = __('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="Go to %title%." href="%link%" class="%type%"><span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>', 'wp-meta-seo');
            $this->wpms_add_breadcrumb($site_name, $template, array('main-home'), get_home_url($current_site->blog_id));
        }
    }

    /**
     * breadcrumb for posts
     */
    public function wpms_post($post)
    {
        if (!($post instanceof WP_Post)) {
            return;
        }

        $arrays = array(
            'name' => get_the_title($post),
            'template' => WPMSEO_TEMPLATE_BREADCRUMB,
            'type' => array('post', 'post-' . $post->post_type, 'current-item'),
            'url' => NULL,
            'id' => $post->ID,
            'click' => false
        );

        if (is_attachment()) {
            $template = __('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="Go to %title%." href="%link%" class="%type%"><span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>', 'wp-meta-seo');
            $arrays['template'] = $template;
            $arrays['url'] = get_permalink($post);
            $arrays['click'] = true;
        }
        $this->breadcrumbs[] = $arrays;
        if ($post->post_type === 'page') {
            $frontpage = get_option('page_on_front');
            if ($post->post_parent && $post->ID != $post->post_parent && $frontpage != $post->post_parent) {

                $this->wpms_post_parents($post->post_parent, $frontpage);
            }
        } else {
            $this->wpms_post_hierarchy($post->ID);
        }
    }

    /*
     * find breadcrumb of parent
     */
    public function wpms_post_parents($id, $frontpage)
    {
        $parent = get_post($id);
        // Add to breadcrumbs list
        $template = __('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="Go to %title%." href="%link%" class="%type%"><span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>', 'wp-meta-seo');
        $this->wpms_add_breadcrumb(get_the_title($id), $template, array('post', 'post-' . $parent->post_type), get_permalink($id), $id);
        if($parent->post_parent >= 0 && $parent->post_parent != false && $id != $parent->post_parent && $frontpage != $parent->post_parent)
        {
            //If valid call this function
            $parent = $this->wpms_post_parents($parent->post_parent, $frontpage);
        }
        return $parent;
    }

    /**
     * breadcrumb for an attachment page.
     */
    public function wpms_attachment()
    {
        $post = get_post();
        // Add to breadcrumbs list
        $this->wpms_add_breadcrumb(get_the_title(), WPMSEO_TEMPLATE_BREADCRUMB, array('post', 'post-attachment', 'current-item'), NULL, $post->ID);
        //Done with the current item, now on to the parents
        $frontpage = get_option('page_on_front');
        if ($post->post_parent >= 0 && $post->post_parent != false && $post->ID != $post->post_parent && $frontpage != $post->post_parent) {
            $parent = get_post($post->post_parent);
            //set the parent's breadcrumb
            $this->wpms_post($parent);
        }
    }

    /**
     * breadcrumb for search
     */
    public function wpms_search()
    {
        $template = __('<span property="itemListElement" typeof="ListItem"><span property="name">Search results for &#39;%htitle%&#39;</span><meta property="position" content="%position%"></span>', 'wp-meta-seo');
        $this->wpms_add_breadcrumb(get_search_query(), $template, array('search', 'current-item'));
    }

    /**
     * breadcrumb for author
     */
    public function wpms_author()
    {
        if (get_query_var('author_name')) {
            $author = get_user_by('slug', get_query_var('author_name'));
        } else {
            $author = get_userdata(get_query_var('author'));
        }
        // array author_name values
        $author_name = array('display_name', 'nickname', 'first_name', 'last_name');
        if (in_array('display_name', $author_name)) {
            // Add to breadcrumbs list
            $template = __('<span property="itemListElement" typeof="ListItem"><span property="name">Articles by: %htitle%</span><meta property="position" content="%position%"></span>', 'wp-meta-seo');
            $this->wpms_add_breadcrumb(get_the_author_meta('display_name', $author->ID), $template, array('author', 'current-item'), NULL, $author->ID);
        }
    }

    /**
     * breadcrumb for an archive by post_type.
     */
    public function wpms_archive_by_post_type()
    {
        $type = $this->wpms_get_type();
        // Add to breadcrumbs list
        $this->wpms_add_breadcrumb(post_type_archive_title('', false), WPMSEO_TEMPLATE_BREADCRUMB, array('archive', 'post-' . $type . '-archive', 'current-item'));
    }

    /**
     * breadcrumb for date
     */
    public function wpms_archive_by_date($type)
    {
        global $wp_query;
        $date_template = __('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="Go to the %title% archives." href="%link%" class="%type%"><span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>', 'wp-meta-seo');
        if (is_day() || is_single()) {
            $arrays = array(
                'name' => get_the_time(_x('d', 'day archive breadcrumb date format', 'wp-meta-seo')),
                'template' => WPMSEO_TEMPLATE_BREADCRUMB,
                'type' => array('archive', 'date-day'),
            );

            if (is_day()) {
                $arrays['type'] = 'current-item';
                $arrays['url'] = NULL;
                $arrays['click'] = false;
            }
            // if is single
            if (is_single()) {
                $arrays['template'] = $date_template;
                $url = get_day_link(get_the_time('Y'), get_the_time('m'), get_the_time('d'));
                $url = $this->wpms_add_post_type_arg($url, $type);
                $arrays['url'] = $url;
                $arrays['click'] = true;
            }

            $this->breadcrumbs[] = $arrays;
        }

        //Now deal with the month breadcrumb
        if (is_month() || is_day() || is_single()) {
            $arrays = array(
                'name' => get_the_time(_x('F', 'month archive breadcrumb date format', 'wp-meta-seo')),
                'template' => WPMSEO_TEMPLATE_BREADCRUMB,
                'type' => array('archive', 'date-month'),
            );

            if (is_month()) {
                $arrays['type'] = 'current-item';
                $arrays['url'] = NULL;
                $arrays['click'] = false;
            }

            if (is_day() || is_single()) {
                $arrays['template'] = $date_template;
                $url = get_month_link(get_the_time('Y'), get_the_time('m'));
                $url = $this->wpms_add_post_type_arg($url, $type);
                $arrays['url'] = $url;
                $arrays['click'] = true;
            }

            $this->breadcrumbs[] = $arrays;
        }


        $arrays = array(
            'name' => get_the_time(_x('Y', 'year archive breadcrumb date format', 'wp-meta-seo')),
            'template' => WPMSEO_TEMPLATE_BREADCRUMB,
            'type' => array('archive', 'date-year'),
        );

        //If this is a year archive, add current-item type
        if (is_year()) {
            $arrays['type'] = 'current-item';
            $arrays['url'] = NULL;
            $arrays['click'] = false;
        }
        // day or month or single
        if (is_day() || is_month() || is_single()) {
            //We're linking, so set the linked template
            $arrays['template'] = $date_template;
            $url = get_year_link(get_the_time('Y'));
            $url = $this->wpms_add_post_type_arg($url, $type);
            $arrays['url'] = $url;
            $arrays['click'] = true;
        }

        $this->breadcrumbs[] = $arrays;
    }

    public function wpms_archive_by_term()
    {
        global $wp_query;
        $term = $wp_query->get_queried_object();
        // Add to breadcrumbs list
        $template = __('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="Go to the %title% category archives." href="%link%" class="%type%"><span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>', 'wp-meta-seo');
        $this->wpms_add_breadcrumb($term->name, $template, array('archive', 'taxonomy', $term->taxonomy, 'current-item'), NULL, $term->term_id);
        //Get parents of current term
        if ($term->parent) {
            $this->term_parents($term->parent, $term->taxonomy);
        }
    }

    public function term_parents($id, $taxonomy)
    {
        //Get the current category
        $term = get_term($id, $taxonomy);
        // Add to breadcrumbs list
        $template = __('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="Go to the %title% category archives." href="%link%" class="%type%"><span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>', 'wp-meta-seo');
        $this->wpms_add_breadcrumb($term->name, $template, array('taxonomy', $taxonomy), $this->wpms_add_post_type_arg(get_term_link($term), NULL, $taxonomy), $id);
        if ($term->parent && $term->parent != $id) {
            $term = $this->term_parents($term->parent, $taxonomy);
        }
        return $term;
    }

    /*
     * add a enlement to lists
     */
    public function wpms_add_breadcrumb($name = '', $template = '', array $type = array(), $url = '', $id = NULL , $click = true)
    {
        $allowed_html = wp_kses_allowed_html('post');
        $tmp = __('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="Go to %title%." href="%link%" class="%type%"><span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>', 'wp-meta-seo');
        $this->template_no_anchor = WPMSEO_TEMPLATE_BREADCRUMB;
        if ($template == NULL) {
            $template = wp_kses($tmp,$allowed_html);
        } else {
            //Loose comparison, evaluates to true if URL is '' or NULL
            if ($url == NULL) {
                $this->template_no_anchor = wp_kses($template, $allowed_html);
                $template = wp_kses($tmp,$allowed_html);
            } else {
                $template = wp_kses($template,$allowed_html);
            }
        }

        // check click or not
        if(empty($this->breadcrumb_settings['clickable'])){
            $click = false;
        }else{
            if ($url == NULL) {
                $click = false;
            } else {
                $click = true;
            }
        }

        // add to array
        $this->breadcrumbs[] = array(
            'name' => $name,
            'template' => $template,
            'type' => $type,
            'url' => $url,
            'id' => $id,
            'click' => $click
        );
    }

    public function wpms_post_hierarchy($id)
    {
        $taxonomy = 'category';
        if (is_taxonomy_hierarchical($taxonomy)) {
            // Get all term of object
            $wpms_object = get_the_terms($id, $taxonomy);
            $potential_parent = 0;
            $term = false;
            // check array
            if (is_array($wpms_object)) {
                $wpms_use_term = key($wpms_object);
                foreach ($wpms_object as $key => $object) {
                    if ($object->parent > 0 && ($potential_parent === 0 || $object->parent === $potential_parent)) {
                        $wpms_use_term = $key;
                        $potential_parent = $object->term_id;
                    }
                }
                $term = $wpms_object[$wpms_use_term];
            }

            if ($term instanceof WP_Term) {
                //Fill out the term hiearchy
                $parent = $this->term_parents($term->term_id, $taxonomy);
            }
        } else {
            $this->post_terms($id, $taxonomy);
        }
    }

    /**
     * Add post type argument to the URL
     */
    public function wpms_add_post_type_arg($url, $type = NULL, $taxonomy = NULL)
    {
        global $wp_taxonomies;
        if ($type == NULL) {
            $type = $this->wpms_get_type();
        }

        // add post_type to url
        $query_arg = (!($taxonomy && $type === $wp_taxonomies[$taxonomy]->object_type[0]) && $type !== 'post');
        if ($query_arg) {
            $url = add_query_arg(array('post_type' => $type), $url);
        }
        return $url;
    }

    /**
     * get post type
     */
    public function wpms_get_type($default = 'post')
    {
        $type = get_query_var('post_type', $default);
        if ($type === '' || is_array($type)) {
            $post = get_post();
            if ($post instanceof WP_Post) {
                $type = $post->post_type;
            } else {
                $type = $default;
            }
        }
        return esc_attr($type);
    }

    /**
     * breadcrumb for the home page.
     */
    public function wpms_home()
    {
        global $current_site;
        //Get the site name
        $site_name = get_option('blogname');
        $template = __('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="Go to %title%." href="%link%" class="%type%"><span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>', 'wp-meta-seo');

        if(!empty($this->breadcrumb_settings['home_text_default'])){
            $title = $this->breadcrumb_settings['home_text'];
        }else{
            $title = $site_name;
        }
        $this->wpms_add_breadcrumb($title, $template, array('home'), get_home_url());
        if (!is_main_site()) {
            //Get the site name
            $site_name = get_site_option('site_name');
            // Add to breadcrumbs list
            $this->wpms_add_breadcrumb($site_name, $template, array('main-home'), get_home_url($current_site->blog_id));
        }
    }
}