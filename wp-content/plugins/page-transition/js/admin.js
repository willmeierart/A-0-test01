jQuery( document ).ready(function( $ ) {
	$( '#loading_color' ).wpColorPicker();
	$( '#show_loading' ).click(function() {
		if( $(this).prop( 'checked' ) ) {
			$( '#loading_color_field' ).show();
		} else {
			$( '#loading_color_field' ).hide();
		}
	});
});