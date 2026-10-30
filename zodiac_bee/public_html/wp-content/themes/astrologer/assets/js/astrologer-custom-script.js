/*--------------------- Copyright (c) 2021 -----------------------
[Master Javascript]
Project: Astrology
Version: 1.0.0
Assigned to: Theme Forest
-------------------------------------------------------------------*/
(function($) {
    "use strict";
    /*-----------------------------------------------------
    	Function  Start
    -----------------------------------------------------*/
    var Astrology = {
        initialised: false,
        version: 1.0,
        collapseScreenSize: 1199,
        init: function() {
            if (!this.initialised) {
                this.initialised = true;
            } else {
                return;
            }
            /*-----------------------------------------------------
            	Function Calling
			-----------------------------------------------------*/
            this.loader();
            this.headerCart();
            this.searchBox();
            this.fixClasses();
            this.topButton();
            this.focusText();
            this.toggleMenu();
            this.popupVideo();
            this.relaterProduct();
            this.Product();
            this.niceSelectType();
            this.singleSlideSlider();
            this.doubleSlideSlider();
            this.astroCounter();
            this.testimonial_slider();	
            this.sticky_header();		
            
        },
        /*-----------------------------------------------------
            	Function Start
		-----------------------------------------------------*/
        /*-----------------------------------------------------
            	Fix Loader JS
		-----------------------------------------------------*/
        loader: function() {
            jQuery(window).on('load', function() {
                $(".preloader").fadeOut();
                $(".preloader-inner").delay(500).fadeOut("slow");
            });
        },
	    
        /*-----------------------------------------------------
            	Fix Header Cart JS
		-----------------------------------------------------*/
        headerCart: function() {
            $(".al-cart").on('click', function() {
                $(".al-cart-box").toggleClass('open-cart');
            });
            $("body").on('click', function() {
                $(".al-cart-box").removeClass('open-cart');
            });
            $('.al-cart-box, .al-cart').click(function(event) {
                event.stopPropagation();
            });
        },

        /*-----------------------------------------------------
            	Fix Classes JS
		-----------------------------------------------------*/
        fixClasses: function() {
            $('.wc-forward, .wp-block-button__link, .woocommerce div.product form.cart .button, .woocommerce-Button.button, .yith-wcaf.yith-wcaf-settings.woocommerce input[type="submit"], .yith-wcaf.yith-wcaf-link-generator input[type="submit"], a.more-link').addClass('al-btn');
            $('.wp-block-search__button, .search-form .search-submit, .form-submit .submit, form.post-password-form input[type="submit"], .widget.woocommerce.widget_price_filter button.button, .woocommerce-product-search button').addClass('al-btn');
        },

        /*-----------------------------------------------------
        	Fix GoToTopButton
        -----------------------------------------------------*/
        topButton: function() {
            var scrollTop = $("#scroll");
            $(window).on('scroll', function() {
                if ($(this).scrollTop() < 500) {
                    scrollTop.removeClass("active");
                } else {
                    scrollTop.addClass("active");
                }
            });
            $('#scroll').click(function() {
                $("html, body").animate({
                    scrollTop: 0
                }, 2000);
                return false;
            });
        },

        /*-----------------------------------------------------
            	Fix Mobille Menu JS
		-----------------------------------------------------*/
        toggleMenu: function() {

            /* Header Style One */
            $(".al-menu-toggle").on('click', function() {
                $(".al-main-navigation, .al-menu-toggle").toggleClass('open-menu');
            });
            $("body").on('click', function() {
                $(".al-main-navigation, .al-menu-toggle").removeClass('open-menu');
            });

            $(".al-main-navigation, .al-menu-toggle, .menu-item-has-children > a").on('click', function(e) {
                e.stopPropagation();
            });

            if ($(window).width() <= Astrology.collapseScreenSize) {

                $(".al-main-navigation ul > li:has(ul)").addClass('has_sub_menu');
                $(".al-main-navigation > div > ul > li:has(ul) > a").on('click', function(e) {
                    e.preventDefault();

                    $('.al-main-navigation > div > ul > li:has(ul) > a').not($(this)).closest('li').find('.sub-menu').slideUp();
                    $('.al-main-navigation > div > ul > li:has(ul) > a').not($(this)).closest('li').removeClass('open');

                    $(this).parent('.al-main-navigation > div > ul li').children('ul.sub-menu').slideToggle();
                    $(this).parent('.al-main-navigation > div > ul li').toggleClass("open");
                });

                $(".al-main-navigation ul.sub-menu > li:has(ul) > a").on('click', function(e) {
                    e.preventDefault();

                    $('.al-main-navigation ul.sub-menu > li:has(ul) > a').not($(this)).closest('li').find('.sub-menu').slideUp();
                    $('.al-main-navigation ul.sub-menu > li:has(ul) > a').not($(this)).closest('li').removeClass('open');

                    $(this).parent('.al-main-navigation ul.sub-menu li').children('ul.sub-menu').slideToggle();
                    $(this).parent('.al-main-navigation ul.sub-menu li').toggleClass("open");
                });
            }
        },
        /*-----------------------------------------------------
        	Fix Search Bar
        -----------------------------------------------------*/
        searchBox: function() {
            $('.al-search-btn').on("click", function() {
                $('.al-search-form').addClass('show-search');
            });
            $('.close-search').on("click", function() {
                $('.al-search-form').removeClass('show-search');
            });
            $('.al-search-form').on("click", function() {
                $('.al-search-form').removeClass('show-search');
            });
            $(".al-search-form-inner").on('click', function(e) {
                e.stopPropagation();
            });
        },


        /*-----------------------------------------------------
        	Fix  On focus Placeholder
        -----------------------------------------------------*/
        focusText: function() {
            var place = '';
            $('input,textarea').focus(function() {
                place = $(this).attr('placeholder');
                $(this).attr('placeholder', '');
            }).blur(function() {
                $(this).attr('placeholder', place);
            });
        },

        /*-----------------------------------------------------
        	Fix Select Field
        -----------------------------------------------------*/
        niceSelectType: function() {
            if ($('.select-type, select:not(#rating, .woocommerce-input-wrapper select)').length > 0) {
                $('.select-type, select:not(#rating, .woocommerce-input-wrapper select)').niceSelect();
            }
        },

        /*-----------------------------------------------------
				Fix Video Popup
		----------------------------------------------------*/
        popupVideo: function() {
            if ($(".popup-youtube").length > 0) {
                $(".popup-youtube").magnificPopup({
                    type: "iframe",
                });
            }
        },

        /*-----------------------------------------------------
            Fix Counter
        -----------------------------------------------------*/
        astroCounter: function() {
            if ($('.counter-holder').length > 0) {
                var a = 0;
                $(window).scroll(function() {
                    var topScroll = $('.counter-holder').offset().top - window.innerHeight;
                    if (a == 0 && $(window).scrollTop() > topScroll) {
                        $('.count-no').each(function() {
                            var $this = $(this),
                                countTo = $this.attr('data-count');
                            $({
                                countNum: $this.text()
                            }).animate({
                                countNum: countTo
                            }, {
                                duration: 5000,
                                easing: 'swing',
                                step: function() {
                                    $this.text(Math.floor(this.countNum));
                                },
                                complete: function() {
                                    $this.text(this.countNum);
                                }
                            });
                        });
                        a = 1;
                    }
                });
            };
        },


        /*-----------------------------------------------------
			Fix Related Product Slider
		----------------------------------------------------*/
        relaterProduct: function() {
            var productCount = $('.al-related-product-swiper').attr('data-product');
            if ($('.al-related-product-swiper').length > 0) {
                var swiper = new Swiper('.al-related-product-swiper', {
                    slidesPerView: 4,
                    ween: 30,
                    autoplay: true,
                    speed: 1500,
                    pagination: {
                        el: '.al-related-product-swiper .swiper-pagination',
                        clickable: true,
                    },
                    loop: false,
                    breakpoints: {
                        0: {
                            slidesPerView: 1,
                            spaceBetween: 30
                        },
                        480: {
                            slidesPerView: 1,
                            spaceBetween: 30
                        },
                        767: {
                            slidesPerView: 2,
                            spaceBetween: 30
                        },
                        991: {
                            slidesPerView: 2,
                            spaceBetween: 30
                        },
                        1200: {
                            slidesPerView: productCount,
                            spaceBetween: 30
                        },
                        navigation: {
                            nextEl: '.al-related-product-swiper .al-nav-next',
                            prevEl: '.al-related-product-swiper .al-nav-prev',
                        },
                    }

                });

            }
            
            $( ".is-style-outline" ).prev().addClass("is-style-rounded");
            $( ".al_product_filters select,.woocommerce div.product form.cart .variations select" ).addClass("select-type");
        },

        singleSlideSlider: function() {
            if ($('.al-about-wrap').length > 0) {
                var swiper = new Swiper('.single-slider', {
                    mousewheel: true,
                    slidesPerView: 1,
                    spaceBetween: 15,
                    loop: false,
                    autoplay: false,
                    speed: 500,
                    pagination: {
                        el: '.al-about-wrap .swiper-pagination',
                        clickable: true,
                    },
                    effect: 'fade',
                    loop: true,
                });
            }

            if ($('.al-overview-slider').length > 0) {
                var swiper = new Swiper('.overview-slider', {
                    mousewheel: true,
                    slidesPerView: 1,
                    spaceBetween: 30,
                    loop: false,
                    autoplay: false,
                    speed: 500,
                    pagination: {
                        el: '.al-overview-slider .swiper-pagination',
                        clickable: true,
                    },
                    loop: true,
                    navigation: {
                        nextEl: '.al-overview-slider .al-nav-next',
                        prevEl: '.al-overview-slider .al-nav-prev',
                    },
                });
            }
        },

        doubleSlideSlider: function() {
            if ($('.al-testimonial-slider').length > 0) {
                var swiper = new Swiper('.double-slider', {
                    mousewheel: true,
                    slidesPerView: 2,
                    spaceBetween: 15,
                    loop: false,
                    autoplay: false,
                    speed: 500,
                    pagination: {
                        el: '.al-testimonial-slider .swiper-pagination',
                        clickable: true,
                    },
                    loop: true,
                    breakpoints: {
                        0: {
                            slidesPerView: 1,
                        },
                        480: {
                            slidesPerView: 1,
                        },
                        767: {
                            slidesPerView: 1,
                        },
                        991: {
                            slidesPerView: 2,
                        },
                        1200: {
                            slidesPerView: 2,
                        }
                    },
                    navigation: {
                        nextEl: '.al-testimonial-slider .al-nav-next',
                        prevEl: '.al-testimonial-slider .al-nav-prev',
                    },
                });
            }
        },

        /**
        * latest slider
        */				
		testimonial_slider: function () {
			var swiper = new Swiper('.al-testimonial-wrapper .swiper-container', {
				slidesPerView: 4,
				spaceBetween: 30,
				loop: true,
				speed: 1000,
				autoplay: {
					delay: 2500,
					disableOnInteraction: false,					
				},
				navigation: {
					nextEl: '.swiper-button-next',
					prevEl: '.swiper-button-prev',					
					clickable: true,
				},

				breakpoints: {
					1199: {
						slidesPerView: 4,
						spaceBetween: 30,
					},
					992: {
						slidesPerView: 3,
						spaceBetween: 30,
					},
					768: {
						slidesPerView: 2,
						spaceBetween: 30,
					},
					575: {
						slidesPerView: 1,
						spaceBetween: 15,
					},
					320: {
						slidesPerView: 1,
						spaceBetween: 15,
					}
					,
					0: {
						slidesPerView: 1,
						spaceBetween: 15,
					}
				}
			});
		},
		// testimonial slider
        Product: function() {
            if ($('.product-slider').length > 0) {
                var swiper = new Swiper('.product-slider', {
                    slidesPerView: 4,
                    spaceBetween: 30,
                    loop: true,
                    autoplay: true,
                    speed: 1500,
                    breakpoints: {
                        0: {
                            slidesPerView: 1,
                        },
                        480: {
                            slidesPerView: 1,
                        },
                        767: {
                            slidesPerView: 2,
                        },
                        991: {
                            slidesPerView: 2,
                        },
                        1200: {
                            slidesPerView: 4,
                        }
                    },
                    navigation: {
                        nextEl: '.al-product-slider-wrapper .al-nav-next',
                        prevEl: '.al-product-slider-wrapper .al-nav-prev',
                    },
                    pagination: {
                        el: '.al-product-slider-wrapper .swiper-pagination',
                        clickable: true,
                    },
                });
            }
        },
        /* Sticky Header*/    
        
       
        
        sticky_header: function() {
            if(frontadminajax.switch_header == 'true'){
              if ($('input.checkbox_check').attr(':checked')); {
                var header = $(".al-wrapper-sticky");
                var w = window.innerWidth;
                if (w >= 1199) {
                    $(window).scroll(function() {
                        var scroll = $(window).scrollTop();
                        if (scroll >= 300 && $(this).width() > 1199) {
                            header.addClass("al-sticky-header animated fadeInDown");
                        } else {
                            header.removeClass('al-sticky-header animated fadeInDown');
                        }
                    });
                }
                if (w <= 1199) {
                    $(window).scroll(function() {
                        var scroll = $(window).scrollTop();
                        if (scroll >= 400 && $(this).width() < 1199) {
                            header.addClass("al-sticky-header");
                        } else {
                            header.removeClass('al-sticky-header');
                        }
                    });
                }
            }  
            }
            
            
            },
    };

    Astrology.init();

    /**
     * Cart Quantity Count
     */
    if (!String.prototype.getDecimals) {
        String.prototype.getDecimals = function() {
            var num = this,
                match = ('' + num).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
            if (!match) {
                return 0;
            }
            return Math.max(0, (match[1] ? match[1].length : 0) - (match[2] ? +match[2] : 0));
        }
    }
    // Quantity "plus" and "minus" buttons
    $(document.body).on('click', '.plus, .minus', function() {
        var $qty = $(this).closest('.quantity').find('.qty'),
            currentVal = parseFloat($qty.val()),
            max = parseFloat($qty.attr('max')),
            min = parseFloat($qty.attr('min')),
            step = $qty.attr('step');

        // Format values
        if (!currentVal || currentVal === '' || currentVal === 'NaN') currentVal = 0;
        if (max === '' || max === 'NaN') max = '';
        if (min === '' || min === 'NaN') min = 0;
        if (step === 'any' || step === '' || step === undefined || parseFloat(step) === 'NaN') step = 1;

        // Change the value
        if ($(this).is('.plus')) {
            if (max && (currentVal >= max)) {
                $qty.val(max);
            } else {
                $qty.val((currentVal + parseFloat(step)).toFixed(step.getDecimals()));
            }
        } else {
            if (min && (currentVal <= min)) {
                $qty.val(min);
            } else if (currentVal > 0) {
                $qty.val((currentVal - parseFloat(step)).toFixed(step.getDecimals()));
            }
        }

        // Trigger change event
        $qty.trigger('change');
    });

   $('.add_to_cart').on('click', function() {

        var product_id = $(this).attr('data-productid');
        var formdata = 'product_id=' + product_id;
        formdata += '&action=Astrologyn_woocommerce_ajax_add_to_cart';
        $.ajax({
            type: 'post',
            url: frontadminajax.ajax_url,
            data: formdata,
            success: function(response) {

                toastr.success('Product has been added to your cart');
                location.reload();
            }

        });

        $('.add_to_wishlist').on('click', function() {
            toastr.success('Product has been added to your wishlist');
        });

    });
	
// Testimonial slider 1
	 $('.as-customer-for').slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      infinite:true,
      speed:800,
      dots:false,
      arrows: false,
      fade: true,
      asNavFor: '.as-customer-nav'
  });
  $('.as-customer-nav').slick({
      slidesToShow: 6,
      arrows: false,
      slidesToScroll: 1,
      asNavFor: '.as-customer-for',
      dots: false,
      centerMode: true,
      focusOnSelect: true
    });
    // Testimonial Slider style 2 
    $('.ast-testimonials-slider .owl-carousel').owlCarousel({
    loop:true,
    margin:10,
    nav:true,
    responsive:{
        0:{
            items:1
        },
        600:{
            items:1
        },
        1000:{
            items:1
        }
    }
    });
    // Magnific Popup
    $(document).ready(function() {
      $('.youtube-popup').magnificPopup({
        type: 'iframe'
      });
    });
    
    var $backToTop = $("#scroll");
    $backToTop.hide();
    
    $(window).on('scroll', function() {
      if ($(this).scrollTop() > 100) {
        $backToTop.fadeIn();
      } else {
        $backToTop.fadeOut();
      }
    });

    $backToTop.on('click', function(e) {
      e.preventDefault(); // Prevent default anchor click behavior
      $("html, body").animate({ scrollTop: 0 }, 500);
    });

// Tarot Cards load More
loadmore_palm(2);
	function loadmore_palm(target){
		var cnt = 0;
		var chk = 0;
	    var lenght = $('.ast_palm_wrapper .ast_palm_section').length-1;
		$('.ast_palm_wrapper .ast_palm_section').each(function(){
		    chk++;
			if($(this).css('display') != 'block'){
				$(this).show();
				cnt++;
				if(cnt == target){ return false;}
				if(chk == lenght){
					$('#ast_loadmore').hide();
				}
			}
			if(chk == 3){
			  $('#ast_loadmore').hide();
			}
			
		});
	}
	$('#ast_loadmore').on('click' , function(){
		loadmore_palm(1);
	}); 
	
new WOW().init();
})(jQuery);

;