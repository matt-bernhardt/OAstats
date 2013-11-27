$(function() {
	var bucket;
	var newdiv;
	var ls;
	var theB, theT, theE;
	// Hide pre-built select/option control, so we can replace it with something better
	//$("#filter").attr("class","semantic");

	// Build lb container for all possible elements
	newdiv = document.createElement("div");
	$(newdiv).attr("class","lb");
	$("#filter").after(newdiv);

	// Build ls container for selected elements
	ls = document.createElement("div");
	$(ls).addClass("ls");
	$(".lb").after(ls);

	// Each
	$("#filter option").each(function(i,e) {
		bucket = document.createElement("div");
		$(bucket).append(e.value);
		$(bucket).addClass("b"+i).addClass("bucket");
		$(bucket).attr("data-attribute","b"+i);
		$(newdiv).append(bucket);
	});

	$(".lb .bucket").click(function() {
		theB = $(this).attr("data-attribute");
		theT = $(this).text();
		// alert('click '+theB);
		// hide clicked element
		$(this).addClass("semantic");
		// add element to selected bin
		theE = document.createElement("div");
		$(theE).addClass(theB).addClass("bucket");
		$(theE).text(theT);
		$(".ls").append(theE);
	});

	// Make sure that we made it through
	console.log('hi');

});