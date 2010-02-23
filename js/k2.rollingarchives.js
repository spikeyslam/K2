/**
 * Inserts the Rolling Archives user interface.
 *
 * @param {string}	content		An element to place the UI before. Eg. '#content';
 * @param {string}	posts		Class of the elements containing individual posts.
 * @param {string}	parent		ID of parent element of RA UI, for .smartposition.
 * @param {string}	pagetext  	A localized string of 'X of Y' inserted into the UI.
 * @param {string}	older  		A localized string of 'Older' inserted into the UI.
 * @param {string}	newer  		A localized string of 'Newer' inserted into the UI.
 * @param {string}	loading		A localized string of 'Loading' inserted into the UI. 
 * @param {Int}		offset		Value in pixels to offset scrolls to an element with. Defaults to 0.
 */
function RollingArchives(args) {
	RA						= this;

	RA.content				= args.content;
	RA.posts				= args.posts;
	RA.parent				= args.parent;
	RA.offset				= args.offset || 0;

	// Localization strings for the UI.
	RA.pageText				= args.pagetext;
	var older				= args.older || 'Older';
	var newer				= args.newer || 'Newer';
	var loading				= args.loading || 'Loading';

	// Initially loaded values.
	RA.initPageNumber		= args.pagenumber;
	RA.initPageCount		= args.pagecount;
	RA.initQuery			= args.query;
	RA.initPageDates		= args.pagedates;

	RA.active				= false;

	// Insert the Rolling Archives UI
	jQuery(RA.content).before('\
		<div id="rollingarchivesbg"></div>\
		<div id="rollingarchives">\
			<div id="rollnavigation">\
				<div id="pagetrackwrap"><div id="pagetrack"><div id="pagehandle"><div id="rollhover"><div id="rolldates"></div></div></div></div></div>\
				\
				<div id="rollpages"></div>\
				\
				<a id="rollprevious" title="' + older + '" href="#"><span>&laquo;</span> '+ older +'</a>\
				<div id="rollload" title="'+ loading +'"><span>'+ loading +'</span></div>\
				<a id="rollnext" title="'+ newer +'" href="#">'+ newer +' <span>&raquo;</span></a>\
				\
				<div id="texttrimmer">\
					<div id="trimmertrim"><span>&raquo;&nbsp;&laquo;</span></div>\
					<div id="trimmeruntrim"><span>&laquo;&nbsp;&raquo;</span></div>\
				</div>\
			</div> <!-- #rollnavigation -->\
		</div> <!-- #rollingarchives -->\
	')

	RA.setState();
};							

/**
 * Initializes the Rolling Archives system at load or after a new page has been fetched by RA.
 *
 * @param {int} 	pagenumber	The page to get.
 * @param {int}		pagecount	The total number of pages.
 * @param {array}	query		The query to fetch from WordPress
 * @param {array}	pagedates	An array of 'month, year' to show as you scrub the RA slider.
 */
RollingArchives.prototype.setState = function(pagenumber, pagecount, query, pagedates) {
	RA.pageNumber			= pagenumber	|| RA.initPageNumber;
	RA.pageCount 			= pagecount		|| RA.initPageCount;
	RA.query 				= query			|| RA.initQuery;
	RA.pageDates 			= pagedates		|| RA.initPageDates;

/* 	console.log( RA.pageNumber, RA.pageCount, RA.query, RA.pageDates ); */

	// Save the original content for later retrieval
	if (!query && RA.pageNumber === 1) RA.saveState(); 

	// First time RA is called? Let's get to work.
	if ( !jQuery('body').hasClass('rollingarchives') ) {
		// Add click events
		jQuery('#rollnext').click(function() {
			RA.pageSlider.setValueBy(1);
			return false;
		});

		jQuery('#rollprevious').click(function() {
			RA.pageSlider.setValueBy(-1);
			return false;
		});

		jQuery('#trimmertrim').click(function() {
			jQuery('body').addClass('trim');
		})
	
		jQuery('#trimmeruntrim').click(function() {
			jQuery('body').removeClass('trim');
		})

		RA.smartPosition(RA.parent); // Prepare a 'sticky' scroll point

		RA.assignHotkeys(); // Setup Keyboard Shortcuts
	
		jQuery('body').addClass('rollingarchives') // Put the world on notice.

		jQuery(window).bind( 'hashchange', K2.parseFragments ) // Looks for fragment changes
	}

	if ( RA.validatePage(RA.pageNumber) ) {
		jQuery('body').removeClass('hiderollingarchives').addClass('showrollingarchives')

		jQuery('#rollingarchives').show();

		jQuery('#rollload').hide();
		jQuery('#rollhover').hide();

		// Setup the page slider
		RA.pageSlider = new K2Slider('#pagehandle', '#pagetrackwrap', {
			minimum:	1,
			maximum:	RA.pageCount,
			value:		RA.pageCount - RA.pageNumber + 1,
			onSlide:	function(value) {
							jQuery('#rollhover').show();
							RA.updatePageText( RA.pageCount - value + 1);
						},
			onChange:	function(value) {
							RA.updatePageText( RA.pageCount - value + 1);
							RA.gotoPage( RA.pageCount - value + 1 );
						}
		})

		RA.updatePageText( RA.pageNumber )

		RA.active = true;
	} else {
		jQuery('body').removeClass('showrollingarchives').addClass('hiderollingarchives');
	}
};

/**
 * Save the current set of data for later retrieval using .restoreState.
 */
RollingArchives.prototype.saveState = function() {
	// RA.prevQuery = RA.query;
	RA.originalContent = jQuery(RA.content).html();
};

/**
 * Restore the data saved using .saveState.
 */
RollingArchives.prototype.restoreState = function() {
	if (RA.originalContent != '') {
		jQuery('body').removeClass('livesearchactive').addClass('livesearchinactive'); // Used to show/hide elements w. CSS.

		jQuery(RA.content).html(RA.originalContent)

		jQuery.bbq.removeState('page');
		jQuery.bbq.removeState('search');

		RA.setState();
	}
};

/**
 * Updates the x part of the 'x of y' page counter.
 *
 * @param {int}	page	The page to update to.
 */
RollingArchives.prototype.updatePageText = function(page) {
	jQuery('#rollpages').html(page +' '+ RA.pageText +' '+ RA.pageCount)
	jQuery('#rolldates').html(RA.pageDates[page - 1])
};


/**
 * Validates a given page number, modifies the classes on 'body' and returns the pagenumber (or 0 if it's outside the available range).
 *
 * @param 	{Int}	newpage A requested page number. 
 * @return	{Int}			A validated page number, or 0 if the number given is outside the legal range.
 */
RollingArchives.prototype.validatePage = function(newpage) {
	if (!isNaN(newpage) && RA.pageCount > 1) {

		if (newpage >= RA.pageCount) {
			jQuery('body').removeClass('onepageonly firstpage nthpage').addClass('lastpage');
			return RA.pageCount;

		} else if (newpage <= 1) {
			jQuery('body').removeClass('onepageonly nthpage lastpage').addClass('firstpage');
			return 1;

		} else {
			jQuery('body').removeClass('onepageonly firstpage lastpage').addClass('nthpage');
			return newpage;
		}
	}

	jQuery('body').removeClass('firstpage nthpage lastpage').addClass('onepageonly');

	return 0;
};


/**
 * Adds removes the 'rollload' class to or from 'body'.
 *
 * @param {String} gostop If set to 'start', adds the 'rollload' class, otherwise removes it.
 */
RollingArchives.prototype.loading = function(gostop) {
	if (gostop == 'start')
		jQuery('body').addClass('rollload')
	else
		jQuery('body').removeClass('rollload')
};


/**
 * Makes Rolling Archives go to the page requested.
 *
 * @param {Int} newpage The page to go to.
 */
RollingArchives.prototype.gotoPage = function(newpage) {
	var page = RA.validatePage(newpage);

	// Detect if the user was using hotkeys.
	var selected = jQuery('.selected').length > 0;
	
	// New valid page?
	if ( page != RA.pageNumber && page != 0) {
		RA.lastPage = RA.pageNumber;
		RA.pageNumber = page;

		// Update the hash/fragment
		if (page === 1)		
			jQuery.bbq.removeState('page');
		else
			jQuery.bbq.pushState( 'page='+page )

		// Show the loading spinner
		RA.loading('start')

		// Do fancy animation stuff
		if (K2.Animations) {
			RA.flashElement(page > RA.lastPage ? '#rollprevious' : '#rollnext')
			jQuery(RA.parent).height(jQuery(RA.parent).height()) // Don't skip in height
			jQuery(RA.content).hide("slide", { direction: (page > RA.lastPage ? 'right' : 'left'), easing: 'easeInExpo'}, 200)
		}

		// ...and scroll to the top if needed
		if (K2.Animations && (RA.pageNumber != 1) && jQuery('body').hasClass('smartposition'))
			jQuery('html,body').animate({ scrollTop: jQuery(RA.parent).offset().top }, 100)

		jQuery.extend(RA.query, { paged: RA.pageNumber, k2dynamic: 1 })

		K2.ajaxGet(RA.query,
			function(data) {
				jQuery('#rollhover').fadeOut('slow')
				RA.loading('stop')

				// Insert the content and show it.
				jQuery(RA.content).html(data)

				if (K2.Animations)
					jQuery(RA.content).show("slide", { direction: (page > RA.lastPage ? 'left' : 'right'), easing: 'easeOutExpo' }, 450, jQuery(RA.parent).height('inherit'))

				if (selected == true)
					RA.scrollTo(RA.posts, 1, (page > RA.lastPage ? -1 : jQuery(RA.posts).length -2 )) // If the hotkeys were used, select the first post
			}
		)
	}

	if (page == 1) // Reset trimmer setting
		jQuery('body').removeClass('trim')
};


/**
 * When a given element scrolls off the top of the screen, add a given classname to 'body'. 
 *
 * @param {String} obj			The element to watch.
 * @param {String} edge			Can be set to 'bottom', in which case it checks to see if it's
 * 								scrolled off the bottom. Otherwise it always checks the top.
 */
RollingArchives.prototype.smartPosition = function(obj, edge) {
	var classname	= 'smartposition';
	var objTop		= jQuery(obj).offset().top;

	if ( jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 7 ) return; // No IE6 or lower

	if (edge != 'bottom') { // Check Obj pos vs top edge by default
		RA.checkTop(objTop, classname); // Check on load
		
		jQuery(window)
			.scroll(function() { RA.checkTop(objTop, classname); });
	} else {  // Check Obj pos vs bottom edge
		RA.checkBottom(objTop, classname);  // Check on load

		jQuery(window)
			.scroll(function() { RA.checkBottom(obj, classname); })
			.resize(function() { RA.checkBottom(obj, classname); })
			.onload(function() { RA.checkBottom(obj, classname); })
	}
};


/**
 * Check if an element disappears underneath the fold
 */
RollingArchives.prototype.checkBottom = function(obj, classname) {
	if ( (document.documentElement.scrollTop + document.documentElement.clientHeight || document.body.scrollTop + document.documentElement.clientHeight) >= jQuery(obj).offset().top && jQuery('body').hasClass('showrollingarchives')) {
		jQuery('body').addClass(classname);
	} else {
		jQuery('body').removeClass(classname);
	}
}

/**
 * Check if an element disappears above the window
 */
RollingArchives.prototype.checkTop = function(objTop, classname) {
	if ( jQuery(document).scrollTop() >= objTop )
		jQuery('body').addClass(classname);
	else
		jQuery('body').removeClass(classname);
};

/*
 * Scroll to next/previous of given elements. 
 *
 * @param	{String}	elements	The element(s) to go to. Is fed directly to jQuery.
 * @param 	{Int}		offset		An offset in pixels added to the top, to scroll to.
 * @param 	{Int}		direction	1 to go to next, -1 to go to previous.
 * @type	{DOM Object}
 */
RollingArchives.prototype.scrollTo = function(elements, direction, next) {
	// Turn off our scroll detection.
	jQuery(window).unbind('scroll.scrolldetector')
	jQuery('html, body').stop()

	// Someone telling us where to go?
	RA.nextObj = (next != undefined ? next : RA.nextObj);

	// Find the next element below the upper fold
	if (RA.nextObj == undefined) {
		jQuery(elements).each(function(idx) {
			if ( jQuery(this).offset().top - RA.offset > jQuery(window).scrollTop() ) {
				RA.nextObj = (direction === 1 ? idx -1 : idx);
				return false;
			}
		})
	}

	// direction: -1 on the first page? Can't do bub.
	if (direction === -1 && RA.pageNumber === 1 && RA.nextObj === 0) return;

	// Now, who's next?
	RA.nextObj = RA.nextObj + direction;

	// Next element is outside the range of objects? Then let's change the page.
	if ( ( RA.nextObj > jQuery(elements).length - 1 ) || RA.nextObj < 0 ) {
		RA.nextObj = undefined;
		RA.pageSlider.setValueBy(-direction);
		RA.flashElement(direction === 1 ? '#rollprevious' : '#rollnext');
	}

	// And finally scroll to the element (if the last element in the selection isn't on screen in its entirety).
/* 	if ( jQuery(obj+':first').offset().top + jQuery(obj+':last').offset().top + jQuery(obj+':last').height() > jQuery(window).scrollTop() + jQuery(window).height() ) */

	// Move .selected class to new element, return its vertical position to variable
	nextElementPos = jQuery(elements).removeClass('selected').eq(RA.nextObj).addClass('selected').offset().top - RA.offset;

	// Scroll to the next element. Then detect if user manually scrolls away, in which case we clear our .selected stuff.
	var theBrowserWindow	= (jQuery.browser.safari) ? jQuery('body') : jQuery('html'); // Browser differences, hurray.
	theBrowserWindow.animate({ scrollTop: nextElementPos }, (K2.Animations ? 150 : 0), 'easeOutExpo', function() { jQuery(window).bind('scroll.scrolldetector', function() { RA.scrollDetection(nextElementPos) }) } );
};


/*
 * 'Flashes' and element by doubling its fontsize a microsecond.
 */
RollingArchives.prototype.flashElement = function(el) {
	if (jQuery(el+':animated').length > 0 || !K2.Animations) return; // Prevent errors

	var origSize = parseInt(jQuery(el).css('font-size'));
	jQuery(el).animate({fontSize: origSize * 2}, 30, 'easeInQuad', function() {
		jQuery(el).animate({fontSize: origSize}, 150, 'easeOutQuad')
	})
}


/*
 * Detect whether the user scrolls more than 40px away from the .selected element and then clears .selected stuff.
 *
 * @Param {Int}	scrollPos	The position, in pixels from the top, of the element to scroll to. 
 */
RollingArchives.prototype.scrollDetection = function(scrollPos) {
	// If we're at the bottom already, bail.
	if  (jQuery(document).scrollTop() + jQuery(window).height() >= jQuery(document).height()) return; 

	// "We went too far. He said we went too far..."
	var tolerance = 40;
	if ( jQuery(document).scrollTop() > scrollPos + tolerance || jQuery(document).scrollTop() < scrollPos - tolerance ) {
		jQuery(window).unbind('scroll.scrolldetector');
		jQuery('*').removeClass('selected')
		RA.nextObj = undefined;
	}
};


/*
 * Binds keyboard shortcuts for scrolling back and forth between posts and pages.
 */
RollingArchives.prototype.assignHotkeys = function() {
	// J: Scroll to next post
	jQuery(document).bind('keydown.hotkeys', 'J', function() { RA.scrollTo(RA.posts, 1) });

	// K: Scroll to previous post
	jQuery(document).bind('keydown.hotkeys', 'K', function() { RA.scrollTo(RA.posts, -1) });

	// Enter: Go to selected post
	jQuery(document).bind('keydown.hotkeys', 'Return', function() { if (jQuery('.selected').length > 0) window.location = jQuery('.selected .post-title a').attr('href') });

	// Esc: Deactivate selected post
	jQuery(document).bind('keydown.hotkeys', 'Esc', function() { jQuery(window).unbind('scroll.scrolldetector'); jQuery('*').removeClass('selected'); RA.nextObj = undefined });

	// H: Go back to page 1  
	jQuery(document).bind('keydown.hotkeys', 'H', function() { RA.gotoPage(1) })

	// T: Trim, or remove .post-content  
	jQuery(document).bind('keydown.hotkeys', 'T', function() { 
		
		if ( !jQuery('body').hasClass('trim') )
			jQuery('body').addClass('trim')
		else
			jQuery('body').removeClass('trim')

		RA.flashElement('#texttrimmer');
	});

	// Left Arrow: Previous Page
	jQuery(document).bind('keydown.hotkeys', 'Left', function() { RA.pageSlider.setValueBy(-1) });

	// Right Arrow: Next Page
	jQuery(document).bind('keydown.hotkeys', 'Right', function() { RA.pageSlider.setValueBy(1) });
}