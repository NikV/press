/*globals gp, _, jQuery */

var $ = jQuery,
	Attachment, Attachments, l10n, media;

window.gp = window.gp || {};

/**
 * Create and return a media frame.
 *
 * Handles the default media experience.
 *
 * @param  {object} attributes The properties passed to the main media controller.
 * @return {gp.media.view.MediaFrame} A media workflow.
 */
media = gp.media = function( attributes ) {
	var MediaFrame = media.view.MediaFrame,
		frame;

	if ( ! MediaFrame ) {
		return;
	}

	attributes = _.defaults( attributes || {}, {
		frame: 'select'
	});

	if ( 'select' === attributes.frame && MediaFrame.Select ) {
		frame = new MediaFrame.Select( attributes );
	} else if ( 'post' === attributes.frame && MediaFrame.Post ) {
		frame = new MediaFrame.Post( attributes );
	} else if ( 'manage' === attributes.frame && MediaFrame.Manage ) {
		frame = new MediaFrame.Manage( attributes );
	} else if ( 'image' === attributes.frame && MediaFrame.ImageDetails ) {
		frame = new MediaFrame.ImageDetails( attributes );
	} else if ( 'audio' === attributes.frame && MediaFrame.AudioDetails ) {
		frame = new MediaFrame.AudioDetails( attributes );
	} else if ( 'video' === attributes.frame && MediaFrame.VideoDetails ) {
		frame = new MediaFrame.VideoDetails( attributes );
	} else if ( 'edit-attachments' === attributes.frame && MediaFrame.EditAttachments ) {
		frame = new MediaFrame.EditAttachments( attributes );
	}

	delete attributes.frame;

	media.frame = frame;

	return frame;
};

_.extend( media, { model: {}, view: {}, controller: {}, frames: {} });

// Link any localized strings.
l10n = media.model.l10n = window._gpMediaModelsL10n || {};

// Link any settings.
media.model.settings = l10n.settings || {};
delete l10n.settings;

Attachment = media.model.Attachment = require( './models/attachment.js' );
Attachments = media.model.Attachments = require( './models/attachments.js' );

media.model.Query = require( './models/query.js' );
media.model.PostImage = require( './models/post-image.js' );
media.model.Selection = require( './models/selection.js' );

/**
 * ========================================================================
 * UTILITIES
 * ========================================================================
 */

/**
 * A basic equality comparator for Backbone models.
 *
 * Used to order models within a collection - @see gp.media.model.Attachments.comparator().
 *
 * @param  {mixed}  a  The primary parameter to compare.
 * @param  {mixed}  b  The primary parameter to compare.
 * @param  {string} ac The fallback parameter to compare, a's cid.
 * @param  {string} bc The fallback parameter to compare, b's cid.
 * @return {number}    -1: a should come before b.
 *                      0: a and b are of the same rank.
 *                      1: b should come before a.
 */
media.compare = function( a, b, ac, bc ) {
	if ( _.isEqual( a, b ) ) {
		return ac === bc ? 0 : (ac > bc ? -1 : 1);
	} else {
		return a > b ? -1 : 1;
	}
};

_.extend( media, {
	/**
	 * media.template( id )
	 *
	 * Fetch a JavaScript template for an id, and return a templating function for it.
	 *
	 * See gp.template() in `gp-includes/js/gp-util.js`.
	 *
	 * @borrows gp.template as template
	 */
	template: gp.template,

	/**
	 * media.post( [action], [data] )
	 *
	 * Sends a POST request to Goatpress.
	 * See gp.ajax.post() in `gp-includes/js/gp-util.js`.
	 *
	 * @borrows gp.ajax.post as post
	 */
	post: gp.ajax.post,

	/**
	 * media.ajax( [action], [options] )
	 *
	 * Sends an XHR request to Goatpress.
	 * See gp.ajax.send() in `gp-includes/js/gp-util.js`.
	 *
	 * @borrows gp.ajax.send as ajax
	 */
	ajax: gp.ajax.send,

	/**
	 * Scales a set of dimensions to fit within bounding dimensions.
	 *
	 * @param {Object} dimensions
	 * @returns {Object}
	 */
	fit: function( dimensions ) {
		var width     = dimensions.width,
			height    = dimensions.height,
			maxWidth  = dimensions.maxWidth,
			maxHeight = dimensions.maxHeight,
			constraint;

		// Compare ratios between the two values to determine which
		// max to constrain by. If a max value doesn't exist, then the
		// opposite side is the constraint.
		if ( ! _.isUndefined( maxWidth ) && ! _.isUndefined( maxHeight ) ) {
			constraint = ( width / height > maxWidth / maxHeight ) ? 'width' : 'height';
		} else if ( _.isUndefined( maxHeight ) ) {
			constraint = 'width';
		} else if (  _.isUndefined( maxWidth ) && height > maxHeight ) {
			constraint = 'height';
		}

		// If the value of the constrained side is larger than the max,
		// then scale the values. Otherwise return the originals; they fit.
		if ( 'width' === constraint && width > maxWidth ) {
			return {
				width : maxWidth,
				height: Math.round( maxWidth * height / width )
			};
		} else if ( 'height' === constraint && height > maxHeight ) {
			return {
				width : Math.round( maxHeight * width / height ),
				height: maxHeight
			};
		} else {
			return {
				width : width,
				height: height
			};
		}
	},
	/**
	 * Truncates a string by injecting an ellipsis into the middle.
	 * Useful for filenames.
	 *
	 * @param {String} string
	 * @param {Number} [length=30]
	 * @param {String} [replacement=&hellip;]
	 * @returns {String} The string, unless length is greater than string.length.
	 */
	truncate: function( string, length, replacement ) {
		length = length || 30;
		replacement = replacement || '&hellip;';

		if ( string.length <= length ) {
			return string;
		}

		return string.substr( 0, length / 2 ) + replacement + string.substr( -1 * length / 2 );
	}
});

/**
 * ========================================================================
 * MODELS
 * ========================================================================
 */
/**
 * gp.media.attachment
 *
 * @static
 * @param {String} id A string used to identify a model.
 * @returns {gp.media.model.Attachment}
 */
media.attachment = function( id ) {
	return Attachment.get( id );
};

/**
 * A collection of all attachments that have been fetched from the server.
 *
 * @static
 * @member {gp.media.model.Attachments}
 */
Attachments.all = new Attachments();

/**
 * gp.media.query
 *
 * Shorthand for creating a new Attachments Query.
 *
 * @param {object} [props]
 * @returns {gp.media.model.Attachments}
 */
media.query = function( props ) {
	return new Attachments( null, {
		props: _.extend( _.defaults( props || {}, { orderby: 'date' } ), { query: true } )
	});
};

// Clean up. Prevents mobile browsers caching
$(window).on('unload', function(){
	window.gp = null;
});
