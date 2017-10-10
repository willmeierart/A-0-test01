<?php
/**
 * Class WPMSEO_Snippet_Preview
 *
 * Generates a Google Search snippet preview.
 *
 * Takes a $post, $title and $description
 *
 */

class WPMSEO_Snippet_Preview {

    protected $content;
    protected $options;
    protected $post;
    protected $title;
    protected $description;
    protected $date = '';
    protected $url;
    protected $slug = '';

    public function __construct($post, $title, $description) {
        $this->post = $post;
        $this->title = esc_html($title);
        $this->description = esc_html($description);

        $this->set_date();
        $this->set_url();
        $this->set_content();
    }
    
    /**
    * Getter for $this->content
    *
    * @return string html for snippet preview
    */
    public function get_content() {
        return $this->content;
    }

    /*
    * Sets date if available
    */
    protected function set_date() {
        if (is_object($this->post) && isset($this->options['showdate-' . $this->post->post_type]) && $this->options['showdate-' . $this->post->post_type] === true) {
            $date = $this->get_post_date();
            $this->date = '<span class="date">' . $date . ' - </span>';
        }
    }
    
    /**
    * Retrieves a post date when post is published, or return current date when it's not.
    *
    * @return string
    *
    */
    protected function get_post_date() {
        if (isset($this->post->post_date) && $this->post->post_status == 'publish') {
            $date = date_i18n('j M Y', strtotime($this->post->post_date));
        } else {
            $date = date_i18n('j M Y');
        }

        return (string) $date;
    }
    
    /**
    * Generates the url that is displayed in the snippet preview.
    */
    protected function set_url() {
        $this->url = str_replace(array('http://', 'https://'), '', get_bloginfo('url')) . '/';
        $this->set_slug();
    }
    
    /**
    * Sets the slug and adds it to the url if the post has been published and the post name exists.
    *
    * If the post is set to be the homepage the slug is also not included.
    */
    protected function set_slug() {
        $frontpage_post_id = (int) ( get_option('page_on_front') );

        if (is_object($this->post) && isset($this->post->post_name) && $this->post->post_name !== '' && $this->post->ID !== $frontpage_post_id) {
            $this->slug = sanitize_title($this->title);
            $this->url .= esc_html($this->slug);
        }
    }
    
    /**
    * Generates the html for the snippet preview and assign it to $this->content.
    */
    protected function set_content() {
        $content = <<<HTML
<div id="wpmseosnippet">
<a class="title" id="wpmseosnippet_title" href="#">$this->title</a>
<span class="url">$this->url</span>
<p class="desc">$this->date<span class="autogen"></span><span class="content">$this->description</span></p>
</div>
HTML;
        $this->set_content_through_filter($content);
    }
    
    /**
    * Sets the html for the snippet preview through a filter
    *
    * @param string $content Content string.
    */
    protected function set_content_through_filter($content) {
        $properties = get_object_vars($this);
        $properties['desc'] = $properties['description'];
        $this->content = apply_filters('wpmseo_snippet', $content, $this->post, $properties);
    }

}
