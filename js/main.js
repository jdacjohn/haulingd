$(function(){
	
	$("#geocomplete").geocomplete({
		details: ".fromDetails",
		types: ['(regions)']
	});

	$("#geocomplete2").geocomplete({
		details: ".toDetails",
		types: ['(regions)']
	});
	
	$("#geocomplete2").on('keyup change focus blur', function() {
		setTimeout( function() {
			var cs = $("#countryS").val();
			var cs2 = $("#countryS2").val();
			if (cs !== "US" || cs2 !== "US") {
				document.getElementById("ship").action = "international-vehicle-shipping.php";
			} else {
				document.getElementById("ship").action=document.getElementById("selectType").options[document.getElementById("selectType").selectedIndex].value;
			}
		}, 100);
	});
	
	$('#ship').on('submit', function (event, force) {
		if (!force) {
			event.preventDefault();
			var cs3 = $("#countryS").val();
			var cs4 = $("#countryS2").val();
			if (cs3 !== "US" || cs4 !== "US") {
				document.getElementById("ship").action = "international-vehicle-shipping.php";
			} else {
				document.getElementById("ship").action=document.getElementById("selectType").options[document.getElementById("selectType").selectedIndex].value;
			}
			
			setTimeout(function () { $('#ship').trigger('submit', true); }, 800);
		}
	});
	
	$( "form" ).submit(function( event ) {
		$("#fromCity").attr('name', "fromCity");
		$("#fromZip").attr('name', "fromZip");
		$("#fromState").attr('name', "fromState");
		$("#countryS").attr('name', "fromCountry");
		$("#toCity").attr('name', "toCity");
		$("#toZip").attr('name', "toZip");
		$("#toState").attr('name', "toState");
		$("#countryS2").attr('name', "toCountry");
	});
});

function aUpdate(){
 document.getElementById("ship").action=document.getElementById("selectType").options[document.getElementById("selectType").selectedIndex].value;
}