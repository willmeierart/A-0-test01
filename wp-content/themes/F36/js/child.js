jQuery( document ).ready(function() {

    // pass attributes to GTM
	jQuery('#et-info-phone > a').attr('data-gtm', 'click-to-call');
	jQuery('#et-info > a').attr('data-gtm', 'email');

    jQuery( "[title^='data-gtm']" ).each(function() {
        // split title attribute at .
        var attrVal = jQuery(this).attr('title').split('.')[1];
        // create data attribute, with split[1] as the value
        jQuery(this).attr('data-gtm', attrVal);
    });

    jQuery("[title^='data-gtm']").attr('title').split('.')[1];

    // wpcf7 redirect
	document.addEventListener( 'wpcf7mailsent', function( event ) {
	    jQuery( ".entry-content" ).fadeOut( 500, function() {
		    location = '/contact/thank-you';
		});
	}, false );

    // copy the phone number to the lockup
    var phonelink = jQuery("#top-header .az-phone a").clone();
    phonelink.appendTo(".logo_addl");

    // search placeholder
    jQuery('#s').attr('placeholder', 'Search');

    //dynamic subject lines
    var FCsrc = "";
    switch (window.location.pathname) {
        // PPC Info & Kit
        case '/tarmac/next-steps-1':
        case '/tarmac/next-steps-2':
        case '/tarmac/information-kit':
            FCsrc = "FIT36 PPC Franchise Info Lead"
            break;
        // General Info & Kit
        case '/franchise-kit':
        case '/lets-talk':
        case '/details/territories':
            FCsrc = "FIT 36 Franchise Inquiry"
            break;
        // Safety net
        default:
            FCsrc = "FIT 36 Franchise Inquiry"
    }
    if(jQuery('input[name="FCsrc"]').length){
        jQuery('input[name="FCsrc"]').val(FCsrc);
    }

    // populate attribute for images that are missing alt (accessibility)
    var altOptions=[
        "FIT36 Franchise"
        ,"Recurring Revenue"
        ,"Simple Model"
        ,"Focused Business Concept"
        ,"World Class Support"
        ,"Growing Industry"
        ,"Low Start-up Costs"
        ,"Wide-open Territory"
    ];
    jQuery('img').each(function(){
        if(!jQuery( this ).attr( "alt" )){
            jQuery(this).attr("alt", altOptions[Math.floor(Math.random()*altOptions.length)] );
        }
    });

    // strip id from top-menu
    jQuery('#top-menu li').each(function(){
        jQuery(this).removeAttr('id');
    });

    // add title to video iframes
    jQuery('iframe').each(function(){
        if( !jQuery( this ).attr("title") ){
            jQuery(this).attr("title", "FIT36 Fitness franchise video");
        }
    })

    // clone CF7 form field names to ids
    jQuery('.wpcf7-form-control').each(function(){
        console.log(jQuery(this).attr('name'));
        if(jQuery( this ).attr( 'type') !== 'submit' ){
            var attrVal = jQuery(this).attr('name');
            jQuery(this).attr('id', attrVal);
        }
    });

    // exit intent
    jQuery(document).on('mouseleave', leaveFromTop);
    function leaveFromTop(e){
        var doc = document.documentElement;
        if( e.clientY < 0 ){
            console.log('EXIT TOP');
        }else if( e.clientX > doc.clientWidth ){
            console.log('EXIT RIGHT');
        }else if( e.clientY > doc.clientHeight ){
            console.log('EXIT BOTTOM');
        }else if( e.clientX < 0 ){
            console.log('EXIT LEFT');
        }
    }
});





