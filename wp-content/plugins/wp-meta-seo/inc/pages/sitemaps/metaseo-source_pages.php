<div id="menu_source_pages" class="wpms_source wpms_source_pages">
    <div class="div_sitemap_check_all">
        <div class="pure-checkbox">
            <input class="sitemap_check_all" data-type="pages" id="wpms_check_all_pages" type="checkbox">
            <label for="wpms_check_all_pages"><?php _e("Check all pages", 'wp-meta-seo'); ?></label>
        </div>
    </div>

    <div class="div_sitemap_check_all">
        <div class="pure-checkbox">
            <input class="sitemap_check_all_posts_in_page" data-type="pages" id="wpms_check_all_posts_in_page_pages" type="checkbox">
            <label for="wpms_check_all_posts_in_page_pages"><?php _e("Check all posts in current page", 'wp-meta-seo'); ?></label>
        </div>
    </div>

    <div class="div_sitemap_check_all" style="font-weight: bold;">
        <label><?php _e('Public name', 'wp-meta-seo'); ?></label>
        <input type="text" class="public_name_pages" value="<?php echo $metaseo_sitemap->settings_sitemap['wpms_public_name_pages'] ?>">
    </div>

    <div class="div_sitemap_check_all wpms_xmp_custom_column" style="font-weight: bold;">
        <label><?php _e('Display in column', 'wp-meta-seo'); ?></label>
        <select class="wpms_display_column wpms_display_column_pages">
            <?php
            for ($i = 1; $i <= $metaseo_sitemap->settings_sitemap['wpms_html_sitemap_column']; $i++) {
                echo '<option ' . (selected($metaseo_sitemap->settings_sitemap['wpms_display_column_pages'], $i)) . ' value="' . $i . '">' . $metaseo_sitemap->columns[$i] . '</option>';
            }
            ?>
        </select>
    </div>

    <div class="div_sitemap_check_all wpms_xmp_order" style="font-weight: bold;">
        <label><?php _e('Order' , 'wp-meta-seo'); ?></label>
        <select class="wpms_display_order_pages">
            <?php
            for($i = 1 ; $i <= 4 ; $i++){
                echo '<option '.(selected($metaseo_sitemap->settings_sitemap['wpms_display_order_pages'], $i)).' value="'.$i.'">'.$i.'</option>';
            }
            ?>
        </select>
    </div>
    <div id="wrap_sitemap_option_pages" class="wrap_sitemap_option">
        <h3><?php _e('Pages', 'wp-meta-seo') ?></h3>
        <?php
        $pages = $metaseo_sitemap->wpms_get_pages();
        foreach ($pages as $page) {
            if(empty($metaseo_sitemap->settings_sitemap['wpms_sitemap_pages'][$page->ID]['frequency'])){
                $pagefrequency = 'monthly';
            }else{
                $pagefrequency = $metaseo_sitemap->settings_sitemap['wpms_sitemap_pages'][$page->ID]['frequency'];
            }
            if(empty($metaseo_sitemap->settings_sitemap['wpms_sitemap_pages'][$page->ID]['priority'])){
                $pagepriority = '1.0';
            }else{
                $pagepriority = $metaseo_sitemap->settings_sitemap['wpms_sitemap_pages'][$page->ID]['priority'];
            }
            $select_priority = $metaseo_sitemap->wpms_view_select_priority('priority_pages_' . $page->ID, '_metaseo_settings_sitemap[wpms_sitemap_pages][' . $page->ID . '][priority]', $pagepriority);
            $select_frequency = $metaseo_sitemap->wpms_view_select_frequency('frequency_pages_' . $page->ID, '_metaseo_settings_sitemap[wpms_sitemap_pages][' . $page->ID . '][frequency]', $pagefrequency);
            $permalink = get_permalink($page->ID);
            echo '<div class="wpms_row wpms_row_record">';
            echo '<div style="float:left;line-height:30px">';
            if (isset($metaseo_sitemap->settings_sitemap['wpms_sitemap_pages'][$page->ID]['post_id']) && $metaseo_sitemap->settings_sitemap['wpms_sitemap_pages'][$page->ID]['post_id'] == $page->ID) {
                echo '<input class="wpms_sitemap_input_link checked" type="hidden" data-type="page" value="'.$permalink.'">';
                echo '<div class="pure-checkbox">';
                echo '<input class="cb_sitemaps_pages wpms_xmap_pages" id="wpms_sitemap_pages_' . $page->ID . '" type="checkbox" name="_metaseo_settings_sitemap[wpms_sitemap_pages][' . $page->ID . '][post_id]" value="' . $page->ID . '" checked>';
                echo '<label for="wpms_sitemap_pages_' . $page->ID . '">'. $page->post_title .'</label>';
                echo '</div>';
            } else {
                echo '<input class="wpms_sitemap_input_link" type="hidden" data-type="page" value="'.$permalink.'">';
                echo '<div class="pure-checkbox">';
                echo '<input class="cb_sitemaps_pages wpms_xmap_pages" id="wpms_sitemap_pages_' . $page->ID . '" type="checkbox" name="_metaseo_settings_sitemap[wpms_sitemap_pages][' . $page->ID . '][post_id]" value="' . $page->ID . '">';
                echo '<label for="wpms_sitemap_pages_' . $page->ID . '">'. $page->post_title .'</label>';
                echo '</div>';
            }

            echo '</div>';
            echo '<div style="margin-left:200px">' . $select_priority . $select_frequency . '</div>';
            echo '</div>';
        }
        ?>
    </div>
    <div class="holder holder_pages"></div>
</div>