function wpms_cat_status_length(len, mclass) {
    if (len < 0) {
        jQuery(mclass).addClass('length-wrong').removeClass('length-true');
    } else {
        jQuery(mclass).addClass('length-true').removeClass('length-wrong');
    }
}

function wpms_cat_show_length(){
    var titleElm = jQuery('.wpms_category_metatitle');
    if(titleElm.length > 0){
        if (titleElm.val() !== '') {
            var len = wpms_localize.wpms_cat_metatitle_length - titleElm.val().length;
            wpms_cat_status_length(len, '.cat-title-len');
            jQuery('.cat-title-len').html(len);
        } else {
            jQuery('.cat-title-len').addClass('length-true').removeClass('length-wrong').html('<span class="good">' + wpms_localize.wpms_cat_metatitle_length + '</span>');
        }
    }

    var descElm = jQuery('.wpms_category_metadesc');
    if(descElm.length > 0) {
        if (descElm.val() !== '') {
            var len = wpms_localize.wpms_cat_metadesc_length - descElm.val().length;
            wpms_cat_status_length(len, '.cat-desc-len');
            jQuery('.cat-desc-len').html(len);
        } else {
            jQuery('.cat-desc-len').addClass('length-true').removeClass('length-wrong').html('<span class="good">' + wpms_localize.wpms_cat_metadesc_length + '</span>');
        }
    }
}

jQuery(document).ready(function ($) {
    jQuery('.wpms_custom_cat_field').qtip({
        content: {
            attr: 'alt'
        },
        position: {
            my: 'bottom left',
            at: 'top center'
        },
        style: {
            tip: {
                corner: true,
            },
            classes: 'metaseo-qtip qtip-rounded'
        },
        show: 'hover',
        hide: {
            fixed: true,
            delay: 10
        }

    });

    jQuery('.wpms_category_metatitle').keyup(function () {
        var titleElm = jQuery(this);

        if (titleElm.val() !== '') {
            var len = wpms_localize.wpms_cat_metatitle_length - titleElm.val().length;
            wpms_cat_status_length(len, '.cat-title-len');
            jQuery('.cat-title-len').html(len);
        } else {
            jQuery('.cat-title-len').addClass('length-true').removeClass('length-wrong').html('<span class="good">' + wpms_localize.wpms_cat_metatitle_length + '</span>');
        }
    });

    jQuery('.wpms_category_metadesc').keyup(function () {
        var descElm = jQuery(this);

        if (descElm.val() !== '') {
            var len = wpms_localize.wpms_cat_metadesc_length - descElm.val().length;
            wpms_cat_status_length(len, '.cat-desc-len');
            jQuery('.cat-desc-len').html(len);
        } else {
            jQuery('.cat-desc-len').addClass('length-true').removeClass('length-wrong').html('<span class="good">' + wpms_localize.wpms_cat_metadesc_length + '</span>');
        }
    });

    jQuery('.wpms_category_metakeywords').keyup(function () {
        var titleElm = jQuery(this);

        if (titleElm.val() !== '') {
            var len = wpms_localize.wpms_cat_metakeywords_length - titleElm.val().length;
            wpms_cat_status_length(len, '.cat-keywords-len');
            jQuery('.cat-keywords-len').html(len);
        } else {
            jQuery('.cat-keywords-len').addClass('length-true').removeClass('length-wrong').html('<span class="good">' + wpms_localize.wpms_cat_metakeywords_length + '</span>');
        }
    });

    wpms_cat_show_length();
});