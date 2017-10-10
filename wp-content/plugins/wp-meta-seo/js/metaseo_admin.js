/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var title_max_len = 69;
var desc_max_len = 156;
var keywords_max_len = 256;
var metaseoValueHolder = {};
var iUnchecked = 0;

function metaseo_clean(str) {
    if (str == '' || str == undefined)
        return '';
    try {
        str = jQuery('<div/>').html(str).text();
        str = str.replace(/<\/?[^>]+>/gi, '');
        str = str.replace(/\[(.+?)\](.+?\[\/\\1\])?/g, '');
    } catch (e) {
    }

    return str;
}

var oldTitleValues = {};
var oldKeywordsValues = {};
var oldDescValues = {};
var metaContentChangeWait;

function metaseo_titlelength(metatitle_id, needToSave, updateSnippet) {
    var title = jQuery.trim(metaseo_clean(jQuery('#' + metatitle_id).val()));
    var postid = metatitle_id.replace('metaseo-metatitle-', '');
    var counter_id = 'metaseo-metatitle-len' + postid;
    jQuery('#' + counter_id).text(title_max_len - title.length);
    if (title.length >= title_max_len) {
        jQuery('#' + counter_id).addClass('word-exceed');//#FEFB04
    } else {
        jQuery('#' + counter_id).removeClass('word-exceed');
    }

    if (title.length > title_max_len) {
        jQuery('#snippet_title' + postid).empty().text(title.substr(0, title_max_len));
    }

    if (typeof updateSnippet == "undefined" || updateSnippet !== false) {
        jQuery('#snippet_title' + postid).text(title.substr(0, title_max_len));
    }
}

function metaseo_updateTitle(metatitle_id, needToSave, updateSnippet) {
    var title = jQuery.trim(metaseo_clean(jQuery('#' + metatitle_id).val()));
    var postid = metatitle_id.replace('metaseo-metatitle-', '');
    if (needToSave === true && oldTitleValues[postid] != title) {
        saveMetaContentChanges('metatitle', postid, title);
    }

    //Push the new value into the array
    oldTitleValues[postid] = title;
}

function metaseo_keywordlength(metakeywords_id, needToSave, updateSnippet) {
    var keywords = jQuery.trim(metaseo_clean(jQuery('#' + metakeywords_id).val()));
    var postid = metakeywords_id.replace('metaseo-metakeywords-', '');

    var counter_id = 'metaseo-metakeywords-len' + postid;
    jQuery('#' + counter_id).text(keywords_max_len - keywords.length);

    if (keywords.length >= keywords_max_len) {
        jQuery('#' + counter_id).addClass('word-exceed');
    } else {
        jQuery('#' + counter_id).removeClass('word-exceed');
    }
}

function metaseo_updatekeywords(metakeywords_id, needToSave, updateSnippet) {
    var keywords = jQuery.trim(metaseo_clean(jQuery('#' + metakeywords_id).val()));
    var postid = metakeywords_id.replace('metaseo-metakeywords-', '');
    if (needToSave === true && oldKeywordsValues[postid] != keywords) {
        saveMetaContentChanges('metakeywords', postid, keywords);
    }

    //Push the new value into the array
    oldKeywordsValues[postid] = keywords;
}

function saveMetaLinkChanges(button_update) {
    var link_id = jQuery(button_update).closest('tr').data('link');
    var meta_title = jQuery(button_update).closest('tr').find('.metaseo_link_title').val();
    meta_title = jQuery.trim(metaseo_clean(meta_title));
    jQuery.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'json',
        data: {
            'action': 'metaseo_update_link',
            'link_id': link_id,
            'meta_title': meta_title,
        },
        success: function (response) {
            jQuery(button_update).closest('tr').find('.wpms_update_link').hide();
            if (response != false) {
                jQuery(button_update).closest('tr').find('.wpms_old_link').val(response.link_new).change();
                jQuery(button_update).closest('tr').find('.wpms_mesage_link').show().fadeIn(3000).delay(200).fadeOut(3000);
            } else {
                jQuery(button_update).closest('tr').find('.wpms_error_mesage_link').show().fadeIn(3000).delay(200).fadeOut(3000);
            }

        }
    });
}

function metaseo_update_pageindex(page_id, index) {
    jQuery.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'json',
        data: {
            'action': 'metaseo_update_pageindex',
            'page_id': page_id,
            'index': index,
        },
        success: function (response) {

        }
    });
}

function metaseo_update_pagefollow(page_id, follow) {
    jQuery.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'json',
        data: {
            'action': 'metaseo_update_pagefollow',
            'page_id': page_id,
            'follow': follow,
        },
        success: function (response) {

        }
    });
}

function wpmsChangeFollow(button) {
    var link_id = jQuery(button).closest('tr').data('link');
    var type = jQuery(button).data('type');
    var follow = 1;
    if (type == 'done') {
        jQuery(button).data('type', 'warning').html('warning');
        jQuery(button).removeClass('wpms_ok').addClass('wpms_warning');
        follow = 0;
    } else {
        jQuery(button).data('type', 'done').html('done');
        jQuery(button).removeClass('wpms_warning').addClass('wpms_ok');
        follow = 1;
    }

    jQuery.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'json',
        data: {
            'action': 'metaseo_update_follow',
            'link_id': link_id,
            'follow': follow,
        },
        success: function (response) {

        }
    });
}

function wpms_scan_link($this) {
    if ($this.hasClass('page_link_meta')) {
        jQuery('.spinner_apply_follow').css('visibility', 'visible').show();
    }
    jQuery.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'json',
        data: {
            'action': 'wpms_scan_link',
            'paged': $this.data('paged'),
            'comment_paged': $this.data('comment_paged')
        },
        success: function (res) {
            if (res.status == false) {

                if (res.type == 'limit') {
                    var wpms_process = jQuery('.wpms_process').data('w');
                    var wpms_process_new = parseFloat(wpms_process) + parseFloat(res.percent);
                    if (wpms_process_new > 100)
                        wpms_process_new = 100;
                    jQuery('.wpms_process').data('w', wpms_process_new);
                    jQuery('.wpms_process').css('width', wpms_process_new + '%').show();
                    $this.click();
                } else if (res.type == 'limit_comment_content') {
                    var wpms_process = jQuery('.wpms_process').data('w');
                    if (wpms_process < 33.33)
                        wpms_process = 33.33;
                    var wpms_process_new = parseFloat(wpms_process) + parseFloat(res.percent);
                    if (wpms_process_new > 100)
                        wpms_process_new = 100;
                    jQuery('.wpms_process').data('w', wpms_process_new);
                    jQuery('.wpms_process').css('width', wpms_process_new + '%').show();
                    $this.data('comment_paged', parseInt(res.paged) + 1);
                    $this.click();
                } else {
                    var wpms_process = jQuery('.wpms_process').data('w');
                    if (wpms_process < 66.66)
                        wpms_process = 66.66;
                    var wpms_process_new = parseFloat(wpms_process) + parseFloat(res.percent);
                    if (wpms_process_new > 100)
                        wpms_process_new = 100;
                    jQuery('.wpms_process').data('w', wpms_process_new);
                    jQuery('.wpms_process').css('width', wpms_process_new + '%').show();
                    $this.data('paged', parseInt(res.paged) + 1);
                    $this.click();
                }
            } else {
                jQuery('.wpms_process').data('w', 100);
                jQuery('.wpms_process').css('width', '100%');
                jQuery('#wp-seo-meta-form .spinner').hide();
                if ($this.hasClass('page_link_meta')) {
                    jQuery('.spinner_apply_follow').hide();
                }
                window.location.assign(document.URL);
            }
        }
    });
}

function wpmsUpdateFollow(button) {
    var $this = jQuery(button);
    jQuery('.spinner_apply_follow').css('visibility', 'visible').show();
    var follow_value = jQuery('.metaseo_follow_action').val();
    if (follow_value == 'follow_selected' || follow_value == 'nofollow_selected') {
        if (follow_value == 0)
            return;

        var link_selected = [];
        jQuery(".metaseo_link").each(function (index) {
            if (jQuery(this).is(':checked')) {
                link_selected.push(jQuery(this).val());
            }
        });
        if (link_selected.length == 0)
            return;
        var data = {
            action: 'metaseo_update_multiplefollow',
            linkids: link_selected,
            follow_value: follow_value
        };
    } else {
        var data = {
            action: 'metaseo_update_multiplefollow',
            follow_value: follow_value
        };
    }
    jQuery.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'json',
        data: data,
        success: function (response) {
            if (response.status == true) {
                jQuery('.spinner_apply_follow').hide();
                window.location.assign(document.URL);
            } else {
                if (follow_value == 'follow_all' || follow_value == 'nofollow_all') {
                    if (response.message == 'limit') {
                        $this.click();
                    }
                }
            }
        }
    });
}

function metaseo_desclength(metadesc_id, needToSave) {
    var desc = jQuery.trim(metaseo_clean(jQuery('#' + metadesc_id).val()));
    var postid = metadesc_id.replace('metaseo-metadesc-', '');
    var counter_id = 'metaseo-metadesc-len' + postid;
    jQuery('#' + counter_id).text(desc_max_len - desc.length);

    if (desc.length >= desc_max_len) {
        jQuery('#' + counter_id).addClass('word-exceed');
    } else {
        jQuery('#' + counter_id).removeClass('word-exceed');
    }

    jQuery('#snippet_desc' + postid).text(desc.substr(0, desc_max_len));
}


function metaseo_updateDesc(metadesc_id, needToSave) {
    var desc = jQuery.trim(metaseo_clean(jQuery('#' + metadesc_id).val()));
    var postid = metadesc_id.replace('metaseo-metadesc-', '');
    if (needToSave === true && oldDescValues[postid] != desc) {
        saveMetaContentChanges('metadesc', postid, desc);
    }

    //Push the new value into the array
    oldDescValues[postid] = desc;
}

var autosaveNotification;
function saveMetaContentChanges(metakey, postid, data) {
    jQuery('.wpms_loader' + postid).show();
    var postData = {
        'action': 'updateContentMeta',
        'metakey': metakey,
        'postid': postid,
        'value': data
    };
    // We can also pass the url value separately from ajaxurl for front end AJAX implementations
    jQuery.post(wpms_localize.ajax_url, postData, function (response) {
        jQuery('.wpms_loader' + postid).hide();
        result = jQuery.parseJSON(response);

        if (result.updated == true) {
            autosaveNotification = setTimeout(function () {
                jQuery('#savedInfo' + postid).text(result.msg);
                jQuery('#savedInfo' + postid).fadeIn(200).delay(2000).fadeOut(1000);
            }, 1000);
        } else {
            alert(result.msg);
        }

    });


}

function checkspecial(element_id) {
    var element = jQuery(element_id);
    var meta_type = element.data('meta-type');

    if (meta_type == 'change_image_name') {
        var str = (element.val());
        if (/^[\w\d\-\s+_.$]*$/.test(str) == false) {
            return false;
        } else {
            return true;
        }
    } else {
        return true;
    }
}

function metaseo_update(element_id) {
    //metaseo-img-alt-4
    var element = jQuery(element_id);
    var post_id = element.data('post-id');
    var meta_type = element.data('meta-type');
    var meta_value = element.val();
}

function saveChanges(element_id, post_id, meta_type, meta_value) {

    var element = jQuery(element_id);
    var savedInfo = element.parent().find('span.saved-info');
    if (savedInfo.length < 1) {
        savedInfo = element.closest('td').find('span.saved-info');
    }
    var updated = false;
    var postData = {
        'action': 'updateMeta',
        'post_id': post_id,
        'meta_type': meta_type,
        'meta_value': meta_value,
        'img_name': element.closest('tr').find('.fix-metas').data('img-name'),
        'opt_key': element.closest('tr').find('.fix-metas').data('opt-key'),
        'addition': {
            'meta_key': element.data('meta-key'),
            'meta_type': element.data('meta-type'),
            'meta_value': element.val(),
            'meta_order': element.data('meta-order'),
            'img_post_id': element.data('img-post-id'),
            'post_id': element.data('post-id'),
        }
    };

    // We can also pass the url value separately from ajaxurl for front end AJAX implementations
    jQuery.ajax({
        url: wpms_localize.ajax_url,
        async: false,
        type: 'post',
        data: postData,
        dataType: 'json',
        beforeSend: function () {
            savedInfo.empty().append('<span class="spinner"></span>');
            if(element.hasClass('metaseo-fix-meta')){
                element.closest('.metaseo-img-wrapper').find('.spinner').css('visibility','visible').show();
            }else{
                element.parent().find('.spinner').css('visibility','visible').show();
            }
        },
        success: function (response) {
            if (response == 0) {
                saveChanges(element_id, post_id, meta_type, meta_value);
            }

            updated = response.updated;

            if (updated == true) {
                autosaveNotification = setTimeout(function () {

                    if(element.hasClass('metaseo-fix-meta')){
                        element.closest('.metaseo-img-wrapper').find('.spinner').hide();
                    }else{
                        element.parent().find('.spinner').hide();
                    }

                    savedInfo.removeClass('metaseo-msg-warning').addClass('metaseo-msg-success')
                            .text(response.msg).fadeIn(200);

                    setTimeout(function () {
                        savedInfo.empty().append('<span class="spinner"></span>');
                    }, 3000);

                }, 200);

                //update image's data-name attribute
                if (typeof element.data('extension') != 'undefined') {
                    jQuery('[data-img-post-id="' + element.data('post-id') + '"]').data('name', element.val() + element.data('extension'));
                }
                //Scan post and update post_meta
                var img = jQuery('[data-img-post-id="' + postData['addition']['img_post_id'] + '"]');
                if (img.length > 0) {
                    _metaSeoScanImages(
                            
                                {
                                    'name': img.data('name'),
                                    'img_post_id': postData['addition']['img_post_id'],
                                    'type': 'update_meta'
                                }

                            );
                }

                if(typeof response.type != "undefined" && response.type == 'auto_override'){
                    _metaSeoScanImages(
                        {
                            'name': response.imgname,
                            'img_post_id': response.pid,
                            'type': 'update_meta'
                        }

                    );
                }

            } else {
                element.val(response.iname);
                savedInfo.removeClass('metaseo-msg-success').addClass('metaseo-msg-warning')
                        .text(response.msg).fadeIn(200).delay(2000).fadeOut(200);
            }
        },
        error: function () {

        }
    });

    return updated;
}

//Scan all posts to find a group of images in their content
function metaSeoScanImages() {
    var imgs = [];
    jQuery('.metaseo-image').each(function (i) {
        if (jQuery(this).data('name') != '') {
            imgs[i] = {
                'name': jQuery(this).data('name'),
                'img_post_id': jQuery(this).data('img-post-id')
            };
        }
    });
    
    jQuery.each(imgs,function(i,v){
        _metaSeoScanImages(v);
    });

}

function _metaSeoScanImages(imgs) {
    if (imgs.length < 1) {
        //alert('No images choosen for scanning, please check again!');
        return false;
    }

    jQuery.ajax({
        url: wpms_localize.ajax_url,
        method: 'post',
        data: {'action': 'scanPosts', 'imgs': imgs},
        dataType: 'json',
        beforeSend: function () {},
        success: function (response) {
            if (response == 0) {
                _metaSeoScanImages(imgs);
            }
            //clog(imgs);
            if (response.success === true) {
                //Clear content holder first
                if (imgs.length === 1) {
                    jQuery('#opt-info-' + imgs[0]['img_post_id']).removeClass('opt-info-warning').empty();
                }
                var list_imNotGood = [];
                var list_iNotGood = [];
                //id is refered to image post id
                for (var iID in response.msg) {
                    jQuery('#opt-info-' + iID).html(null);
                    //Change css position property of td tag to default
                    jQuery('#opt-info-' + iID).parent().css('position', 'static');
                    jQuery('#opt-info-' + iID).append('<p class="btn-wrapper"></p>');

                    for (var msgType in response.msg[iID]) {
                        if (response.msg[iID][msgType]['warning'] == true
                                && !jQuery('#opt-info-' + iID).hasClass('opt-info-warning')) {
                            jQuery('#opt-info-' + iID).addClass('opt-info-warning');
                        }

                        jQuery('#opt-info-' + iID).find('p.btn-wrapper').append(response.msg[iID][msgType]['button']);
                        if (typeof response.msg[iID][msgType]['msg'] != 'object') {
                            var hlight = !response.msg[iID][msgType]['warning'] ? 'metaseo-msg-success' : '';
                            jQuery('#opt-info-' + iID).prepend('<p class="' + hlight + '">' + response.msg[iID][msgType]['msg'] + '</p>');
                        } else {
                            for (var k in response.msg[iID][msgType]['msg']) {
                                jQuery('#opt-info-' + iID).prepend('<p>' + response.msg[iID][msgType]['msg'][k] + '</p>');
                            }
                        }

                    }

                    jQuery('#opt-info-' + iID).parent().find('span.metaseo-loading').hide();
                }

                jQuery('.opt-info-warning').fadeIn(200);
                jQuery('input.metaseo-checkin').each(function (i, input) {
                    uncheck(input);
                });
            }

            jQuery('.fix-metas').qtip({
                content: {
                    attr: 'alt'
                },
                position: {
                    my: 'bottom center',
                    at: 'top center'
                },
                style: {
                    tip: {
                        corner: true,
                    },
                    classes: 'wpms-widgets-qtip_show_arow'
                },
                show: 'hover',
                hide: {
                    fixed: true,
                    delay: 10
                }
            });
        },
        error: function () {
           // alert('Errors occured while scanning posts for optimization');
        }
    });
}

//To fix meta of a specified image
function metaseo_fix_meta(that) {
    var $this = jQuery(that);

    if (checkspecial(that) === true) {

        if (that.jquery === undefined) {
            $this.bind('input propertychange', function () {
                metaseo_update(that);
            });
        } else {
            metaseo_update(that);
        }

    }
}

function add_meta_default(that) {
    var $this = jQuery(that);
    var input = $this.parent().find('input');
    var id = input.attr('id');

    input.val($this.data('default-value')).focus();
}
//--------------------------------
//Optimize a single post
function optimize_imgs(element) {
    var $this = jQuery(element);
    var post_id = $this.data('post-id');
    var img_post_id = $this.data('img-post-id');
    var checkin = jQuery('.checkin-' + post_id);
    var img_exclude = [];
    var not_checked_counter = 0;
    var updated = false;

    var j = 0;
    checkin.each(function (i, el) {
        if (!(jQuery(el).is(':checked'))) {
            not_checked_counter++;
            if (jQuery(el).val() != '' || jQuery(el).val() != 'undefined') {
                img_exclude[j] = parseInt(jQuery(el).val());
                j++;
            }
        }
    });

    if (checkin.length <= not_checked_counter) {
        //alert('No images has choosen. \\nPlease click on the checkbox in what image you want to replace!');
        return false;
    }

    if (!post_id && !img_post_id) {
        alert('Cant do the optimization because of missing image ID.\\nPlease check again!');
    } else {
        jQuery.ajax({
            url: wpms_localize.ajax_url,
            async: false,
            data: {
                'action': 'optimize_imgs',
                'post_id': post_id,
                'img_post_id': img_post_id,
                'img_exclude': img_exclude
            },
            dataType: 'json',
            type: 'post',
            beforeSend: function () {
                $this.parent().find('span.spinner').show();
            },
            success: function (response) {
                if (response == 0) {
                    optimize_imgs(element);
                }

                if (response.success) {
                    updated = true;

                    checkin.each(function (i, e) {
                        if (jQuery.inArray(parseInt(jQuery(this).val()), img_exclude) == -1) {

                            var img_choosen = jQuery(this).parent();
                            jQuery(this).remove();

                            img_choosen.empty().append('<span class="metaseo-checked"></span>');
                            img_choosen.parent().find('p.metaseo-msg').removeClass('msg-error').addClass('msg-success').empty().text(response.msg).fadeIn(200);
                            setTimeout(function () {
                                img_choosen.find('p.metaseo-msg').fadeOut(300);
                            }, 5000);

                        }
                    });

                    var checked = jQuery('.checkin-' + post_id);
                    if (checked.length == 0) {
                        $this.addClass('disabled');
                    }

                    $this.parent().find('span.spinner').fadeOut(300);
                    //Disable Replace all button if all image were resized
                    var metaseo_checkin = jQuery('.metaseo-checkin');

                    if (metaseo_checkin.length == 0) {
                        jQuery('#metaseo-replace-all').addClass('disabled');
                    }
                    //Scan post and update post_meta
                    var img = jQuery('[data-img-post-id="' + img_post_id + '"]');
                    _metaSeoScanImages({'name': img.data('name'), 'img_post_id': img_post_id});

                } else {
                    $this.parent().find('span.spinner').hide();
                    $this.parent().find('p.metaseo-msg').removeClass('msg-success').addClass('msg-error');
                }

            },
            error: function () {

            }
        });
    }

    img_exclude = [];
    return updated;
}

//Optimize all posts in list displayed
function optimize_imgs_group(that) {
    jQuery('a.metaseo-optimize').each(function (i, el) {
        if (i == 0) {
            jQuery(that).parent().find('span.spinner').show();
        }

        jQuery(this).click();

        if (i == (jQuery(el).length - 1)) {
            jQuery(that).parent().find('span.spinner').hide();
        }
    });

}

function uncheck(that) {
    var $this = jQuery(that);
    var post_id = that.className.substr(that.className.lastIndexOf('-') + 1);
    var checked = jQuery('.checkin-' + post_id);
    var not_checked_counter = 0;

    checked.each(function (i, e) {
        if (!(jQuery(this).is(':checked'))) {
            not_checked_counter++;
        }
    });

    //Toggle disable Replace button if all images in a post were resized
    if (not_checked_counter >= checked.length) {
        jQuery('a.metaseo-optimize[data-post-id="' + post_id + '"]').addClass('disabled');
    } else {
        jQuery('a.metaseo-optimize[data-post-id="' + post_id + '"]').removeClass('disabled');
    }

    //Toggle disable Replace all button if all images in posts were resized
    var replaceBtns = jQuery('.metaseo-optimize');
    var disable = true;
    replaceBtns.each(function (i, btn) {
        if (!jQuery(btn).hasClass('disabled')) {
            disable = false;
            return;
        }
    });

    if (disable === true) {
        jQuery('#metaseo-replace-all').addClass('disabled');
    } else {
        jQuery('#metaseo-replace-all').removeClass('disabled');
    }
}

//Show posts list for resizing or update meta info of an image with specified id
function showPostsList(element) {
    var that = jQuery(element);
    var data = {
        'action': 'load_posts',
        'img_name': that.data('img-name'),
        'post_id': that.data('post-id'),
        'opt_key': that.data('opt-key')
    };

    if (that.data('img-name') != '') {
        jQuery.ajax({
            url: wpms_localize.ajax_url,
            type: 'post',
            dataType: 'html',
            data: data,
            beforeSend: function () {
                that.find('.spinner-light').show();
            },
            success: function (response) {
                if (response == 0) {
                    showPostsList(element);
                }
                that.parent().find('.spinner-light').hide();
                that.closest('td.col_image_info').find('div.popup > .popup-content').empty().html(response).fadeIn(300);

                //to set background-color of popup-header to like adminmenu active 
                var metaseo_bg = jQuery('#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu').css('background-color');

                if (metaseo_bg !== 'undified') {
                    jQuery('.popup-content .content-header').css({'background-color': metaseo_bg, 'color': '#FFF'});
                    jQuery('span.popup-close').css({'color': '#FFF'});
                }

                that.showPopup(that);
            }
        });
    } else {
        alert('Something went wrong, please check Image name if it\'s empty before click Resize button');
    }
}

//Import meta data from other plugin into Wp Meta Seo
function importMetaData(that, event) {
    var plugin = that.id;
    var element = jQuery('#' + that.id);

    event.preventDefault();
    if (that.id === '_aio_' || that.id === '_yoast_') {
        jQuery.ajax({
            url: wpms_localize.ajax_url,
            type: 'post',
            data: {'action': 'import_meta_data', 'plugin': that.id},
            dataType: 'json',
            beforeSend: function () {
                element.find('span.spinner-light').show();
            },
            success: function (response) {
                if (response.success == true) {
                    element.find('span.spinner-light').fadeOut(500);
                    jQuery('.metaseo-import-wrn').closest('.error').fadeOut(1500);
                    //Refresh the page to see al changed after import Yoast or AIO data into MetaSEO
                    if (location.href.search('page=metaseo_content_meta') != -1) {
                        location.reload();
                    }
                }
            },
            error: function () {
                alert('Something went wrong in import processing!');
            }

        });
    }
}

//Update once input changed and blur
function updateInputBlur(that) {
    var element = jQuery(that);
    var post_id = element.data('post-id');
    var meta_type = element.data('meta-type');
    var meta_value = element.val();
    if (saveChanges(that, post_id, meta_type, meta_value)) {
        if (meta_type === 'change_image_name') {
            jQuery('a.img-resize[data-post-id="' + post_id + '"]').data('img-name', meta_value);
        }
    }
}

function checkeyCode(event, that) {
    if (event.which == 13 || event.keyCode == 13) {
        return false;
    }
}

/**
 * Check if a name is existed
 */
function is_existed(iname) {
    jQuery.each(metaseoValueHolder, function (i, iName) {
        for (var id in iName) {
            if (typeof iName[id + '_prev'] != 'undefined' && iname == iName[id + '_prev']) {
                return true;
            }
        }
    });
}

/**
 * Check if a image name is valid or not 
 */
function validateiName(iname) {
    var is_only_spaces = iname.length > 0 ? true : false;
    iname = iname.trim();
    var msg = '';

    if (iname.length < 1) {
        msg = !is_only_spaces ? 'Should not be empty' : 'Should not only spaces';
        return {msg: msg, name: ''};
    }

    return {msg: '', name: iname};
}

function wpms_image_scan_meta($this) {
    jQuery('.wpms_process_meta').show();
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: "wpms_image_scan_meta",
            paged: $this.data('paged')
        },
        success: function (res) {
            var w = jQuery('.wpms_process_meta').data('w');
            if (res.status == 'ok') {
                jQuery('.wpms_process_meta').data('w', 0);
                jQuery('.image_scan_meta').data('paged', 1);
                jQuery('.wpms_process_meta').css('width', '100%');
                jQuery('.wpms_process_meta').hide();
            }

            if (res.status == 'error_time') {
                if (typeof res.precent != "undefined") {
                    var new_w = parseFloat(w) + parseFloat(res.precent);
                    if (new_w > 100)
                        new_w = 100;
                    jQuery('.wpms_process_meta').show();
                    jQuery('.wpms_process_meta').data('w', new_w);
                    jQuery('.wpms_process_meta').css('width', new_w + '%');
                    $this.data('paged', parseInt(res.paged) + 1);
                }
                jQuery('.image_scan_meta').click();
            }
        }
    });
}

jQuery(document).ready(function ($) {
    $('#home_text_default').on('change', function () {
        if($(this).is(':checked')){
            $('.tr_home_text').addClass('show').removeClass('hide');
        }else{
            $('.tr_home_text').addClass('hide').removeClass('show');
        }
    });

    $('.image_scan_meta').on('click', function () {
        wpms_image_scan_meta($(this));
    });

    $('.wp-meta-seo_page_metaseo_image_meta #image-submit').click(function (e) {
        e.preventDefault();
        $('.imgspinner').show().css('visibility','visible');
        $('#wp-seo-meta-form').submit();
    });

    //Cursor changes on any ajax start and end
    //Thanks to iambriansreed from stacoverflow.com
    $('body').ajaxStart(function () {
        $(this).css({'cursor': 'wait'});
    }).ajaxStop(function () {
        $(this).css({'cursor': 'default'});
    });

    $('span.pagination-links a.disabled').click(function (e) {
        e.preventDefault();
    });

    $('.metaseo_imgs_per_page').bind('input propertychange', function () {
        var perpage = $(this).val();
        $('.metaseo_imgs_per_page').each(function (i, e) {
            if ($(e).val() != perpage) {
                $(e).val(perpage);
            }
        });
    });

    $('.metaseo_link_source').bind('change', function () {
        var value = $(this).val();
        $('.metaseo_link_source').each(function (i, e) {
            if ($(e).val() != value) {
                $(e).val(value);
            }
        });
    });

    $('.metaseo_follow_action').bind('change', function () {
        var value = $(this).val();
        $('.metaseo_follow_action').each(function (i, e) {
            if ($(e).val() != value) {
                $(e).val(value);
            }
        });
    });

    $('.metaseo-filter').bind('change', function () {
        var value = $(this).val();
        $('.metaseo-filter').each(function (i, e) {
            if ($(e).val() != value) {
                $(e).val(value);
            }
        });
    });

    $('.meta_filter').bind('change', function () {
        var value = $(this).val();
        $('.meta_filter').each(function (i, e) {
            if ($(e).val() != value) {
                $(e).val(value);
            }
        });
    });

    $('.redirect_fillter').bind('change', function () {
        var value = $(this).val();
        $('.redirect_fillter').each(function (i, e) {
            if ($(e).val() != value) {
                $(e).val(value);
            }
        });
    });

    $('.broken_fillter').bind('change', function () {
        var value = $(this).val();
        $('.broken_fillter').each(function (i, e) {
            if ($(e).val() != value) {
                $(e).val(value);
            }
        });
    });

    $('.wpms_duplicate_meta').bind('change', function () {
        var value = $(this).val();
        $('.wpms_duplicate_meta').each(function (i, e) {
            if ($(e).val() != value) {
                $(e).val(value);
            }
        });
    });

    $('.mbulk_copy').bind('change', function () {
        var value = $(this).val();
        $('.mbulk_copy').each(function (i, e) {
            if ($(e).val() != value) {
                $(e).val(value);
            }
        });
    });

    $('.metaseo-img-name').bind('input propertychange', function () {
        var savedInfo = $(this).parent().find('span.saved-info');
        var iname = validateiName($(this).val());
        var msg = iname.msg;
        if (iname.name.length > 0) {
            if (!checkspecial(this)) {
                msg = 'Should not special char';
            }
        }

        if (msg.length > 0) {
            //Set this value to metaseoValueHolder
            metaseoValueHolder[this.id] = iname.name.substr(0, iname.name.length - 1);

            savedInfo.removeClass('metaseo-msg-success')
                    .addClass('metaseo-msg-warning').empty().text(msg);
        }
    });

    $('.metaseo-img-meta').each(function (i, element) {
        if ($(this).hasClass('metaseo-img-name')) {
            metaseoValueHolder[this.id + '_prev'] = jQuery(this).val();
        }
        $(element).bind('keydown', function (event) {
            if (event.which == 13 || event.keyCode == 13) {
                return false;
            }
        });
    });

    $('.metaseo-img-meta').change(function () {
        if (jQuery(this).val() == '') {
            jQuery(this).val(metaseoValueHolder[this.id + '_prev']);
            $(this).parent().find('span.saved-info').empty().append('<span class="spinner"></span>');
        }
        if (checkspecial(this) === true) {
            updateInputBlur(this);
        }
    });

    $('.dissmiss-import').bind('click', function (e) {
        e.preventDefault();
        $(this).closest('.error').fadeOut(1000);
        setTimeout(function () {
            $(this).closest('.error').remove();
        }, 5000);

        var plugin = $(this).parent().find('a.button').attr('id');

        if (plugin === '_aio_' || plugin === '_yoast_') {
            $.ajax({
                url: wpms_localize.ajax_url,
                type: 'post',
                data: {'action': 'dismiss_import_meta', 'plugin': plugin},
                dataType: 'json',
                beforeSend: function () {

                },
                success: function (response) {
                    if (response.success !== true) {
                        alert('Dismiss failed!');
                    }
                }
            });
        }
    });

    $('.metaseo_metabox_follow').on('change', function () {
        var page_id = $(this).data('post_id');
        var follow = $(this).val();
        metaseo_update_pagefollow(page_id, follow);
    });

    $('.metaseo_metabox_index').on('change', function () {
        var page_id = $(this).data('post_id');
        var index = $(this).val();
        metaseo_update_pageindex(page_id, index);
    });
    //----------------------------------------------------------
    //Pop-up declaration
    $.fn.absoluteCenter = function () {
        this.each(function () {
            var top = -($(this).outerHeight() / 2) + 'px';
            var left = -($(this).outerWidth() / 2) + 'px';
            $(this).css({'position': 'fixed', 'top': $('div.wrap').offset().top, 'left': $('div.wrap').offset().left, 'right': '25px', 'bottom': '10px'});

            return this;
        });
    };

    $.fn.showPopup = function (that) {
        var bg = $('div.popup-bg');
        var obj = that.closest('.col_image_info').find('div.popup');
        var btnClose = obj.find('.popup-close');
        bg.animate({opacity: 0.2}, 0).fadeIn(200);
        obj.fadeIn(200).absoluteCenter();
        btnClose.click(function () {
            bg.fadeOut(100);
            obj.fadeOut(100).find('div.popup-content').empty();
        });
        bg.click(function () {
            btnClose.click();
        });
        $(document).keydown(function (e) {
            if (e.keyCode == 27) {
                btnClose.click();
            }
        });
        return false;
    };
    $('a.show-popup').bind('click', function () {
        $(this).showPopup($(this));
    });
});