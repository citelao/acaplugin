/**
 * Add the drag and drop and sort functionality to the Tiered Template admin
 */
window.BSMTM = window.BSMTM || (function(window, document, $, undefined) {

	var app = { $ : {} };

	app.cache = function() {
		var $wrap                = $( '.many-to-many-wrap' );
		app.$.retrievedPosts     = $wrap.find( '.retrieved' );
		app.$.attachedPosts      = $wrap.find( '.attached' );

		app.from                 = $('#post_ID').val();
	};

	app.init = function() {
		app.cache();

		// Allow the user to drag items from the left list
		app.$.retrievedPosts.find( 'li' ).draggable({
			helper: 'clone',
			revert: 'invalid',
			stack: '.retrieved li',
			stop: app.replacePlusIcon,
		});

		// Allow the right list to be droppable and sortable
		app.$.attachedPosts.droppable({
			accept: '.retrieved li',
			drop: function(evt, ui) {
				app.buildItems( ui.draggable );
			}
		}).sortable({
			stop: function( evt, ui ) {
				app.resetItems( ui.item );
			}
		}).disableSelection();

		$( '.cmb2-wrap > .cmb2-metabox' )
			// Add posts when the plus icon is clicked
			.on( 'click', '.many-to-many-wrap .retrieved .add-remove', app.addPostToColumn )
			// Remove posts when the minus icon is clicked
			.on( 'click', '.many-to-many-wrap .attached .add-remove', app.removePostFromColumn )
			// Listen for search events
			.on( 'keyup', '.many-to-many-wrap input.search', app.handleSearch );

	};

	// Clone our dragged item
	app.buildItems = function( item ) {

		var $wrap  = $( item ).parents( '.many-to-many-wrap' );
		// Get the ID of the item being dragged
		var itemID = item[0].attributes[0].value;

		// If our item is in our post ID array, stop
		if ( app.inputHasId( $wrap, itemID ) ) {
			return;
		}

		// Add the 'added' class to our retrieved column when clicked
		$wrap.find( '.retrieved li[data-id="'+ itemID +'"]' ).addClass( 'added' );

		item.clone().appendTo( $wrap.find( '.attached' ) );

		app.resetAttachedListItems( $wrap );
	};

	// Add the items when the plus icon is clicked
	app.addPostToColumn = function() {

		var $li    = $( this ).parent();
		var itemID = $li.data( 'id' );
		var $wrap  = $li.parents( '.many-to-many-wrap' );

		if ( $li.hasClass( 'added' ) ) {
			return;
		}

		if ( $li.hasClass( 'taken' ) ) {
			return;
		}

		// If our item is in our post ID array, stop
		if ( app.inputHasId( $wrap, itemID ) ) {
			return;
		}

		// Add the 'added' class when clicked
		$li.addClass( 'added' );

		// Add 'taken' class if clicked on one-to-many box.
		if( $wrap.data( 'hideconnected' ) === 1 ) {
			$li.addClass( 'taken' );			
		}

		// Add the item to the right list
		$wrap.find( '.attached' ).append( $li.clone() );

		app.resetAttachedListItems( $wrap );
	};

	// Remove items from our attached list when the minus icon is clicked
	app.removePostFromColumn = function() {

		// Get the clicked item's ID
		var $li    = $(this).closest( 'li' );
		var itemID = $li.data( 'id' );
		var $wrap  = $li.parents( '.many-to-many-wrap' );

		// Remove the list item
		$(this).parent().remove();

		// Remove the 'added' class from the retrieved column
		$wrap.find('.retrieved li[data-id="' + itemID +'"]').removeClass('added');

		// Also remove the 'taken' class if present
		$wrap.find('.retrieved li[data-id="' + itemID +'"]').removeClass('taken');

		app.resetAttachedListItems( $wrap );
	};

	app.inputHasId = function( $wrap, itemID ) {
		var $input  = app.getPostIdsInput( $wrap );
		// Get array
		var postIds = app.getPostIdsVal( $input );
		// If our item is in our post ID array, stop everything
		return $.inArray( itemID, postIds) !== -1;
	};

	app.getPostIdsInput = function( $wrap ) {
		return $wrap.find('.attached-posts-ids');
	};

	app.getPostIdsVal = function( $input ) {
		var val = $input.val();
		return val ? val.split( ',' ) : [];
	};

	app.resetAttachedListItems = function( $wrap ) {
		var $input = app.getPostIdsInput( $wrap );
		var newVal = [];

		$wrap.find( '.attached li' ).each( function( index ) {
			var zebraClass = 0 === index % 2 ? 'odd' : 'even';
			newVal.push( $(this).attr( 'class', zebraClass + ' ui-sortable-handle' ).data( 'id' ) );
		});

		// Replace the plus icon with a minus icon in the attached column
		app.replacePlusIcon();

		// Update the DB
		var oldVal = $input.val().split(',').map(function(x) {
			return parseInt(x);
		});

		var relationship = $wrap.data( 'type' );
		var added = newVal.filter(function(x) { return oldVal.indexOf(x) < 0 });
		var deleted = oldVal.filter(function(x) { return newVal.indexOf(x) < 0 });

		console.log(relationship,
			oldVal,
			newVal,
			added,
			deleted);
		
		for (var i = 0; i < added.length; i++) {
			app.updateDB(added[i], 'add', relationship);
		}

		for (var i = 0; i < deleted.length; i++) {
			app.updateDB(deleted[i], 'remove', relationship);
		}

		$input.val( newVal.join( ',' ) );
	};

	app.updateDB = function(id, operation, relationship) {
		var data = {
			'action': 'bs_many_to_many',
			'_ajax_nonce': BS_MANY_TO_MANY_L10N.nonce,
			'operation': operation,
			'type': relationship,
			'from': app.from,
			'to': id
		};
		$.post(ajaxurl, data)
			.done(function(response) {
				// alert('Got this from the server: ' + response);
			})
			.fail(function( e ) {
				alert('Failed');
			});
	};

	// Re-order items when items are dragged
	app.resetItems = function( item ) {
		var $li = $( item );
		app.resetAttachedListItems( $li.parents( '.many-to-many-wrap' ) );
	};

	// Replace the plus icon in the attached posts column
	app.replacePlusIcon = function() {
		$( '.attached li .dashicons.dashicons-plus' ).removeClass( 'dashicons-plus' ).addClass( 'dashicons-minus' );
	};

	// Handle searching available list
	app.handleSearch = function( evt ) {

		var $this = $( evt.target );
		var searchQuery = $this.val() ? $this.val().toLowerCase() : '';

		$this.closest( '.column-wrap' ).find( 'ul.connected li' ).each( function() {
			var $el = $(this);

			if ( $el.text().toLowerCase().search( searchQuery ) > -1 ) {
				$el.show();
			} else {
				$el.hide();
			}
		} );

	};

	jQuery(document).ready( app.init );

	return app;

})(window, document, jQuery);
