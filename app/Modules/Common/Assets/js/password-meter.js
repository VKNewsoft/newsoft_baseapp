jQuery(document).ready(function () {
	"use strict";
	var options = {};
	options.ui = {
		container: "#pwd-container",
		viewports: {
			progress: ".pwstrength_viewport_progress"
		},
		showVerdictsInsideProgressBar: true
	};
	options.common = {
		debug: true,
		onLoad: function () {
			$('#messages').text('Start typing password');
		}
	};
	$(':password').pwstrength(options);
});