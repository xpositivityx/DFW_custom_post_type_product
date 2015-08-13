jQuery(".destroyer").click(function() {

	var data = {
		'id': this.id,
		'action': "delete_media"
	};
	alert("hello");

	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post(ajaxurl, data, function(response) {
		// if(response != 'false'){
		// 	var finder = "[destructor='" + response + "']";
		// 	jQuery(finder).remove();
		// } else {
		// 	alert("There was an error deleting your media.");
		// }
		// alert(response);
	});
});

jQuery(document).ready(function($){
	$('.featherlight-content').append("<p> A Title </p>");
});