$(document).ready(function() {
	$("input[name='vehicle_make']").autocomplete({
	  source: function( request, response ) {
		$.ajax({
		  url: "ajax_autocomplet.php",
		  dataType: "json",
		  data: {
			query: request.term,
			page: 'make'
		  },
		  success: function( data ) {
			response( data );
		  }
		});
	  }
	});

	$("input[name='vehicle_model']").autocomplete({
	  source: function( request, response ) {
		$.ajax({
		  url: "ajax_autocomplet.php",
		  dataType: "json",
		  data: {
			year: $("input[name='vehicle_year']").val(),
			make: $("input[name='vehicle_make']").val(),
			query: request.term,
			page: 'model'
		  },
		  success: function( data ) {
			response( data );
		  }
		});
	  }
	});
});