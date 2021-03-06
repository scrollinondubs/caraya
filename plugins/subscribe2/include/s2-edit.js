/* exported s2Show, s2Hide, s2Update, s2Revert, s2CronUpdate, s2CronRevert */
// Version 1.0 - original version
// Version 1.1 - updated for Subscribe2 8.6
// Version 1.2 - updated for Subscribe2 9.0
// Version 1.3 - eslinted

// hide our span before page loads
jQuery( document ).ready(
	function() {
			jQuery( '#s2bcclimit_2' ).hide();
			jQuery( '#s2entries_2' ).hide();
			jQuery( '#s2cron_2' ).hide();
	}
);

//show span on clicking the edit link
function s2Show( id ) {
	jQuery( '#s2' + id + '_2' ).show();
	jQuery( '#s2' + id + '_1' ).hide();
	return false;
}

// hide span on clicking the hide link
function s2Hide( id ) {
	jQuery( '#s2' + id + '_1' ).show();
	jQuery( '#s2' + id + '_2' ).hide();
	return false;
}

function s2Update( id ) {
	var input = jQuery( 'input[name="' + id + '"]' ).val();
	jQuery( '#s2' + id ).html( input );
	s2Hide( id );
}

function s2Revert( id ) {
	var option = jQuery( '#js' + id ).val();
	jQuery( 'input[name="' + id + '"]' ).val( option );
	jQuery( '#s2' + id ).html( option );
	s2Hide( id );
}

function s2CronUpdate( id ) {
	var date, time;
	date = jQuery( 'input[name="' + id + 'date"]' ).val();
	jQuery( '#s2' + id + 'date' ).html( date );
	time = jQuery( 'select[name="' + id + 'time"] option:selected' ).html();
	jQuery( '#s2' + id + 'time' ).html( time );
	s2Hide( id );
}

function s2CronRevert( id ) {
	var date, time;
	date = jQuery( '#js' + id + 'date' ).val();
	jQuery( 'input[name="' + id + 'date"]' ).val( date );
	jQuery( '#s2' + id + 'date' ).html( date );
	time = jQuery( '#js' + id + 'time' ).val();
	jQuery( '[name=' + id + 'time] option' ).filter(
		function() {
				return ( this.text === time );
		}
	).prop( 'selected', true ).parent().focus();
	jQuery( '#s2' + id + 'time' ).html( time );
	s2Hide( id );
}
