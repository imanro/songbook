/**
 * Created by manro on 13.06.17.
 */
(function($) {

	// New way for "ready" as of jquery 3.0
	$(function () {
		$(document).trigger(SongbookUiEvents.INIT);
	});

})(jQuery);