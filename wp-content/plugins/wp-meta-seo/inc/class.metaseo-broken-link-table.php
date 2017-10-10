<?php
/**
 * Base class for displaying a list of links in an ajaxified HTML table.
 *
 */
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class MetaSeo_Broken_Link_Table extends WP_List_Table {

    public $link_view = array();
    public static $img_pattern = '/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i';
    public static $old_url = '';
    public static $new_url = '';

    function __construct() {
        parent::__construct(array(
            'singular' => 'metaseo_image',
            'plural' => 'metaseo_images',
            'ajax' => true
        ));
    }
    
    /**
    * Generate the table navigation above or below the table
    * @param string $which
    */
    function display_tablenav($which) {
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        if (!empty($post_types['attachment']))
            unset($post_types['attachment']);
        $p_type = "('" . implode("','", $post_types) . "')";
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">

            <?php if ($which == 'top'): ?>
                <input type="hidden" name="page" value="metaseo_image_meta" />

                <div class="alignleft actions bulkactions">
                    <?php $this->broken_fillter('sl_broken'); ?>
                    <?php $this->redirect_fillter('sl_redirect'); ?>
                    <?php $this->type_fillter('sltype'); ?>
                    <?php $this->flush_fillter('sl_flush'); ?>
                </div>
            <?php elseif ($which == 'bottom'): ?>
                <input type="hidden" name="page" value="metaseo_image_meta" />
                <div class="alignleft actions bulkactions">
                    <?php $this->broken_fillter('sl_broken1'); ?>
                    <?php $this->redirect_fillter('sl_redirect1'); ?>
                    <?php $this->type_fillter('sltype1'); ?>
                </div>
            <?php endif ?>

            <input type="hidden" name="page" value="metaseo_image_meta" />
            <?php if (!empty($_REQUEST['post_status'])): ?> 
                <input type="hidden" name="post_status" value="<?php echo esc_attr($_REQUEST['post_status']); ?>" />
            <?php endif ?>

            <div style="float:right;margin-left:8px;">
                <input type="number" required min="1" value="<?php echo $this->_pagination_args['per_page'] ?>" maxlength="3" name="metaseo_broken_link_per_page" class="metaseo_imgs_per_page screen-per-page" max="999" min="1" step="1">
                <input type="submit" name="btn_perpage" class="button_perpage button" id="button_perpage" value="Apply" >
            </div>

            <?php $this->pagination($which); ?>                
            <br class="clear" />
        </div>

        <?php
    }
    
    /**
    * Get a list of columns. The format is:
    * 'internal-name' => 'Title'
    * @return array
    */
    function get_columns() {
        return $columns = array(
            'col_link_url' => __('URL', 'wp-meta-seo'),
            'col_hit' => __('Hits number', 'wp-meta-seo'),
            'col_status' => __('Status', 'wp-meta-seo'),
            'col_link_text' => __('Type or Link text', 'wp-meta-seo'),
            'col_source' => __('Source', 'wp-meta-seo'),
        );
    }
    
    /**
    * Get a list of sortable columns. The format is:
    * 'internal-name' => 'orderby'
    * or
    * 'internal-name' => array( 'orderby', true )
    *
    * The second format will make the initial sorting order be descending
    * @return array
    */
    function get_sortable_columns() {
        return $sortable = array(
            'col_status' => array('status_text', true),
            'col_link_url' => array('link_url', true)
        );
    }

    /**
     * Print column headers, accounting for hidden and sortable columns.
     *
     * @since 3.1.0
     * @access public
     *
     * @param bool $with_id Whether to set the id attribute or not
     */
    public function print_column_headers($with_id = true) {
        list( $columns, $hidden, $sortable ) = $this->get_column_info();

        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg('paged', $current_url);

        if (isset($_GET['orderby']))
            $current_orderby = $_GET['orderby'];
        else
            $current_orderby = '';

        if (isset($_GET['order']) && 'desc' == $_GET['order'])
            $current_order = 'desc';
        else
            $current_order = 'asc';

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All', 'wp-meta-seo') . '</label>'
                    . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" style="margin:0;" />';
            $cb_counter++;
        }

        foreach ($columns as $column_key => $column_display_name) {
            $class = array('manage-column', "column-$column_key");

            $style = '';
            if (in_array($column_key, $hidden))
                $style = 'display:none;';

            $style = ' style="' . $style . '"';

            if ('cb' == $column_key)
                $class[] = 'check-column';
            elseif (in_array($column_key, array('posts', 'comments', 'links')))
                $class[] = 'num';

            if (isset($sortable[$column_key])) {
                list( $orderby, $desc_first ) = $sortable[$column_key];

                if ($current_orderby == $orderby) {
                    $order = 'asc' == $current_order ? 'desc' : 'asc';
                    $class[] = 'sorted';
                    $class[] = $current_order;
                } else {
                    $order = $desc_first ? 'desc' : 'asc';
                    $class[] = 'sortable';
                    $class[] = $desc_first ? 'asc' : 'desc';
                }

                $column_display_name = '<a href="' . esc_url(add_query_arg(compact('orderby', 'order'), $current_url)) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
            }

            $id = $with_id ? "id='$column_key'" : '';

            if (!empty($class))
                $class = "class='" . join(' ', $class) . "'";

            if ($column_key === 'cb') {
                echo "<th scope='col' $id $class style='padding:8px 10px;'>$column_display_name</th>";
            } else {
                echo "<th scope='col' $id $class $style colspan=\"3\">$column_display_name</th>";
            }
        }
    }
    
    /**
    * Prepares the list of items for displaying.
    * @uses WP_List_Table::set_pagination_args()
    */
    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $where = array('1=1');
        if (!empty($_REQUEST['sltype']) && $_REQUEST['sltype'] != 'all') {
            if ($_REQUEST['sltype'] != 'other') {
                $where[] = "type = '" . $_REQUEST['sltype'] . "'";
            } else {
                $where[] = "type IN ('comment','404_automaticaly')";
            }
        }

        if (!empty($_REQUEST['sl_broken']) && $_REQUEST['sl_broken'] != 'all') {
            if ($_REQUEST['sl_broken'] == 'custom_redirect_url') {
                $where[] = "link_url_redirect !=''";
            } else if ($_REQUEST['sl_broken'] == 'valid_links') {
                $where[] = "broken_internal = 0 AND broken_indexed = 0";
            } elseif ($_REQUEST['sl_broken'] == 'internal_broken_links') {
                $where[] = "broken_internal = 1";
            } else {
                $where[] = "broken_indexed = 1";
            }
        }

        if (!empty($_REQUEST['sl_redirect']) && $_REQUEST['sl_redirect'] != 'all') {
            if ($_REQUEST['sl_redirect'] == 'already_redirect') {
                $where[] = "(broken_internal = 1 OR broken_indexed = 1)";
                $where[] = "link_url_redirect !='' ";
            } else {
                $where[] = "(broken_internal = 1 OR broken_indexed = 1)";
                $where[] = "link_url_redirect ='' ";
            }
        }

        $keyword = !empty($_GET["txtkeyword"]) ? $_GET["txtkeyword"] : '';
        if (isset($keyword) && $keyword != '') {
            $where[] .= '(link_text LIKE "%' . $keyword . '%" OR link_url LIKE "%' . $keyword . '%")';
        }

        $orderby = !empty($_GET["orderby"]) ? ($_GET["orderby"]) : 'id';
        $order = !empty($_GET["order"]) ? ($_GET["order"]) : 'asc';
        $sortable = $this->get_sortable_columns();
        $orderby_array = array($orderby,true);
        if (in_array($orderby_array, $sortable)) {
            $orderStr = $orderby;
        } else {
            $orderStr = 'id';
        }

        if ($order == "asc") {
            $orderStr .= " ASC";
        } else {
            $orderStr .= " DESC";
        }

        if (!empty($orderby) & !empty($order)) {
            $orderStr = $wpdb->prepare(' ORDER BY %s ', $orderStr);
            $orderStr = str_replace("'", "", $orderStr);
        }

        $query = "SELECT COUNT(id) FROM " . $wpdb->prefix . "wpms_links WHERE " . implode(' AND ', $where) . $orderStr;
        $total_items = $wpdb->get_var($query);
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $query = "SELECT * FROM " . $wpdb->prefix . "wpms_links WHERE " . implode(' AND ', $where) . $orderStr;
        if (!empty($_REQUEST['metaseo_broken_link_per_page'])) {
            $_per_page = intval($_REQUEST['metaseo_broken_link_per_page']);
        } else {
            $_per_page = 0;
        }

        $per_page = get_user_option('metaseo_broken_link_per_page');
        if ($per_page !== false) {
            if ($_per_page && $_per_page !== $per_page) {
                $per_page = $_per_page;
                update_user_option(get_current_user_id(), 'metaseo_broken_link_per_page', $per_page);
            }
        } else {
            if ($_per_page > 0) {
                $per_page = $_per_page;
            } else {
                $per_page = 10;
            }
            add_user_meta(get_current_user_id(), 'metaseo_broken_link_per_page', $per_page);
        }

        $paged = !empty($_GET["paged"]) ? $_GET["paged"] : '';
        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }

        $total_pages = ceil($total_items / $per_page);

        if (!empty($paged) && !empty($per_page)) {
            $offset = ($paged - 1) * $per_page;
            $query .= ' LIMIT ' . (int) $offset . ',' . (int) $per_page;
        }

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'per_page' => $per_page
        ));


        $this->items = $wpdb->get_results($query);
    }
    
    /**
    * Displays the search box.
    */
    function search_box1() {
        if(is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            require_once (WPMETASEO_ADDON_PLUGIN_DIR . 'inc/page/custom_redirect_form.php');
        }
        if (empty($_REQUEST['txtkeyword']) && !$this->has_items())
            return;
        $txtkeyword = (!empty($_REQUEST['txtkeyword'])) ? urldecode(stripslashes($_REQUEST['txtkeyword'])) : "";
        if (!empty($_REQUEST['orderby']))
            echo '<input type="hidden" name="orderby" value="' . esc_attr($_REQUEST['orderby']) . '" />';
        if (!empty($_REQUEST['order']))
            echo '<input type="hidden" name="order" value="' . esc_attr($_REQUEST['order']) . '" />';
        if (!empty($_REQUEST['post_mime_type']))
            echo '<input type="hidden" name="post_mime_type" value="' . esc_attr($_REQUEST['post_mime_type']) . '" />';
        if (!empty($_REQUEST['detached']))
            echo '<input type="hidden" name="detached" value="' . esc_attr($_REQUEST['detached']) . '" />';
        ?>
        <p class="search-box">
            <input type="search" id="image-search-input" name="txtkeyword" value="<?php echo esc_attr(stripslashes($txtkeyword)); ?>" />
            <?php submit_button('Search URL', 'button', 'search', false, array('id' => 'search-submit')); ?>
        </p>
        <?php
    }
    
    /* Add fillter redirect */
    function redirect_fillter($name) {
        $redirects = array('not_yet_redirect' => __('Not yet redirected', 'wp-meta-seo'), 'already_redirect' => __('Already redirected', 'wp-meta-seo'));
        $curent_redirect = isset($_REQUEST['sl_redirect']) ? $_REQUEST['sl_redirect'] : 'all';
        ?>
        <label for="filter-by-redirect" class="screen-reader-text"><?php _e('Filter by redirect', 'wp-meta-seo'); ?></label>
        <select name="<?php echo $name ?>" id="filter-by-redirect" class="redirect_fillter">
            <option<?php selected($curent_redirect, 'all'); ?> value="all"><?php _e('Status', 'wp-meta-seo'); ?></option>
            <?php
            foreach ($redirects as $k => $redirect) {
                if ($curent_redirect == $k) {
                    echo '<option selected value="' . $k . '">' . $redirect . '</option>';
                } else {
                    echo '<option value="' . $k . '">' . $redirect . '</option>';
                }
            }
            ?>
        </select>

        <?php
    }
    
    /* Add fillter broken */
    function broken_fillter($name) {
        $brokens = array(
            'valid_links' => __('Valid links', 'wp-meta-seo'),
            'automaticaly_indexed' => __('404 automaticaly indexed', 'wp-meta-seo'),
            'internal_broken_links' => __('Internal broken links', 'wp-meta-seo')
            );
        if(is_plugin_active(WPMSEO_ADDON_FILENAME)){
            $brokens['custom_redirect_url'] = __('Custom redirect URL', 'wp-meta-seo');
        }
        $curent_broken = isset($_REQUEST['sl_broken']) ? $_REQUEST['sl_broken'] : 'all';
        ?>
        <label for="filter-by-broken" class="screen-reader-text"><?php _e('Filter by broken', 'wp-meta-seo'); ?></label>
        <select name="<?php echo $name ?>" id="filter-by-broken" class="broken_fillter">
            <option<?php selected($curent_broken, 'all'); ?> value="all"><?php _e('All', 'wp-meta-seo'); ?></option>
            <?php
            foreach ($brokens as $k => $broken) {
                if ($curent_broken == $k) {
                    echo '<option selected value="' . $k . '">' . $broken . '</option>';
                } else {
                    echo '<option value="' . $k . '">' . $broken . '</option>';
                }
            }
            ?>
        </select>

        <?php
    }

    /* Add fillter type */
    function type_fillter($name) {
        $types = array('url' => __('URL','wp-meta-seo') , 'image' => __('Image','wp-meta-seo'), 'other' => __('Other','wp-meta-seo'));
        $curent_type = isset($_REQUEST['sltype']) ? $_REQUEST['sltype'] : 'all';
        ?>
        <label for="filter-by-type" class="screen-reader-text"><?php _e('Filter by type', 'wp-meta-seo'); ?></label>
        <select name="<?php echo $name ?>" id="filter-by-type" class="metaseo-filter">
            <option<?php selected($curent_type, 'all'); ?> value="all"><?php _e('Type', 'wp-meta-seo'); ?></option>
            <?php
            foreach ($types as $k => $type) {
                if ($curent_type == $k) {
                    echo '<option selected value="' . $k . '">' . $type . '</option>';
                } else {
                    echo '<option value="' . $k . '">' . $type . '</option>';
                }
            }
            ?>
        </select>
        <input type="submit" name="filter_type_action" id="broken-submit" class="wpmsbtn wpmsbtn_small wpmsbtn_secondary" value="<?php _e('Filter', 'wp-meta-seo') ?>">
        <?php
        echo '<div style="float:left;padding-left: 5px;"><div class="wpms_process" data-w="0"></div>';
        echo '<div data-comment_paged="1" data-paged="1" class="wpmsbtn wpmsbtn_small wpms_scan_link">' . __('Index internal broken links', 'wp-meta-seo') . '</div></div>';
        echo '<span class="spinner"></span>';
    }
    
    /* Add fillter flush */
    function flush_fillter($name) {
        $flushs = array('automaticaly_indexed' => __('Automatic indexed 404', 'wp-meta-seo'), 'internal_broken_links' => __('Internal broken links', 'wp-meta-seo'), 'all' => __('Flush all 404', 'wp-meta-seo'));
        ?>
        <label for="filter-by-flush" class="screen-reader-text"><?php _e('Filter by flush', 'wp-meta-seo'); ?></label>
        <select name="<?php echo $name ?>" id="filter-by-flush">
            <option value="none"><?php _e('Select', 'wp-meta-seo'); ?></option>
            <?php
            foreach ($flushs as $k => $flush) {
                echo '<option value="' . $k . '">' . $flush . '</option>';
            }
            ?>
        </select>

        <?php
        echo '<div class="wpmsbtn wpmsbtn_small wpmsbtn_secondary wpms_flush_link">' . __('Flush', 'wp-meta-seo') . '</div>';
    }
    
    /**
    * Generate the table rows
    */
    function display_rows() {
        $url = URL;
        $url = preg_replace('/(^(http|https):\/\/[w]*\.*)/', '', $url);
        $records = $this->items;
        $i = 0;
        $alternate = "";

        list( $columns, $hidden ) = $this->get_column_info();
        if (!empty($records)) {
            foreach ($records as $rec) {
                $i++;
                echo '<tr id="record_' . $i . '" data-linkid="'.$rec->id.'" data-link="' . $i . '" data-post_id="' . $rec->source_id . '">';
                foreach ($columns as $column_name => $column_display_name) {

                    $class = sprintf('class="%1$s column-%1$s"', $column_name);
                    $style = "";

                    if (in_array($column_name, $hidden)) {
                        $style = ' style="display:none;"';
                    }

                    $attributes = $class . $style;

                    switch ($column_name) {
                        case 'col_link_url':
                            if ($rec->type == 'url') {
                                $value_url = $rec->link_final_url;
                            } else {
                                $value_url = $rec->link_url;
                            }
                            echo '<td class="wpms_link_html" colspan="3">';
                            echo '<input type="hidden" class="wpms_link_text" value="' . esc_attr($rec->link_text) . '">';
                            if ($rec->type == 'add_custom') {
                                echo '<a class="link_html" target="_blank" href="' . esc_url(site_url() . '/' . $rec->link_url) . '">' . $value_url . '</a>';
                                if($rec->link_url_redirect != ''){
                                    echo ' to ';
                                    echo '<a class="link_html" target="_blank" href="' . esc_url($rec->link_url_redirect) . '">' . str_replace(site_url(),'',$rec->link_url_redirect) . '</a>';
                                }
                            }else{
                                echo '<a class="link_html" target="_blank" href="' . esc_url($value_url) . '">' . $value_url . '</a>';
                            }

                            $row_action = array(
                                'edit' => '<a class="wpms_action_link wpms-edit-button" title="'.__('Edit redirect','wp-meta-seo').'"><div class="wpms_icon_action"><i class="material-icons">mode_edit</i></div><span>' . __('Edit', 'wp-meta-seo') . '</span></a>',
                                'delete' => '<a class="wpms_action_link submitdelete wpms-unlink-button" data-link_id="' . $rec->id . '" data-type="' . $rec->type . '" data-source_id="' . $rec->source_id . '" title="' . __('Remove redirect or link', 'wp-meta-seo') . '"><div class="wpms_icon_action"><i class="material-icons">delete_forever</i></div><span>' . __('Remove redirect', 'wp-meta-seo') . '</span></a>',
                                'recheck' => '<a class="wpms_action_link wpms-recheck-button" data-link_id="' . $rec->id . '" data-type="' . $rec->type . '" data-source_id="' . $rec->source_id . '" title="' . __('Check the link', 'wp-meta-seo') . '"><div class="wpms_icon_action"><i class="material-icons">loop</i></div><span>' . __('Check', 'wp-meta-seo') . '</span></a>'
                            );
                            echo $this->row_actions($row_action, false);
                            if (!empty($rec->source_id)) {
                                if ($rec->type == 'url') {
                                    $iii = 0;
                                    $pos = get_post($rec->source_id);
                                    if (!empty($pos)) {
                                        preg_match_all("#<a[^>]*>.*?</a>#si", $pos->post_content, $matches, PREG_PATTERN_ORDER);
                                        foreach ($matches[0] as $i => $content) {
                                            preg_match('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $matches);
                                            $href = $matches[1];
                                            if ($href == $rec->link_url) {
                                                $iii++;
                                            }
                                        }
                                    }
                                } elseif ($rec->type == 'comment_content_url') {
                                    $jjj = 0;
                                    $com = get_comment($rec->source_id);
                                    if (!empty($pos)) {
                                        preg_match_all("#<a[^>]*>.*?</a>#si", $com->comment_content, $matches, PREG_PATTERN_ORDER);
                                        foreach ($matches[0] as $i => $content) {
                                            preg_match('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $matches);
                                            $href = $matches[1];
                                            if ($href == $rec->link_url) {
                                                $jjj++;
                                            }
                                        }
                                    }
                                }
                            }
                            ?>
                            <div class="wpms-inline-editor-content">
                                <h4><?php echo _x('Edit Link', 'inline editor title', 'wp-meta-seo'); ?></h4>
                            <?php
                            if ($rec->type == 'url') {
                                if ($iii > 1) {
                                    echo '<span class="wpms-input-text-wrap"><span class="title">' . __('Text', 'wp-meta-seo') . '</span><input type="text" name="link_text" class="wpms-link-text-field" placeholder="' . __('Multiple link', 'wp-meta-seo') . '" data-type="multi" /></span>';
                                } else {
                                    echo '<span class="wpms-input-text-wrap"><span class="title">' . __('Text', 'wp-meta-seo') . '</span><input type="text" name="link_text" class="wpms-link-text-field" value="' . esc_attr($rec->link_text) . '" data-type="only" /></span>';
                                }
                            } elseif ($rec->type == 'comment_content_url') {
                                if ($jjj > 1) {
                                    echo '<span class="wpms-input-text-wrap"><span class="title">' . __('Text', 'wp-meta-seo') . '</span><input type="text" name="link_text" class="wpms-link-text-field" placeholder="' . __('Multiple link', 'wp-meta-seo') . '" data-type="multi" /></span>';
                                } else {
                                    echo '<span class="wpms-input-text-wrap"><span class="title">' . __('Text', 'wp-meta-seo') . '</span><input type="text" name="link_text" class="wpms-link-text-field" value="' . esc_attr($rec->link_text) . '" data-type="only" /></span>';
                                }
                            } else {
                                if($rec->type != 'add_custom'){
                                    echo '<span class="wpms-input-text-wrap"><span class="title">' . __('Text', 'wp-meta-seo') . '</span><input readonly type="text" name="link_text" class="wpms-link-text-field" value="(None)" data-type="only" /></span>';
                                }else{
                                    ?>
                                    <p class="wpms-input-text-wrap">
                                        <span class="title">
                                            <?php _e('Status', 'wp-meta-seo') ?>
                                        </span>
                                        <select name="custom_redirect_status" class="custom_redirect_status">
                                            <option value="301" <?php selected($rec->meta_title,301) ?>>301</option>
                                            <option value="302" <?php selected($rec->meta_title,302) ?>>302</option>
                                            <option value="307" <?php selected($rec->meta_title,307) ?>>307</option>
                                        </select>
                                    </p>
                                    <?php
                                }
                            }
                            ?>

                                <p class="wpms-input-text-wrap"><span class="title"><?php _e('URL', 'wp-meta-seo'); ?></span><input <?php echo ($rec->type == '404_automaticaly') ? 'readonly' : '' ?> type="text" name="link_url" class="wpms-link-url-field" value="<?php echo esc_attr($value_url); ?>" /></p>
                                <p class="wpms-input-text-wrap">
                                    <span class="title"><?php _e('Redirect', 'wp-meta-seo'); ?></span>
                                    <input type="text" name="link_url_redirect" class="wpms-link-redirect-field" value="<?php echo esc_attr($rec->link_url_redirect); ?>" />
                                    <span class="wlink-btn"><i class="mce-ico mce-i-link link-btn" id="link-btn"></i></span>
                                </p>
                            </label>

                            <div class="submit wpms-inline-editor-buttons">
                                <input type="button" class="wpmsbtn wpmsbtn_small wpmsbtn_secondary cancel alignleft wpms-cancel-button" value="<?php echo esc_attr(__('Cancel', 'wp-meta-seo')); ?>" />
                                <input type="button" data-type="<?php echo $rec->type ?>" data-link_id="<?php echo $rec->id ?>" data-source_id="<?php echo $rec->source_id ?>" class="wpmsbtn wpmsbtn_small save alignright wpms-update-link-button" value="<?php echo esc_attr(__('Add custom redirect', 'wp-meta-seo')); ?>" />
                            </div>
                            </div>
                            <?php
                            echo '</td>';
                            break;

                        case 'col_hit':
                            echo '<td colspan="3" style="text-align:center;">';
                            echo $rec->hit;
                            echo '</td>';
                            break;
                        case 'col_status':
                            echo '<td colspan="3" class="col_status">';
                            if (strpos($rec->status_text, '200') !== false) {
                                echo '<i class="material-icons wpms_ok metaseo_help_status" alt="Link is OK">done</i>';
                            } elseif (strpos($rec->status_text, '301') !== false) {
                                echo '<i class="material-icons wpms_ok metaseo_help_status" alt="Permanent redirect">done</i>';
                            } elseif (strpos($rec->status_text, '302') !== false) {
                                echo '<i class="material-icons wpms_ok metaseo_help_status" alt="Moved temporarily">done</i>';
                            } elseif (strpos($rec->status_text, '404') !== false || $rec->status_text == 'Server Not Found') {
                                $wpms_settings_404 = get_option('wpms_settings_404');
                                if ((isset($wpms_settings_404['wpms_redirect_homepage']) && $wpms_settings_404['wpms_redirect_homepage'] == 1) || $rec->link_url_redirect != '') {
                                    echo '<i class="material-icons wpms_ok metaseo_help_status" alt="Permanent redirect">done</i>';
                                } else {
                                    echo '<i class="material-icons wpms_warning metaseo_help_status" alt="404 error, not found">warning</i>';
                                }
                            } else {
                                echo $rec->status_text;
                            }

                            echo '</td>';
                            break;

                        case 'col_link_text':
                            if ($rec->type == 'image' || $rec->type == 'comment_content_image') {
                                echo '<td colspan="3" class="link_text"><span style="float: left;margin-right: 5px;"><i class="material-icons metaseo_help_status" alt="Images">photo</i></span><span> ' . __('Image', 'wp-meta-seo') . '</span></td>';
                            } elseif ($rec->type == 'comment') {
                                echo '<td colspan="3" class="link_text"><span> ' . $rec->link_text . '</span></td>';
                            } else {
                                if (strip_tags($rec->link_text) != '') {
                                    echo '<td colspan="3" class="link_text">' . strip_tags($rec->link_text) . '</td>';
                                } else {
                                    echo '<td colspan="3" class="link_text"><i>' . __('No text on this link', 'wp-meta-seo') . '</i></td>';
                                }
                            }

                            break;

                        case 'col_source':
                            if ($rec->type == '404_automaticaly') {
                                $source_inner = '<span style="float: left;margin-right: 5px;"><i class="material-icons metaseo_help_status" alt="External URL indexed">link</i></span>';
                                $source_inner .= __('404 automaticaly indexed', 'wp-meta-seo');
                                echo '<td colspan="3">' . $source_inner . '</td>';
                            } else {
                                if ($rec->type == 'comment' || $rec->type == 'comment_content_url' || $rec->type == 'comment_content_image') {
                                    $source = get_comment($rec->source_id);
                                    if (!empty($source)) {
                                        $row_action = array(
                                            'edit' => '<a target="_blank" href="' . get_edit_comment_link($rec->source_id) . '" title="'.__('Edit this item','wp-meta-seo').'">'.__('Edit','wp-meta-seo','wp-meta-seo').'</a>',
                                            'view' => '<a target="_blank" href="' . get_comment_link($rec->source_id) . '" title="View &#8220;'.$source->comment_author.'&#8221;" rel="permalink">'.__('View','wp-meta-seo').'</a>'
                                        );

                                        if ($rec->type == 'comment') {
                                            $source_inner = '<span style="float: left;margin-right: 5px;"><i class="material-icons metaseo_help_status" alt="Comments">person_outline</i></span>';
                                        } else {
                                            $source_inner = '<span style="float: left;margin-right: 5px;"><i class="material-icons metaseo_help_status" alt="Comments content">chat_bubble</i></span>';
                                        }
                                        $source_inner .= '<a target="_blank" href="' . get_edit_comment_link($rec->source_id) . '">' . $source->comment_author . '</a>';
                                    }
                                } else {
                                    $source = get_post($rec->source_id);
                                    if (!empty($source)) {
                                        $row_action = array(
                                            'edit' => '<a target="_blank" href="' . get_edit_post_link($rec->source_id) . '" title="'.__('Edit this item','wp-meta-seo').'">'.__('Edit','wp-meta-seo').'</a>',
                                            'view' => '<a target="_blank" href="' . get_post_permalink($rec->source_id) . '" title="View &#8220;'.$source->post_title.'&#8221;" rel="permalink">View</a>'
                                        );

                                        $source_inner = '<span style="float: left;margin-right: 5px;"><i class="material-icons metaseo_help_status" alt="Post , Page , Custom post">layers</i></span>';
                                        $source_inner .= '<a target="_blank" href="' . get_edit_post_link($rec->source_id) . '">' . $source->post_title . '</a>';
                                    }
                                }

                                echo '<td colspan="3">';
                                if (!empty($source)) {
                                    echo $source_inner;
                                    echo $this->row_actions($row_action, false);
                                } else {
                                    if($rec->type == 'add_custom' || $rec->type == 'add_rule'){
                                        echo '<a><i title="'.__('Custom redirect','wp-meta-seo').'" class="wpms_outgoing material-icons">call_missed_outgoing</i></a>';
                                    }else{
                                        echo '<a>'.__('Source Not Found','wp-meta-seo').'</a>';
                                    }
                                }
                                echo '</td>';
                                break;
                            }
                    }
                }

                echo '</tr>';
            }
        }
    }
    
    /*
     * Retrieves a modified URL query string.
     */
    public function process_action() {
        global $wpdb;
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $redirect = false;

        if (isset($_POST['search'])) {
            $current_url = add_query_arg(array("search" => "Search", "txtkeyword" => urlencode(stripslashes($_POST["txtkeyword"]))), $current_url);
            $redirect = true;
        }

        if (isset($_POST['filter_type_action'])) {
            $current_url = add_query_arg(array("sltype" => $_POST["sltype"], 'sl_redirect' => $_POST["sl_redirect"], 'sl_broken' => $_POST["sl_broken"]), $current_url);
            $redirect = true;
        }

        if (!empty($_POST['paged'])) {
            $current_url = add_query_arg(array("paged" => intval($_POST['paged'])), $current_url);
            $redirect = true;
        }

        if (!empty($_POST['metaseo_broken_link_per_page'])) {
            $current_url = add_query_arg(array("metaseo_broken_link_per_page" => intval($_POST['metaseo_broken_link_per_page'])), $current_url);
            $redirect = true;
        }

        if ($redirect === true) {
            wp_redirect($current_url);
            ob_end_flush();
            exit();
        }
    }
    
    /*
     * Scan link in post content
     */
    public static function wpms_get_links() {
        global $wpdb, $_wp_column_headers;

        $where = array();
        $post_type = isset($_REQUEST['post_type_filter']) ? $_REQUEST['post_type_filter'] : "";
        if ($post_type == "-1") {
            $post_type = "";
        }

        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        unset($post_types['attachment']);
        if (!empty($post_type) && !in_array($post_type, $post_types))
            $post_type = '\'post\'';
        else if (empty($post_type)) {
            $post_type = "'" . implode("', '", esc_sql($post_types)) . "'";
        } else {
            $post_type = "'" . $post_type . "'";
        }

        $where[] = "post_type IN ($post_type)";
        $states = get_post_stati(array('show_in_admin_all_list' => true));
        $states['trash'] = 'trash';
        $all_states = "'" . implode("', '", esc_sql($states)) . "'";

        if (empty($_REQUEST['post_status'])) {
            $where[] = "post_status IN ($all_states)";
        } else {
            $requested_state = $_REQUEST['post_status'];
            if (in_array($requested_state, $states)) {
                $where[] = "post_status IN ('$requested_state')";
            } else {
                $where[] = "post_status IN ($all_states)";
            }
        }

        $query = "SELECT ID, post_title, post_excerpt , post_content ,post_name, post_type,  post_status"
                . " FROM $wpdb->posts "
                . " WHERE " . implode(' AND ', $where);

        $results = $wpdb->get_results($query);
        $list_link = array();
        
        // get link from comments
        $comments = get_comments();
        foreach ($comments as $comment) {
            if (!empty($comment->comment_author_url)) {
                $source_link = '<a href="' . get_edit_comment_link($comment->comment_ID) . '"><b>' . $comment->comment_author . '</b></a>';
                $status = MetaSeo_Broken_Link_Table::get_urlstatus($comment->comment_author_url);
                $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                $list_link[$comment->comment_author_url . $comment->comment_ID] = MetaSeo_Broken_Link_Table::get_result_link($source_link, $comment->comment_ID, $comment->comment_author_url, $comment->comment_author, 'comment', $status, $status_type);
            }
        }
        
        // get link from posts
        foreach ($results as $post) {
            if ($post->post_excerpt != 'metaseo_404_page') {
                $dom = new DOMDocument;
                libxml_use_internal_errors(true);
                if (isset($post->post_content) && $post->post_content != '') {
                    preg_match_all("#<a[^>]*>.*?</a>#si", $post->post_content, $matches, PREG_PATTERN_ORDER);
                    foreach (array_unique($matches[0]) as $i => $content) {
                        preg_match('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $matches);
                        $href = $matches[1];
                        $status = MetaSeo_Broken_Link_Table::get_urlstatus($href);
                        $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                        $link_text = preg_replace("/<a\s(.+?)>(.+?)<\/a>/is", "$2", $content);
                        $source_link = '<a href="' . get_edit_post_link($post->ID) . '"><b>' . $post->post_title . '</b></a>';
                        $list_link[$href . 'url' . $post->ID . $link_text] = MetaSeo_Broken_Link_Table::get_result_link($source_link, $post->ID, $href, $link_text, 'url', $status, $status_type);
                    }
                    preg_match_all('/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i', $post->post_content, $matches, PREG_PATTERN_ORDER);
                    foreach (array_unique($matches[0]) as $content) {
                        $source_link = '<a href="' . get_edit_post_link($post->ID) . '"><b>' . $post->post_title . '</b></a>';
                        preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $matches);
                        $src = $matches[1];
                        $status = MetaSeo_Broken_Link_Table::get_urlstatus($src);
                        $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                        $link_text = '';
                        $list_link[$src . 'img' . $post->ID] = MetaSeo_Broken_Link_Table::get_result_link($source_link, $post->ID, $src, $link_text, 'image', $status, $status_type);
                    }
                }
            }
        }

        return $list_link;
    }
    
    /*
     *  Return detail link 
     */
    public static function get_result_link($source_link, $source_id, $url, $link_text, $type, $status, $status_type, $meta_title = '', $rel = '', $postID = 0) {
        $res = array(
            'source_link' => $source_link,
            'source_id' => (int) $source_id,
            'link_url' => $url,
            'link_text' => $link_text,
            'type' => $type,
            'status' => $status,
            'status_type' => $status_type);

        if (isset($meta_title)) {
            $res['meta_title'] = $meta_title;
        } else {
            $res['meta_title'] = '';
        }

        if (isset($rel) && $rel == 'nofollow') {
            $res['follow'] = 0;
        } else {
            $res['follow'] = 1;
        }

        if (strpos($url, 'mailto:') !== false) {
            $res['link_final_url'] = $url;
        } else {
            if ($type == 'url') {
                if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                    $perlink = get_option('permalink_structure');
                    if (empty($perlink)) {
                        $res['link_final_url'] = get_site_url() . '/' . $url;
                    } else {
                        if (!empty($postID)) {
                            $res['link_final_url'] = get_permalink($postID) . $url;
                        } else {
                            $res['link_final_url'] = $perlink . '/' . $url;
                        }
                    }
                } else {
                    $res['link_final_url'] = $url;
                }
            } else {
                $res['link_final_url'] = $url;
            }
        }


        return $res;
    }

    /*
     * Get link status
     */
    public static function get_urlstatus($url) {
        $status = @get_headers($url, 0);
        if (strpos($url, 'mailto:') !== false)
            return 'Not checked';
        if (strpos($url, '#') === 0 || strpos($url, 'tel:') === 0) {
            return 'HTTP/1.1 200 OK';
        }
        if (isset($status[0])) {
            return $status[0];
        } else {
            return 'Server Not Found';
        }
    }
    
    /*
     * Get link status type
     */
    public static function get_urlstatus_type($status) {
        if (isset($status) && $status == 'Not checked')
            return 'ok';
        if (isset($status) && $status != 'Server Not Found') {
            if (((int) substr($status, 9, 3) >= 200 && (int) substr($status, 9, 3) <= 204) || (int) substr($status, 9, 3) == 401) {
                $type = 'ok';
            } elseif (((int) substr($status, 9, 3) >= 400 && (int) substr($status, 9, 3) <= 503 && (int) substr($status, 9, 3) != 401)) {
                if (in_array((int) substr($status, 9, 3), array(404, 410))) {
                    $type = 'broken_internal';
                } else {
                    $type = 'warning';
                }
            } elseif (((int) substr($status, 9, 3) >= 301 && (int) substr($status, 9, 3) <= 304)) {
                $type = 'ok';
            } else {
                $type = 'dismissed';
            }
        } else {
            $type = 'broken_internal';
        }
        return $type;
    }
    
    /*
     * Delete link comment in wpms_links table when delete comment
     */
    public static function wpms_deleted_comment($comment_ID) {

        global $wpdb;
        $wpdb->query(
                $wpdb->prepare(
                        "DELETE FROM " . $wpdb->prefix . "wpms_links WHERE source_id = %d
                     AND (type = %s || type = %s || type = %s)
                    ", $comment_ID, 'comment', 'comment_content_url', 'comment_content_image'
                )
        );
    }
    
    /*
     * Delete link post in wpms_links table when delete post
     */
    public static function wpms_delete_post($post_id) {
        global $wpdb;
        $wpdb->query(
                $wpdb->prepare(
                        "DELETE FROM " . $wpdb->prefix . "wpms_links WHERE source_id = %d
                     AND type != %s
                    ", $post_id, 'comment'
                )
        );
    }
    
    /*
     * Update wpms_links table when update comment
     */
    public static function wpms_update_comment($comment_ID) {
        global $wpdb;
        $comment = get_comment($comment_ID);
        $status = wp_get_comment_status($comment_ID);
        if ($status == 'approved') {
            if (!empty($comment->comment_author_url)) {
                $status = MetaSeo_Broken_Link_Table::get_urlstatus(($comment->comment_author_url));
                $status_text = MetaSeo_Broken_Link_Table::wpms_get_status_text($status);
                $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                $sql = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpms_links WHERE source_id=%d AND type=%s ", array($comment_ID, 'comment'));
                $check = $wpdb->get_var($sql);
                if ($check == 0) {
                    $value = array(
                        'link_url' => $comment->comment_author_url,
                        'link_text' => $comment->comment_author,
                        'source_id' => $comment_ID,
                        'type' => 'comment',
                        'status_code' => $status,
                        'status_text' => $status_text,
                        'broken_indexed' => 0,
                        'broken_internal' => 0,
                        'warning' => 0,
                        'dismissed' => 0,
                    );
                    if (isset($status_type) && $status_type != 'ok') {
                        $value[$status_type] = 1;
                    }
                    $wpdb->insert(
                            $wpdb->prefix . 'wpms_links', $value
                    );
                } else {
                    $value = array('link_url' => $comment->comment_author_url,
                        'status_code' => $status,
                        'status_text' => $status_text,
                        'broken_indexed' => 0,
                        'broken_internal' => 0,
                        'warning' => 0,
                        'dismissed' => 0,
                    );

                    if (isset($_POST['link_redirect']))
                        $value['link_url_redirect'] = ($_POST['link_redirect']);

                    if (isset($status_type) && $status_type != 'ok') {
                        $value[$status_type] = 1;
                    }
                    $wpdb->update(
                            $wpdb->prefix . 'wpms_links', $value, array('source_id' => $comment_ID, 'type' => 'comment'), array('%s', '%s', '%s', '%d', '%d', '%d', '%d'), array('%d', '%s')
                    );
                }
            } else {
                $wpdb->query(
                        $wpdb->prepare("DELETE FROM " . $wpdb->prefix . "wpms_links WHERE source_id = %d AND (type = %s)", array($comment_ID, 'comment'))
                );
            }


            if (isset($comment->comment_content) && $comment->comment_content != '') {
                preg_match_all("#<a[^>]*>.*?</a>#si", $comment->comment_content, $matches, PREG_PATTERN_ORDER);
                foreach (array_unique($matches[0]) as $i => $content) {
                    preg_match('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $matches);
                    $href = $matches[1];
                    $status = MetaSeo_Broken_Link_Table::get_urlstatus($href);
                    $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                    $link_text = preg_replace("/<a\s(.+?)>(.+?)<\/a>/is", "$2", $content);
                    $source_link = '<a href="' . get_edit_comment_link($comment->comment_ID) . '"><b>' . $comment->comment_author . '</b></a>';
                    $links_in_content[$href . 'comment_content_url' . $comment->comment_ID . $link_text] = MetaSeo_Broken_Link_Table::get_result_link($source_link, $comment->comment_ID, $href, $link_text, 'comment_content_url', $status, $status_type);
                }
                preg_match_all('/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i', $comment->comment_content, $matches, PREG_PATTERN_ORDER);
                foreach (array_unique($matches[0]) as $content) {
                    preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $matches);
                    $src = $matches[1];
                    $status = MetaSeo_Broken_Link_Table::get_urlstatus($src);
                    $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                    $link_text = '';
                    $source_link = '<a href="' . get_edit_comment_link($comment->comment_ID) . '"><b>' . $comment->comment_author . '</b></a>';
                    $links_in_content[$src . 'comment_content_image' . $comment->comment_ID] = MetaSeo_Broken_Link_Table::get_result_link($source_link, $comment->comment_ID, $src, $link_text, 'comment_content_image', $status, $status_type);
                }
            }

            global $wpdb;
            $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "wpms_links WHERE source_id=%d AND (type = %s || type = %s)", array($comment->comment_ID, "comment_content_url", "comment_content_image"));
            $links = $wpdb->get_results($sql);
            foreach ($links as $link) {
                if (empty($links_in_content[$link->link_url . $link->type])) {
                    $wpdb->delete($wpdb->prefix . "wpms_links", array('id' => $link->id), array('%d'));
                } else {
                    unset($links_in_content[$link->link_url . $link->type . $link->link_text]);
                }
            }

            if (!empty($links_in_content)) {
                foreach ($links_in_content as $link) {
                    MetaSeo_Broken_Link_Table::wpms_insert_link($link, $wpdb);
                }
            }
        } else {
            global $wpdb;
            $wpdb->query(
                    $wpdb->prepare("DELETE FROM " . $wpdb->prefix . "wpms_links WHERE source_id = %d AND (type = %s || type = %s || type = %s)", array($comment_ID, 'comment', 'comment_content_url', 'comment_content_image'))
            );
        }

        update_option('wpms_last_update_post',time());
    }
    
    /*
     * Update wpms_links table when update post
     */
    public static function wpms_update_post($post_ID, $post_after, $post_before) {
        $post = $post_after;
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $links_in_content = array();

        if ($post->post_excerpt != 'metaseo_404_page') {
            if ($post->post_status == 'publish') {
                if (isset($post->post_content) && $post->post_content != '') {

                    preg_match_all("#<a[^>]*>.*?</a>#si", $post->post_content, $matches, PREG_PATTERN_ORDER);
                    foreach (array_unique($matches[0]) as $i => $content) {
                        $dom->loadHTML($content);
                        $tags = $dom->getElementsByTagName('a');
                        $meta_title = $tags->item(0)->getAttribute('title');
                        $rel = $tags->item(0)->getAttribute('rel');

                        preg_match('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $matches);
                        $href = $matches[1];
                        $status = MetaSeo_Broken_Link_Table::get_urlstatus($href);
                        $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                        $link_text = preg_replace("/<a\s(.+?)>(.+?)<\/a>/is", "$2", $content);
                        $source_link = '<a href="' . get_edit_post_link($post->ID) . '"><b>' . $post->post_title . '</b></a>';
                        $links_in_content[$href . 'url' . $post->ID . $link_text] = MetaSeo_Broken_Link_Table::get_result_link($source_link, $post->ID, $href, $link_text, 'url', $status, $status_type, $meta_title, $rel);
                    }
                    preg_match_all('/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i', $post->post_content, $matches, PREG_PATTERN_ORDER);
                    foreach (array_unique($matches[0]) as $content) {
                        $source_link = '<a href="' . get_edit_post_link($post->ID) . '"><b>' . $post->post_title . '</b></a>';
                        preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $matches);
                        $src = $matches[1];
                        $status = MetaSeo_Broken_Link_Table::get_urlstatus($src);
                        $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                        $link_text = '';
                        $links_in_content[$src . 'img' . $post->ID] = MetaSeo_Broken_Link_Table::get_result_link($source_link, $post->ID, $src, $link_text, 'image', $status, $status_type);
                    }
                }

                global $wpdb;
                $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "wpms_links WHERE source_id=%d AND type != %s", array($post->ID, "comment"));
                $links = $wpdb->get_results($sql);
                foreach ($links as $link) {
                    if (empty($links_in_content[$link->link_url . $link->type])) {
                        $wpdb->delete($wpdb->prefix . "wpms_links", array('id' => $link->id), array('%d'));
                    } else {
                        unset($links_in_content[$link->link_url . $link->type . $link->link_text]);
                    }
                }

                if (!empty($links_in_content)) {
                    foreach ($links_in_content as $link) {
                        MetaSeo_Broken_Link_Table::wpms_insert_link($link, $wpdb);
                    }
                }
            } else {
                global $wpdb;
                $wpdb->query(
                        $wpdb->prepare("DELETE FROM " . $wpdb->prefix . "wpms_links WHERE source_id = %d AND (type = %s || type = %s)", array($post->ID, 'image', 'url'))
                );
            }
        }

        update_option('wpms_last_update_post',time());
    }
    
    /*
     * Scan link in comment , post
     */
    public static function wpms_scan_link() {

        global $wpdb, $_wp_column_headers;
        $limit_comment_content = 1;
        $limit_comment = 10;
        $limit_post = 1;

        $where = array();
        $post_type = isset($_REQUEST['post_type_filter']) ? $_REQUEST['post_type_filter'] : "";
        if ($post_type == "-1") {
            $post_type = "";
        }

        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        unset($post_types['attachment']);
        if (!empty($post_type) && !in_array($post_type, $post_types))
            $post_type = '\'post\'';
        else if (empty($post_type)) {
            $post_type = "'" . implode("', '", $post_types) . "'";
        } else {
            $post_type = "'" . $post_type . "'";
        }

        $where[] = "post_type IN ($post_type)";
        $states = 'publish';
        $where[] = "post_status = '$states'";
        $total_comments = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "comments");
        $total_posts = $wpdb->get_var("SELECT COUNT(*)"
                . " FROM $wpdb->posts "
                . " WHERE " . implode(' AND ', $where));

        $total_link = (int) $total_comments + (int) $total_posts;
        $percent_comment_content = 33.33;
        $percent_comment = 33.33;
        $percent_post = 33.33;

        if (!empty($total_comments))
            $percent_comment_content = 33.33 / $total_comments;
        if ($total_comments < $limit_comment_content)
            $percent_comment_content = 33.33;
        if (!empty($total_comments))
            $percent_comment = 33.33 / $total_comments;
        if ($total_comments < $limit_comment)
            $percent_comment = 33.33;
        if (!empty($total_posts))
            $percent_post = 33.33 / $total_posts;
        if ($total_posts < $limit_post)
            $percent_post = 33.33;

        // scan link in comment url
        $comments = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "comments WHERE comment_approved = 1 AND comment_author_url != '' AND comment_author_url NOT IN (SELECT link_url FROM " . $wpdb->prefix . "wpms_links WHERE type='comment') LIMIT $limit_comment");
        if (!empty($comments)) {
            foreach ($comments as $comment) {
                if (!empty($comment->comment_author_url)) {
                    $source_link = '<a href="' . get_edit_comment_link($comment->comment_ID) . '"><b>' . $comment->comment_author . '</b></a>';
                    $status = MetaSeo_Broken_Link_Table::get_urlstatus($comment->comment_author_url);
                    $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                    $coms = MetaSeo_Broken_Link_Table::get_result_link($source_link, $comment->comment_ID, $comment->comment_author_url, $comment->comment_author, 'comment', $status, $status_type);
                    MetaSeo_Broken_Link_Table::wpms_insert_link($coms, $wpdb);
                }
            }
            wp_send_json(array('status' => false, 'type' => 'limit', 'percent' => $percent_comment));
        }

        // scan link in comment content
        $k = 0;
        $off_set = ($_POST['comment_paged'] - 1) * $limit_comment_content;
        $query = "SELECT * FROM " . $wpdb->prefix . "comments WHERE comment_approved = 1 AND comment_content != '' LIMIT $limit_comment_content OFFSET $off_set";
        $comments_content = $wpdb->get_results($query);
        if (!empty($comments_content)) {
            foreach ($comments_content as $comment) {
                $dom = new DOMDocument;
                libxml_use_internal_errors(true);
                if (isset($comment->comment_content) && $comment->comment_content != '') {
                    preg_match_all("#<a[^>]*>.*?</a>#si", $comment->comment_content, $matches, PREG_PATTERN_ORDER);
                    foreach (array_unique($matches[0]) as $i => $content) {
                        preg_match('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $matches);
                        $href = $matches[1];
                        $status = MetaSeo_Broken_Link_Table::get_urlstatus($href);
                        $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                        $link_text = preg_replace("/<a\s(.+?)>(.+?)<\/a>/is", "$2", $content);
                        $source_link = '<a href="' . get_edit_comment_link($comment->comment_ID) . '"><b>' . $comment->comment_author . '</b></a>';
                        $link_a = MetaSeo_Broken_Link_Table::get_result_link($source_link, $comment->comment_ID, $href, $link_text, 'comment_content_url', $status, $status_type);
                        MetaSeo_Broken_Link_Table::wpms_insert_link($link_a, $wpdb);
                    }
                    preg_match_all('/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i', $comment->comment_content, $matches, PREG_PATTERN_ORDER);
                    foreach (array_unique($matches[0]) as $content) {
                        $source_link = '<a href="' . get_edit_comment_link($comment->comment_ID) . '"><b>' . $comment->comment_author . '</b></a>';
                        preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $matches);
                        $src = $matches[1];
                        $status = MetaSeo_Broken_Link_Table::get_urlstatus($src);
                        $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                        $link_text = '';
                        $link_sou = MetaSeo_Broken_Link_Table::get_result_link($source_link, $comment->comment_ID, $src, $link_text, 'comment_content_image', $status, $status_type);
                        MetaSeo_Broken_Link_Table::wpms_insert_link($link_sou, $wpdb);
                    }
                }
            }

            $k++;
            if ($k >= $limit_comment_content) {
                wp_send_json(array('status' => false, 'type' => 'limit_comment_content', 'paged' => $_POST['comment_paged'], 'percent' => $percent_comment_content * count($comments_content)));
            }
        }

        // scan link in post
        $j = 0;
        $off_set = ($_POST['paged'] - 1) * $limit_post;
        $query = "SELECT ID, post_title, post_excerpt , post_content ,post_name, post_type,  post_status"
                . " FROM $wpdb->posts "
                . " WHERE " . implode(' AND ', $where) . "LIMIT $limit_post OFFSET $off_set";

        $results = $wpdb->get_results($query);
        if (empty($results))
            wp_send_json(array('status' => true));

        foreach ($results as $post) {
            if ($post->post_excerpt != 'metaseo_404_page') {
                $dom = new DOMDocument;
                libxml_use_internal_errors(true);
                if (isset($post->post_content) && $post->post_content != '') {
                    preg_match_all("#<a[^>]*>.*?</a>#si", $post->post_content, $matches, PREG_PATTERN_ORDER);
                    foreach (array_unique($matches[0]) as $i => $content) {
                        $dom->loadHTML($content);
                        $tags = $dom->getElementsByTagName('a');
                        $meta_title = $tags->item(0)->getAttribute('title');
                        $rel = $tags->item(0)->getAttribute('rel');
                        preg_match('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $matches);
                        $href = $matches[1];
                        $status = MetaSeo_Broken_Link_Table::get_urlstatus($href);
                        $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                        $link_text = preg_replace("/<a\s(.+?)>(.+?)<\/a>/is", "$2", $content);
                        $source_link = '<a href="' . get_edit_post_link($post->ID) . '"><b>' . $post->post_title . '</b></a>';
                        $link_a = MetaSeo_Broken_Link_Table::get_result_link($source_link, $post->ID, $href, $link_text, 'url', $status, $status_type, $meta_title, $rel, $post->ID);
                        MetaSeo_Broken_Link_Table::wpms_insert_link($link_a, $wpdb);
                    }
                    preg_match_all('/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i', $post->post_content, $matches, PREG_PATTERN_ORDER);
                    foreach (array_unique($matches[0]) as $content) {
                        $source_link = '<a href="' . get_edit_post_link($post->ID) . '"><b>' . $post->post_title . '</b></a>';
                        preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $matches);
                        $src = $matches[1];
                        $status = MetaSeo_Broken_Link_Table::get_urlstatus($src);
                        $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
                        $link_text = '';
                        $link_sou = MetaSeo_Broken_Link_Table::get_result_link($source_link, $post->ID, $src, $link_text, 'image', $status, $status_type);
                        MetaSeo_Broken_Link_Table::wpms_insert_link($link_sou, $wpdb);
                    }
                }
            }
            $j++;
            if ($j >= $limit_post) {
                wp_send_json(array('status' => false, 'type' => 'limit_post', 'paged' => $_POST['paged'], 'percent' => $percent_post * count($results)));
            }
        }

        $link_settings = array(
            "enable" => 0,
            "numberFrequency" => 1,
            "showlinkFrequency" => "month"
        );

        $linksettings = get_option('wpms_link_settings');
        if (is_array($linksettings)) {
            $link_settings = array_merge($link_settings, $linksettings);
        }

        $link_settings['wpms_lastRun_scanlink'] = time();
        update_option('wpms_link_settings', $link_settings);
        wp_send_json(array('status' => true));
    }
    
    /*
     * Insert link to wpms_link table
     */
    public static function wpms_insert_link($link, $wpdb) {
        $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "wpms_links WHERE link_url=%s AND type=%s AND source_id=%d ", array($link['link_url'], $link['type'], $link['source_id']));
        $links = $wpdb->get_results($sql);
        if (count($links) == 0) {
            $status = MetaSeo_Broken_Link_Table::get_urlstatus(($link['link_url']));
            $status_text = MetaSeo_Broken_Link_Table::wpms_get_status_text($status);
            $value = array(
                'link_url' => $link['link_url'],
                'link_final_url' => $link['link_final_url'],
                'link_text' => $link['link_text'],
                'source_id' => $link['source_id'],
                'type' => $link['type'],
                'status_code' => $link['status'],
                'status_text' => $status_text,
                'broken_indexed' => 0,
                'broken_internal' => 0,
                'warning' => 0,
                'dismissed' => 0,
                'meta_title' => $link['meta_title'],
                'follow' => $link['follow']
            );
            if (isset($link['status_type']) && $link['status_type'] != 'ok') {
                $value[$link['status_type']] = 1;
            }
            
            $site_url = get_site_url();
            $value = MetaSeo_Broken_Link_Table::wpms_check_internal_link($link['link_url'], $site_url, $value);

            $wpdb->insert(
                    $wpdb->prefix . 'wpms_links', $value
            );
        } else {
            $value = array(
                'meta_title' => $link['meta_title'],
                'follow' => $link['follow']
            );
            $site_url = get_site_url();

            $status = MetaSeo_Broken_Link_Table::get_urlstatus(($link['link_url']));
            $status_text = MetaSeo_Broken_Link_Table::wpms_get_status_text($status);

            $value = MetaSeo_Broken_Link_Table::wpms_check_internal_link($links[0]->link_url, $site_url, $value);
            $value['status_code'] = $status;
            $value['status_text'] = $status_text;
            if ($links[0]->follow != $link['follow'] || $links[0]->meta_title != $link['meta_title'] || $links[0]->internal != $value['internal'] || $links[0]->status_code != $value['status_code']) {
                $wpdb->update(
                        $wpdb->prefix . 'wpms_links', $value, array('id' => $links[0]->id)
                );
            }
        }
    }
    
    /*
     * Check internal link
     */
    public static function wpms_check_internal_link($link, $siteUrl, $value) {
        $info_link = parse_url($link);
        if(empty($info_link['path'])){
            $value['internal'] = 0;
            return $value;
        }

        if(empty($info_link['host'])){
            $value['internal'] = 0;
            return $value;
        }

        $info_site_url = parse_url($siteUrl);
        $domain_link = $info_link['host'] . $info_link['path'] . '/';
        $domain_site = $info_site_url['host'] . $info_site_url['path'] . '/';
        if (strpos($domain_link, $domain_site) !== false) {
            $value['internal'] = 1;
        } else {
            $value['internal'] = 0;
        }

        return $value;
    }
    
    /*
     * Flush link
     */
    public static function wpms_flush_link() {
        global $wpdb;
        if (isset($_POST['type']) && $_POST['type'] != 'none') {
            switch ($_POST['type']) {
                case 'automaticaly_indexed':
                    $wpdb->query(
                            $wpdb->prepare(
                                    "
                                    DELETE FROM " . $wpdb->prefix . "wpms_links
                                     WHERE broken_indexed = %d AND link_url_redirect = %s
                                    ", array(1, '')
                            )
                    );
                    break;
                case 'internal_broken_links':
                    $wpdb->query(
                            $wpdb->prepare(
                                    "
                                    DELETE FROM " . $wpdb->prefix . "wpms_links
                                     WHERE broken_internal = %d AND link_url_redirect = %s
                                    ", array(1, '')
                            )
                    );
                    break;
                case 'all':
                    $wpdb->query(
                            $wpdb->prepare(
                                    "
                                    DELETE FROM " . $wpdb->prefix . "wpms_links
                                     WHERE (broken_internal = %d
                                     OR broken_indexed = %d) AND link_url_redirect = %s
                                    ", array(1, 1, '')
                            )
                    );

                    break;
            }
            wp_send_json(true);
        }
        wp_send_json(false);
    }
    
    /*
     * Get status text
     */
    public static function wpms_get_status_text($status) {
        if ($status == 'Not checked')
            return 'Not checked';
        if ($status == 'Server Not Found') {
            $status_text = 'Server Not Found';
        } else {
            $status_text = substr($status, 9);
        }
        return $status_text;
    }

    /*
     * Add custom redirect
     */
    public static function wpms_add_custom_redirect() {
        do_action('wpms_add_custom_redirect');
        wp_send_json(array('status' => true , 'message' => __('Done!','wp-meta-seo')));
    }
    
    /*
     * Update link
     */
    public static function wpms_update_link() {

        if (isset($_POST['link_id'])) {
            global $wpdb;
            $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "wpms_links WHERE id=%d", array($_POST['link_id']));
            $link_detail = $wpdb->get_row($sql);
            if (empty($link_detail))
                wp_send_json(false);
            $new_link = stripslashes($_POST['new_link']);
            $link_redirect = stripslashes($_POST['link_redirect']);
            if(isset($_POST['new_text'])){
                $new_text = stripcslashes($_POST['new_text']);
            }else{
                $new_text = '';
            }

            if($link_redirect != ''){
                $status = 'HTTP/1.1 200 OK';
            }else{
                $status = MetaSeo_Broken_Link_Table::get_urlstatus($new_link);
            }

            $status_text = MetaSeo_Broken_Link_Table::wpms_get_status_text($status);
            $status_type = MetaSeo_Broken_Link_Table::get_urlstatus_type($status);
            if ($link_detail->type != '404_automaticaly') {
                $value = array('link_url' => $new_link,
                    'link_final_url' => '',
                    'link_url_redirect' => '',
                    'link_text' => stripcslashes($new_text),
                    'status_code' => $status,
                    'status_text' => $status_text,
                    'broken_indexed' => 0,
                    'broken_internal' => 0,
                    'warning' => 0,
                    'dismissed' => 0,
                );
                if (strpos($new_link, 'mailto:') !== false) {
                    $value['link_final_url'] = $new_link;
                } else {
                    if (!preg_match("~^(?:f|ht)tps?://~i", $new_link)) {
                        $perlink = get_option('permalink_structure');
                        if (empty($perlink)) {
                            $value['link_final_url'] = get_site_url() . '/' . $new_link;
                        } else {
                            if (!empty($link_detail->source_id)) {
                                $value['link_final_url'] = get_permalink($link_detail->source_id) . $new_link;
                            } else {
                                $value['link_final_url'] = $perlink . '/' . $new_link;
                            }
                        }
                    } else {
                        $value['link_final_url'] = $new_link;
                    }
                }

                if (!empty($link_redirect))
                    $value['link_url_redirect'] = $link_redirect;
                if (isset($status_type) && $status_type != 'ok') {
                    $value[$status_type] = 1;
                }
            } else {
                $status = MetaSeo_Broken_Link_Table::get_urlstatus($link_redirect);
                $status_text = MetaSeo_Broken_Link_Table::wpms_get_status_text($status);

                $value = array(
                    'link_url_redirect' => stripslashes($link_redirect),
                    'status_code' => $status,
                    'status_text' => $status_text,
                    'broken_indexed' => 1
                );
            }

            if($link_detail->type == 'add_custom'){
                $value['meta_title'] = $_POST['status_redirect'];
            }

            $site_url = get_site_url();
            $value = MetaSeo_Broken_Link_Table::wpms_check_internal_link($new_link, $site_url, $value);

            $wpdb->update(
                    $wpdb->prefix . 'wpms_links', $value, array('id' => $_POST['link_id'])
            );

            switch ($link_detail->type) {
                case '404_automaticaly':
                    wp_send_json(array('status' => true, 'type' => '404_automaticaly', 'status_text' => $status_text, 'new_link' => esc_url($new_link)));
                    break;
                case 'comment_content_image':
                    $comment = get_comment($link_detail->source_id);
                    if (!empty($comment)) {
                        $old_value = $comment->comment_content;
                        $edit_result = MetaSeo_Broken_Link_Table::edit_linkimg($old_value, $new_link, $link_detail->link_url);
                        $my_comment = array(
                            'comment_ID' => $link_detail->source_id,
                            'comment_content' => $edit_result['content']
                        );
                        remove_action('edit_comment', array('MetaSeo_Broken_Link_Table', 'wpms_update_comment'));
                        wp_update_comment($my_comment);
                        wp_send_json(array('status' => true, 'type' => 'image', 'status_text' => $status_text, 'new_link' => esc_url($edit_result['raw_url'])));
                    }
                    break;
                case 'image':
                    $post = get_post($link_detail->source_id);
                    if (!empty($post)) {
                        $old_value = $post->post_content;
                        $edit_result = MetaSeo_Broken_Link_Table::edit_linkimg($old_value, $new_link, $link_detail->link_url);
                        $my_post = array(
                            'ID' => $link_detail->source_id,
                            'post_content' => $edit_result['content']
                        );
                        remove_action('post_updated', array('MetaSeo_Broken_Link_Table', 'wpms_update_post'));
                        wp_update_post($my_post);
                        wp_send_json(array('status' => true, 'type' => 'image', 'status_text' => $status_text, 'new_link' => esc_url($edit_result['raw_url'])));
                    }
                    break;

                case 'comment_content_url':
                    $comment = get_comment($link_detail->source_id);
                    if (!empty($comment)) {
                        $old_value = $comment->comment_content;
                        if (isset($_POST['data_type']) && $_POST['data_type'] == 'multi' && $new_text == '') {
                            $edit_result = MetaSeo_Broken_Link_Table::wpms_edit_linkhtml($old_value, $new_link, $link_detail->link_url);
                            $new_text = '';
                        } else {
                            $edit_result = MetaSeo_Broken_Link_Table::wpms_edit_linkhtml($old_value, $new_link, $link_detail->link_url, $new_text);
                            $new_text = strip_tags($edit_result['link_text']);
                        }

                        $my_comment = array(
                            'comment_ID' => $link_detail->source_id,
                            'comment_content' => $edit_result['content']
                        );
                        remove_action('edit_comment', array('MetaSeo_Broken_Link_Table', 'wpms_update_comment'));
                        wp_update_comment($my_comment);
                        wp_send_json(array('status' => true, 'type' => 'url', 'status_text' => $status_text, 'new_link' => $edit_result['raw_url'], 'new_text' => $new_text));
                    }

                    break;

                case 'url':
                    $post = get_post($link_detail->source_id);
                    if (!empty($post)) {
                        $old_value = $post->post_content;
                        if (isset($_POST['data_type']) && $_POST['data_type'] == 'multi' && $new_text == '') {
                            $edit_result = MetaSeo_Broken_Link_Table::wpms_edit_linkhtml($old_value, $new_link, $link_detail->link_url);
                            $new_text = '';
                        } else {
                            $edit_result = MetaSeo_Broken_Link_Table::wpms_edit_linkhtml($old_value, $new_link, $link_detail->link_url, $new_text);
                            $new_text = strip_tags($edit_result['link_text']);
                        }

                        $my_post = array(
                            'ID' => $link_detail->source_id,
                            'post_content' => $edit_result['content']
                        );
                        remove_action('post_updated', array('MetaSeo_Broken_Link_Table', 'wpms_update_post'));
                        wp_update_post($my_post);
                        wp_send_json(array('status' => true, 'type' => 'url', 'status_text' => $status_text, 'new_link' => $edit_result['raw_url'], 'new_text' => $new_text));
                    }

                    break;
                case 'comment':
                    wp_update_comment(array('comment_ID' => $link_detail->source_id, 'comment_author_url' => $new_link));
                    wp_send_json(array('status' => true, 'type' => 'orther', 'status_text' => $status_text, 'new_link' => $new_link));
                    break;

                case 'add_custom':
                    wp_send_json(array('status' => true, 'type' => 'orther', 'status_text' => $status_text, 'new_link' => $new_link));
                    break;
            }
        }
        wp_send_json(false);
    }
    
    /*
     * Remove link
     */
    public static function wpms_unlink() {
        if (isset($_POST['link_id'])) {
            global $wpdb;
            $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "wpms_links WHERE id=%d", array($_POST['link_id']));
            $link_detail = $wpdb->get_row($sql);
            if (empty($link_detail))
                wp_send_json(false);
            $wpdb->delete($wpdb->prefix . 'wpms_links', array('id' => $_POST['link_id']));
            switch ($link_detail->type) {
                case 'add_rule':
                case '404_automaticaly':
                    wp_send_json(true);
                    break;

                case 'comment_content_image':
                    $comment = get_comment($link_detail->source_id);
                    if (!empty($comment)) {
                        $old_value = $comment->comment_content;
                        $new_content = MetaSeo_Broken_Link_Table::wpms_unlink_img($old_value, $link_detail->link_url);
                        remove_action('edit_comment', array('MetaSeo_Broken_Link_Table', 'wpms_update_comment'));
                        $my_comment = array(
                            'comment_ID' => $link_detail->source_id,
                            'comment_content' => $new_content
                        );
                        wp_update_comment($my_comment);
                    }
                    wp_send_json(true);
                    break;

                case 'image':
                    $post = get_post($link_detail->source_id);
                    if (!empty($post)) {
                        $old_value = $post->post_content;
                        $new_content = MetaSeo_Broken_Link_Table::wpms_unlink_img($old_value, $link_detail->link_url);
                        remove_action('post_updated', array('MetaSeo_Broken_Link_Table', 'wpms_update_post'));
                        $my_post = array(
                            'ID' => $link_detail->source_id,
                            'post_content' => $new_content
                        );
                        wp_update_post($my_post);
                    }
                    wp_send_json(true);
                    break;

                case 'comment_content_url':
                    $comment = get_comment($link_detail->source_id);
                    if (!empty($comment)) {
                        $old_value = $comment->comment_content;
                        $new_content = MetaSeo_Broken_Link_Table::wpms_unlink_html($old_value, $link_detail->link_url);
                        remove_action('edit_comment', array('MetaSeo_Broken_Link_Table', 'wpms_update_comment'));
                        $my_comment = array(
                            'comment_ID' => $link_detail->source_id,
                            'comment_content' => $new_content
                        );
                        wp_update_comment($my_comment);
                    }
                    wp_send_json(true);
                    break;

                case 'url':
                    $post = get_post($link_detail->source_id);
                    if (!empty($post)) {
                        $old_value = $post->post_content;
                        $new_content = MetaSeo_Broken_Link_Table::wpms_unlink_html($old_value, $link_detail->link_url);
                        remove_action('post_updated', array('MetaSeo_Broken_Link_Table', 'wpms_update_post'));
                        $my_post = array(
                            'ID' => $link_detail->source_id,
                            'post_content' => $new_content
                        );
                        wp_update_post($my_post);
                    }
                    wp_send_json(true);
                    break;
                case 'comment':
                    wp_update_comment(array('comment_ID' => $link_detail->source_id, 'comment_author_url' => ''));
                    wp_send_json(true);
                    break;
                case 'add_custom':
                    wp_send_json(true);
                    break;
            }
        }
        wp_send_json(false);
    }
    
    /**
    * Change all occurrences of a given plaintext URLs to a new URL.
    *
    * @param string $content Look for URLs in this string.
    * @param string $new_url Change them to this URL.
    * @param string $old_url The URL to look for.
    *
    * @return array|WP_Error If successful, the return value will be an associative array with two
    * keys : 'content' - the modified content, and 'raw_url' - the new raw, non-normalized URL used
    * for the modified links. In most cases, the returned raw_url will be equal to the new_url.
    */
    public static function edit_linkimg($content, $new_url, $old_url) {
        self::$old_url = $old_url;
        self::$new_url = htmlentities($new_url);
        $content = preg_replace_callback(self::$img_pattern, array('MetaSeo_Broken_Link_Table', 'editimg_callback'), $content);

        return array(
            'content' => $content,
            'raw_url' => self::$new_url,
        );
    }
  
    public static function editimg_callback($matches) {
        $url = $matches[3];
        if (($url) == self::$old_url) {
            return $matches[1] . '"' . self::$new_url . '"' . $matches[4];
        } else {
            return $matches[0];
        }
    }
    
    /**
    * Remove all occurrences of a specific plaintext URL.
    *
    * @param string $content	Look for URLs in this string.
    * @param string $url The URL to look for.
    * @return string Input string with all matching plaintext URLs removed.
    */
    public static function wpms_unlink_img($content, $url) {
        self::$old_url = $url; //used by the callback
        $content = preg_replace_callback(self::$img_pattern, array('MetaSeo_Broken_Link_Table', 'wpms_unlink_img_callback'), $content);
        return $content;
    }

    public static function wpms_unlink_img_callback($matches) {
        $url = $matches[3];
        if (($url) == self::$old_url) {
            return ''; //Completely remove the IMG tag
        } else {
            return $matches[0]; //return the image unchanged
        }
    }
    
    /**
    * Remove all occurrences of a specific plaintext URL.
    *
    * @param string $content	Look for URLs in this string.
    * @param string $url The URL to look for.
    * @return string Input string with all matching plaintext URLs removed.
    */
    public static function wpms_unlink_html($content, $url) {
        $args = array(
            'old_url' => $url,
        );

        $content = MetaSeo_Broken_Link_Table::wpms_multi_edit($content, array('MetaSeo_Broken_Link_Table', 'wpms_unlink_html_callback'), $args);

        return $content;
    }
 
    public static function wpms_unlink_html_callback($link, $params) {
        if ($link['href'] != $params['old_url']) {
            return $link['#raw'];
        }

        return $link['#link_text'];
    }
    
    /**
    * Change all occurrences of a given plaintext URLs to a new URL.
    *
    * @param string $content Look for URLs in this string.
    * @param string $new_url Change them to this URL.
    * @param string $old_url The URL to look for.
    * @param string $new_text new text of this URL.
    * @return array|WP_Error If successful, the return value will be an associative array with two
    * keys : 'content' - the modified content, and 'raw_url' - the new raw, non-normalized URL used
    * for the modified links. In most cases, the returned raw_url will be equal to the new_url.
    */
    public static function wpms_edit_linkhtml($content, $new_url, $old_url, $new_text = null) {
        //Save the old & new URLs for use in the edit callback.
        $args = array(
            'old_url' => $old_url,
            'new_url' => $new_url,
            'new_text' => $new_text,
        );

        //Find all links and replace those that match $old_url.
        $content = MetaSeo_Broken_Link_Table::wpms_multi_edit($content, array('MetaSeo_Broken_Link_Table', 'wpms_edithtml_callback'), $args);

        $result = array(
            'content' => $content,
            'raw_url' => $new_url,
        );
        if (isset($new_text)) {
            $result['link_text'] = $new_text;
        }
        return $result;
    }
  
    public static function wpms_edithtml_callback($link, $params) {
        if ($link['href'] == $params['old_url']) {
            $modified = array(
                'href' => $params['new_url'],
            );
            if (isset($params['new_text'])) {
                $modified['#link_text'] = $params['new_text'];
            }

            if (isset($params['meta_title'])) {
                $modified['title'] = $params['meta_title'];
            }

            if (isset($params['follow']) && $params['follow'] == 0) {
                $modified['rel'] = 'nofollow';
            } else {
                $modified['rel'] = '';
            }
            return $modified;
        } else {
            return $link['#raw'];
        }
    }
    
    /**
    * Helper function for blcHtmlLink::multi_edit()
    * Applies the specified callback function to each link and merges 
    * the result with the current link attributes. If the callback returns
    * a replacement HTML tag instead, it will be stored in the '#new_raw'
    * key of the return array. 
    *
    * @access protected
    *
    * @param array $link
    * @param array $info The callback function and the extra argument to pass to that function (if any).
    * @return array
    */
    public static function wpms_edit_callback($link, $info) {
        list($callback, $extra) = $info;

        //Prepare arguments for the callback
        $params = array($link);
        if (isset($extra)) {
            $params[] = $extra;
        }

        $new_link = call_user_func_array($callback, $params);

        if (is_array($new_link)) {
            $link = array_merge($link, $new_link);
        } elseif (is_string($new_link)) {
            $link['#new_raw'] = $new_link;
        }

        return $link;
    }
    
    /**
    * Modify all HTML links found in a string using a callback function.
    * The callback function should return either an associative array or a string. If 
    * a string is returned, the parser will replace the current link with the contents
    * of that string. If an array is returned, the current link will be modified/rebuilt
    * by substituting the new values for the old ones.
    * htmlentities() will be automatically applied to attribute values (but not to #link_text).   
    * @param string $content A text string containing the links to edit.
    * @param callback $callback Callback function used to modify the links.
    * @param mixed $extra If supplied, $extra will be passed as the second parameter to the function $callback. 
    * @return string The modified input string. 
    */
    public static function wpms_multi_edit($content, $callback, $extra = null) {
        //Just reuse map() + a little helper func. to apply the callback to all links and get modified links
        $modified_links = MetaSeo_Broken_Link_Table::map($content, array('MetaSeo_Broken_Link_Table', 'wpms_edit_callback'), array($callback, $extra));
        //Replace each old link with the modified one
        $offset = 0;
        foreach ($modified_links as $link) {
            if (isset($link['#new_raw'])) {
                $new_html = $link['#new_raw'];
            } else {
                //Assemble the new link tag
                $new_html = '<a';
                foreach ($link as $name => $value) {
                    //Skip special keys like '#raw' and '#offset'
                    if (substr($name, 0, 1) == '#') {
                        continue;
                    }

                    $new_html .= sprintf(' %s="%s"', $name, esc_attr($value));
                }
                $new_html .= '>' . $link['#link_text'] . '</a>';
            }

            $content = substr_replace($content, $new_html, $link['#offset'] + $offset, strlen($link['#raw']));
            //Update the replacement offset
            $offset += ( strlen($new_html) - strlen($link['#raw']) );
        }

        return $content;
    }
    
    /**
    * extract_tags()
    * Extract specific HTML tags and their attributes from a string.
    *
    * You can either specify one tag, an array of tag names, or a regular expression that matches the tag name(s). 
    * If multiple tags are specified you must also set the $selfclosing parameter and it must be the same for 
    * all specified tags (so you can't extract both normal and self-closing tags in one go).
    * 
    * The function returns a numerically indexed array of extracted tags. Each entry is an associative array
    * with these keys :
    * 	tag_name	- the name of the extracted tag, e.g. "a" or "img".
    *	offset		- the numberic offset of the first character of the tag within the HTML source.
    *	contents	- the inner HTML of the tag. This is always empty for self-closing tags.
    *	attributes	- a name -> value array of the tag's attributes, or an empty array if the tag has none.
    *	full_tag	- the entire matched tag, e.g. '<a href="http://example.com">example.com</a>'. This key 
    *		          will only be present if you set $return_the_entire_tag to true.	   
    *
    * @param string $html The HTML code to search for tags.
    * @param string|array $tag The tag(s) to extract.							 
    * @param bool $selfclosing	Whether the tag is self-closing or not. Setting it to null will force the script to try and make an educated guess. 
    * @param bool $return_the_entire_tag Return the entire matched tag in 'full_tag' key of the results array.  
    * @param string $charset The character set of the HTML code. Defaults to ISO-8859-1.
    *
    * @return array An array of extracted tags, or an empty array if no matching tags were found. 
    */
    public static function extract_tags($html, $tag, $selfclosing = null, $return_the_entire_tag = false, $charset = 'ISO-8859-1') {

        if (is_array($tag)) {
            $tag = implode('|', $tag);
        }

        $selfclosing_tags = array('area', 'base', 'basefont', 'br', 'hr', 'input', 'img', 'link', 'meta', 'col', 'param');
        if (is_null($selfclosing)) {
            $selfclosing = in_array($tag, $selfclosing_tags);
        }

        if ($selfclosing) {
            $tag_pattern = '@<(?P<tag>' . $tag . ')			# <tag
				(?P<attributes>\s[^>]+)?		# attributes, if any
				\s*/?>							# /> or just >, being lenient here 
				@xsi';
        } else {
            $tag_pattern = '@<(?P<tag>' . $tag . ')			# <tag
				(?P<attributes>\s[^>]+)?		# attributes, if any
				\s*>							# >
				(?P<contents>.*?)				# tag contents
				</(?P=tag)>						# the closing </tag>
				@xsi';
        }

        $attribute_pattern = '@
			(?P<name>\w+)											# attribute name
			\s*=\s*
			(
				(?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)	# a quoted value
				|							# or
				(?P<value_unquoted>[^\s"\']+?)(?:\s+|$)				# an unquoted value (terminated by whitespace or EOF) 
			)
			@xsi';

        //Find all tags 
        if (!preg_match_all($tag_pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            //Return an empty array if we didn't find anything
            return array();
        }

        $tags = array();
        foreach ($matches as $match) {

            //Parse tag attributes, if any
            $attributes = array();
            if (!empty($match['attributes'][0])) {

                if (preg_match_all($attribute_pattern, $match['attributes'][0], $attribute_data, PREG_SET_ORDER)) {
                    //Turn the attribute data into a name->value array
                    foreach ($attribute_data as $attr) {
                        if (!empty($attr['value_quoted'])) {
                            $value = $attr['value_quoted'];
                        } else if (!empty($attr['value_unquoted'])) {
                            $value = $attr['value_unquoted'];
                        } else {
                            $value = '';
                        }

                        //Passing the value through html_entity_decode is handy when you want
                        //to extract link URLs or something like that. You might want to remove
                        //or modify this call if it doesn't fit your situation.
                        $value = html_entity_decode($value, ENT_QUOTES, $charset);

                        $attributes[$attr['name']] = $value;
                    }
                }
            }

            $tag = array(
                'tag_name' => $match['tag'][0],
                'offset' => $match[0][1],
                'contents' => !empty($match['contents']) ? $match['contents'][0] : '', //empty for self-closing tags
                'attributes' => $attributes,
            );
            if ($return_the_entire_tag) {
                $tag['full_tag'] = $match[0][0];
            }

            $tags[] = $tag;
        }

        return $tags;
    }
    
    /**
    * Apply a callback function to all HTML links found in a string and return the results.
    *
    * The link data array will contain at least these keys :
    *  'href' - the URL of the link (with htmlentitydecode() already applied).
    *  '#raw' - the raw link code, e.g. the entire '<a href="...">...</a>' tag of a HTML link.
    *  '#offset' - the offset within $content at which the first character of the link tag was found.
    *  '#link_text' - the link's anchor text, if any. May contain HTML tags.
    * 
    * Any attributes of the link tag will also be included in the returned array as attr_name => attr_value
    * pairs. This function will also automatically decode any HTML entities found in attribute values.   
    * @param string $content A text string to parse for links. 
    * @param callback $callback Callback function to apply to all found links.  
    * @param mixed $extra If the optional $extra param. is supplied, it will be passed as the second parameter to the function $callback. 
    * @return array An array of all detected links after applying $callback to each of them.
    */
    public static function map($content, $callback, $extra = null) {
        $results = array();

        //Find all links
        $links = MetaSeo_Broken_Link_Table::extract_tags($content, 'a', false, true);

        //Iterate over the links and apply $callback to each
        foreach ($links as $link) {

            //Massage the found link into a form required for the callback function
            $param = $link['attributes'];
            $param = array_merge(
                    $param, array(
                '#raw' => $link['full_tag'],
                '#offset' => $link['offset'],
                '#link_text' => $link['contents'],
                'href' => isset($link['attributes']['href']) ? $link['attributes']['href'] : '',
                    )
            );

            //Prepare arguments for the callback
            $params = array($param);
            if (isset($extra)) {
                $params[] = $extra;
            }

            //Execute & store :)
            $results[] = call_user_func_array($callback, $params);
        }

        return $results;
    }
    
    /*
     * Ajax recheck link
     */
    public static function wpms_recheck_link() {
        if (isset($_POST['link_id'])) {
            global $wpdb;
            $linkId = $_POST['link_id'];
            $sql = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "wpms_links WHERE id=%d", array($linkId));
            $link = $wpdb->get_row($sql);
            if (!empty($link)) {
                if($link->link_url_redirect != ''){
                    $status = 'HTTP/1.1 200 OK';
                }else{
                    $status = MetaSeo_Broken_Link_Table::get_urlstatus(($link->link_url));
                }

                $status_text = MetaSeo_Broken_Link_Table::wpms_get_status_text($status);

                if ($link->type == '404_automaticaly') {
                    if (((int) substr($status, 9, 3) >= 301 && (int) substr($status, 9, 3) <= 304) || ((int) substr($status, 9, 3) >= 400 && (int) substr($status, 9, 3) <= 503 && (int) substr($status, 9, 3) != 401) || $status == 'Server Not Found') {
                        $type = array('broken_indexed' => 1, 'broken_internal' => 0);
                    } else {
                        $type = array('broken_indexed' => 0, 'broken_internal' => 0);
                    }
                } else {
                    if (((int) substr($status, 9, 3) >= 400 && (int) substr($status, 9, 3) <= 503 && (int) substr($status, 9, 3) != 401) || $status == 'Server Not Found') {
                        $type = array('broken_internal' => 1, 'broken_indexed' => 0);
                    } else {
                        $type = array('broken_internal' => 0, 'broken_indexed' => 0);
                    }
                }

                $value = array(
                    'status_code' => $status,
                    'status_text' => $status_text,
                    'broken_indexed' => $type['broken_indexed'],
                    'broken_internal' => $type['broken_internal']
                );

                $wpdb->update(
                        $wpdb->prefix . 'wpms_links', $value, array('ID' => $_POST['link_id'])
                );
                wp_send_json(array('status' => true, 'status_text' => $status_text));
            }
            wp_send_json(array('status' => false));
        }
    }

}
