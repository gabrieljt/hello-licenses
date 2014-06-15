// Aliasing the jQuery Namespace: http://api.jquery.com/ready/

jQuery( document ).ready(function( $ ) {
    $( "#hello_licenses_id_input" ).change(function () {
        var license_id;
        $( "select option:selected" ).each(function() {
            license_id = $( this ).text().replace( " ", "" );
        });        
        var license_description = $( "#hello_licenses_description_" + license_id ).text();
        var license_url         = $( "#hello_licenses_url_" + license_id ).text();
        var license_image       = $( "#hello_licenses_image_" + license_id ).text();
        $( "#hello_licenses_description_input" ).val( license_description );
        $( "#hello_licenses_url_input" ).val( license_url );
        $( "#hello_licenses_image_input" ).val( license_image );
        $( "#hello_licenses_description" ).html( license_description );
        $( "#hello_licenses_url" ).html( license_url );
        $( "#hello_licenses_image" ).html( license_image );
    }).change();
});
