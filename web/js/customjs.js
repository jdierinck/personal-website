$(document).ready(function() {
	
	/*
	Text rotator
	*/
	$(".rotate").textrotator({
 		animation: "dissolve", // You can pick the way it animates when rotating through words. Options are dissolve (default), fade, flip, flipUp, flipCube, flipCubeUp and spin.
 		separator: ",", // If you don't want commas to be the separator, you can define a new separator (|, &, * etc.) by yourself using this field.
  		speed: 3000 // How many milliseconds until the next word show.
	});

	/*
	Scrollspy
	*/
	$('body').scrollspy({
		target: '.navbar',
		offset: 50
	});

	//* affix the navbar after scroll below intro */
	$(".navbar").affix({offset: {top: $("#intro").outerHeight(true)} });

	/*
	Add smooth scrolling on all links inside the navbar
	*/
	$(".navbar a").on('click', function(event) {

	  // Make sure this.hash has a value before overriding default behavior
	  if (this.hash !== "") {

	    // Prevent default anchor click behavior
	    event.preventDefault();

	    // Store hash
	    var hash = this.hash;

	    // Using jQuery's animate() method to add smooth page scroll
	    // The optional number (800) specifies the number of milliseconds it takes to scroll to the specified area
	    $('html, body').animate({
	      scrollTop: $(hash).offset().top
	    }, 800, function(){

	    // Add hash (#) to URL when done scrolling (default click behavior)
	      window.location.hash = hash;
	    });

	  } // End if

	});

	// Parallax function
	var parallax_animation = function() { 
		$('.parallax').each( function(i, obj) {
			var speed = $(this).attr('parallax-speed');
			if( speed ) {
				var background_pos = '-' + (window.pageYOffset / speed) + "px";
				$(this).css( 'background-position', 'center ' + background_pos );
			}
		});
	};

	$(window).scroll(function() {
		 parallax_animation();
	});


	$('.portfolio-item').on('click',function(){
		$('#pf-detail').modal();
		var description = $(this).find('.portfolio-item-description-detail').html();
		var title = $(this).find('.portfolio-item-description h4').html();
		$('#pf-detail .modal-title').html(title);
		$('#pf-detail .modal-body').html(description);

		var tags = $(this).find('.portfolio-item-description-detail').data('tags').split(' ');
		if (tags.length > 0) {
			var ptags = $('<p><i class="fa fa-tags" aria-hidden="true"></i>&nbsp;</p>');
			for (var i=0; i<tags.length; ++i){
				ptags.append('<button type="button" class="btn btn-default btn-xs">' + tags[i] + '</button>' + '&nbsp;');
			}
			$('#pf-detail .modal-body').append(ptags);
		}

	});

	// Initialize Owl carousel on modal open event
	$("#pf-detail").on('shown.bs.modal', function () {
		$(".owl-carousel").owlCarousel({
			loop:false,
		    margin:10,
		    items:1,
		    autoplay:true,
		    autoplayTimeout:4000,
		    rewind:true
		    // autowidth: true
		});	
    });

	// Destroy Owl carousel on modal close
	$("#pf-detail").on('hidden.bs.modal', function () {
		$('.owl-carousel').trigger('destroy.owl.carousel').removeClass('owl-loaded');
		$('.owl-carousel').find('.owl-stage-outer').children().unwrap();
	});

	// Google Analytics
	$("button#form_send").click(function(){
		ga('send', 'event', 'form', 'submit');
	});



});