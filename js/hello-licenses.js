// Aliasing the jQuery Namespace: http://api.jquery.com/ready/

jQuery( document ).ready(function( $ ) {
    $( "#hello_licenses_id" ).change(function () {
        var license_id;
        $( "select option:selected" ).each(function() {
            license_id = $( this ).text().replace( " ", "" );
        });        
        var license_description = $( "#hello_licenses_descriptions_" + license_id ).text();
        $( "#hello_licenses_description" ).val( license_description );
    }).change();
});
