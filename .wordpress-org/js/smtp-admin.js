/* globals jQuery */
jQuery( document ).ready( function ( $ ) {

	$( '.wop-wordpress-smtp-mail-mailer input' ).click( function () {
		if ( $( this ).prop( 'disabled' ) ) {
			return false;
		}

		// Deselect the current mailer.
		$( '.wop-wordpress-smtp-mail-mailer' ).removeClass( 'active' );
		// Select the correct one.
		$( this ).parents( '.wop-wordpress-smtp-mail-mailer' ).addClass( 'active' );

		$( '.wop-wordpress-smtp-mail-mailer-option' ).addClass( 'hidden' ).removeClass( 'active' );
		$( '.wop-wordpress-smtp-mail-mailer-option-' + $( this ).val() ).addClass( 'active' ).removeClass( 'hidden' );
	} );

	$( '.wop-wordpress-smtp-mail-mailer-image' ).click( function () {
		$( this ).parents( '.wop-wordpress-smtp-mail-mailer' ).find( 'input' ).trigger( 'click' );
	} );

	$( '.wop-wordpress-smtp-mail-setting-copy' ).click( function ( e ) {
		e.preventDefault();

		var target = $( '#' + $( this ).data( 'source_id' ) ).get(0);

		target.select();

		document.execCommand( 'Copy' );
	} );

	$( '#wop-wordpress-smtp-mail-setting-smtp-auth' ).change(function() {
		$( '#wop-wordpress-smtp-mail-setting-row-smtp-user, #wop-wordpress-smtp-mail-setting-row-smtp-pass' ).toggleClass( 'inactive' );
	});

} );
