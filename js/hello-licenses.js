// Aliasing the jQuery Namespace: http://api.jquery.com/ready/

jQuery( document ).ready(function( $ ) {
    $( '#hello_licenses_id_input' ).change(function () {
        var license_id;
        $( 'select option:selected' ).each(function() {
            license_id = $( this ).text().replace( ' ', '' );
        });        
        var license_description = $( '#hello_licenses_description_' + license_id ).text();
        var license_url         = $( '#hello_licenses_url_' + license_id ).text();
        var license_image       = $( '#hello_licenses_image_' + license_id ).text();
        
        $( '#hello_licenses_description_input' ).val( license_description );
        $( '#hello_licenses_description' ).html( license_description );

        if ( license_url ) {            
            $( '#hello_licenses_url_input' ).val( license_url );        
            $( '#hello_licenses_url' ).html( '<a href="' + license_url + '" target="_blank">' + license_url + '</a>' );
            $( '#hello_licenses_url_label' ).show();
            $( '#hello_licenses_url' ).show();
        } else {
            $( '#hello_licenses_url_label' ).hide();
            $( '#hello_licenses_url' ).hide();
        }

        if ( license_image ) {
            $( '#hello_licenses_image_input' ).val( license_image );        
            $( '#hello_licenses_image' ).html( '<img src="' + license_image + '" alt="License Logo">' );
            $( '#hello_licenses_image' ).show();
        } else {
            $( '#hello_licenses_image' ).hide();
        }

    }).change();
});
