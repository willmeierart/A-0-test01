<div id="menu_source_posts" class="wpms_source wpms_source_posts">
    <div class="div_sitemap_check_all">
        <div class="pure-checkbox">
            <input class="sitemap_check_all" data-type="posts" id="wpms_check_all_posts" type="checkbox">
            <label for="wpms_check_all_posts"><?php _e("Check all posts", 'wp-meta-seo'); ?></label>
        </div>
    </div>
    
    <div class="div_sitemap_check_all">
        <div class="pure-checkbox">
            <input class="sitemap_check_all_posts_in_page" data-type="posts" id="wpms_check_all_posts_in_page" type="checkbox">
            <label for="wpms_check_all_posts_in_page"><?php _e("Check all posts in current page", 'wp-meta-seo'); ?></label>
        </div>
    </div>
    
    <div class="div_sitemap_check_all" style="font-weight: bold;">
        <label><?php _e('Public name', 'wp-meta-seo'); ?></label>
        <input type="text" class="public_name_posts" value="<?php echo $metaseo_sitemap->settings_sitemap['wpms_public_name_posts'] ?>">
    </div>
    
    <div class="div_sitemap_check_all wpms_xmp_custom_column" style="font-weight: bold;">
        <label><?php _e('Display in column', 'wp-meta-seo'); ?></label>
        <select class="wpms_display_column wpms_display_column_posts">
            <?php 
                for($i = 1 ; $i <= $metaseo_sitemap->settings_sitemap['wpms_html_sitemap_column'] ; $i++){
                    echo '<option '.(selected($metaseo_sitemap->settings_sitemap['wpms_display_column_posts'], $i)).' value="'.$i.'">'.$metaseo_sitemap->columns[$i].'</option>';
                } 
            ?>
        </select>
    </div>

    <div class="div_sitemap_check_all wpms_xmp_order" style="font-weight: bold;">
        <label><?php _e('Order' , 'wp-meta-seo'); ?></label>
        <select class="wpms_display_order_posts">
            <?php
            for($i = 1 ; $i <= 4 ; $i++){
                echo '<option '.(selected($metaseo_sitemap->settings_sitemap['wpms_display_order_posts'], $i)).' value="'.$i.'">'.$i.'</option>';
            }
            ?>
        </select>
    </div>

    <div id="wrap_sitemap_option_posts" class="wrap_sitemap_option">
        <?php
        $posts = $metaseo_sitemap->wpms_get_posts();
        $check = array();
        $desclink_category_add = __('Add link to category name' , 'wp-meta-seo');
        $desclink_category_remove = __('Remove link to category name' , 'wp-meta-seo');
        foreach ($posts as $post) {
            if(!in_array($post->taxo, $check)){
                $check[] = $post->taxo;
                echo '<div class="wpms_row"><h1>' . $post->taxo . '</h1></div>';
            }
            
            if(in_array($post->cat_ID, $metaseo_sitemap->settings_sitemap['wpms_category_link'])){
                echo '<div class="wpms_row"><h3>';
                echo '<div class="pure-checkbox">';
                echo '<input for="'.$desclink_category_remove.'" class="sitemap_addlink_categories" id="sitemap_addlink_categories" type="checkbox" value="' . $post->cat_ID . '" checked>';
                echo '<label for="sitemap_addlink_categories">'. $post->cat_name .'</label>';
                echo '</div>';
                echo '</h3></div>';
            }else{
                echo '<div class="wpms_row"><h3>';
                echo '<div class="pure-checkbox">';
                echo '<input for="'.$desclink_category_remove.'" class="sitemap_addlink_categories" id="sitemap_addlink_categories" type="checkbox" value="' . $post->cat_ID . '">';
                echo '<label for="sitemap_addlink_categories">'. $post->cat_name .'</label>';
                echo '</div>';
                echo '</h3></div>';
            }
            
            echo '<div class="wpms_row wpms_row_check_all_posts">';
            echo '<div class="pure-checkbox">';
            echo '<input data-category="'.$post->taxo.$post->slug.'" class="sitemap_check_all_posts_categories" id="sitemap_check_all_posts_categories" type="checkbox">';
            echo '<label for="sitemap_check_all_posts_categories">'.__('Select all' , 'wp-meta-seo').'</label>';
            echo '</div>';
            echo '</div>';
            foreach ($post->results as $p) {
                $category = get_the_terms($p, $post->taxo);
                if($category[0]->term_id == $post->cat_ID){
                    if(empty($metaseo_sitemap->settings_sitemap['wpms_sitemap_posts'][$p->ID]['frequency'])){
                        $postfrequency = 'monthly';
                    }else{
                        $postfrequency = $metaseo_sitemap->settings_sitemap['wpms_sitemap_posts'][$p->ID]['frequency'];
                    }
                    if(empty($metaseo_sitemap->settings_sitemap['wpms_sitemap_posts'][$p->ID]['priority'])){
                        $postpriority = '1.0';
                    }else{
                        $postpriority = $metaseo_sitemap->settings_sitemap['wpms_sitemap_posts'][$p->ID]['priority'];
                    }
                    $select_priority = $metaseo_sitemap->wpms_view_select_priority('priority_posts_'.$p->ID,'_metaseo_settings_sitemap[wpms_sitemap_posts][' . $p->ID . '][priority]', $postpriority);
                    $select_frequency = $metaseo_sitemap->wpms_view_select_frequency('frequency_posts_'.$p->ID,'_metaseo_settings_sitemap[wpms_sitemap_posts][' . $p->ID . '][frequency]', $postfrequency);
                    $permalink = get_permalink($p->ID);
                    echo '<div class="wpms_row wpms_row_record">';
                    echo '<div style="float:left;line-height:30px;min-width: 300px;">';
                    if(strlen($p->post_title) > 30){
                        $title = substr($p->post_title,0,30);
                    }else{
                        $title = $p->post_title;
                    }
                    if (isset($metaseo_sitemap->settings_sitemap['wpms_sitemap_posts'][$p->ID]['post_id']) && $metaseo_sitemap->settings_sitemap['wpms_sitemap_posts'][$p->ID]['post_id'] == $p->ID) {
                        echo '<input class="wpms_sitemap_input_link checked" type="hidden" data-type="post" value="'.$permalink.'">';
                        echo '<div class="pure-checkbox">';
                        echo '<input class="cb_sitemaps_posts wpms_xmap_posts '.$post->taxo.$post->slug.'" id="wpms_sitemap_posts_' . $p->ID . '" type="checkbox" name="_metaseo_settings_sitemap[wpms_sitemap_posts]" value="' . $p->ID . '" checked>';
                        echo '<label for="wpms_sitemap_posts_' . $p->ID . '">'. $title .'</label>';
                        echo '</div>';
                    } else {
                        echo '<input class="wpms_sitemap_input_link" type="hidden" data-type="post" value="'.$permalink.'">';
                        echo '<div class="pure-checkbox">';
                        echo '<input class="cb_sitemaps_posts wpms_xmap_posts '.$post->taxo.$post->slug.'" id="wpms_sitemap_posts_' . $p->ID . '" type="checkbox" name="_metaseo_settings_sitemap[wpms_sitemap_posts]" value="' . $p->ID . '">';
                        echo '<label for="wpms_sitemap_posts_' . $p->ID . '">'. $title .'</label>';
                        echo '</div>';
                    }


                    echo '</div>';
                    echo '<div style="margin-left:200px">' . $select_priority . $select_frequency . '</div>';
                    echo '</div>';
                }
            }
        }
        ?>
    </div>
    <div class="holder holder_posts"></div>
</div>