var __widthMobile = 1000;
var __widthMobileDesktopSmall = 1150;
var __widthMobileTablet = 1000;
var __widthMobileTabletMiddle = 850;
var __widthMobileTabletSmall = 600;
var __widthMobileSmall = 540;
var __isMobile = ($(window).width() <= __widthMobile);
var __isMobileDesktopSmall = ($(window).width() <= __widthMobileDesktopSmall);
var __isMobileTablet = ($(window).width() <= __widthMobileTablet);
var __isMobileTabletMiddle = ($(window).width() <= __widthMobileTabletMiddle);
var __isMobileTabletSmall = ($(window).width() <= __widthMobileTabletSmall);
var __isMobileSmall = ($(window).width() <= __widthMobileSmall);
var __animationSpeed = 350;

function initElements(element) {
	$element=$(element ? element : 'body');

	$(window).on('resize',function(){
		onResize();
	});

	$.widget('app.selectmenu', $.ui.selectmenu, {
		_drawButton: function() {
		    this._super();
		    var selected = this.element
		    .find('[selected]')
		    .length,
		        placeholder = this.options.placeholder;

		    if (!selected && placeholder) {
		      	this.buttonItem.text(placeholder).addClass('placeholder');
		    } else {
		    	this.buttonItem.removeClass('placeholder');
		    }
		}
	});

	$.datepicker.regional['ru']={
           closeText: 'Закрыть',
           prevText: '&#x3c;Пред',
           nextText: 'След&#x3e;',
           currentText: 'Сегодня',
           monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
           monthNamesShort: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
           dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
           dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
           dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
           weekHeader: 'Нед',
           dateFormat: 'dd.mm.yy',
           firstDay: 1,
           isRTL: false,
           showMonthAfterYear: false,
           yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['ru']);

	$element.find('select').each(function(i, select) {
		// editable select
		if (typeof($(select).attr('editable')) != 'undefined' && $(select).attr('editable') !== 'false') {
			$(select).editableSelect({ 
				effects: 'fade',
				source: $(select).attr('source') ? $(select).attr('source') : false
			}).on('change.editable-select', function(e) {
				var $holder = $(e.target).closest('.input-holder');
				if ($holder.find('.es-input').val()) {
					$(e.target).closest('.input-holder').addClass('focused');
				} else {
					$(e.target).closest('.input-holder').removeClass('focused');
				}
			});

		// simple select
		} else {
			if ($(select).offset().left + 370 > $(window).width()) {
				$(select).attr('data-pos', 'right');
			}

			var offset = $(select).attr('data-offset');
			if ($(select).attr('data-pos') == 'right') {
				var data = {
					position: {my : "right"+(offset?"+"+offset:"")+" top-2", at: "right bottom"}
				}
			} else {
				var data = {
					position: {my : "left"+(offset?"+"+offset:"")+" top-2"}
				}
			}
			if (typeof($(select).attr('placeholder')) != 'undefined') {
				data['placeholder'] = $(select).attr('placeholder');
			}
			data['change'] = function(e, ui) {
				$(ui.item.element).closest('.input-holder').addClass('focused');
			}
			data['appendTo'] = $(select).parent();
			$(select).selectmenu(data);
			if (typeof($(select).attr('placeholder')) != 'undefined') {
				$(select).prepend('<option value="" disabled selected>' + data['placeholder'] + '</option>');
			}
		}
	});

	$element.find('.js-date').each(function(index,input){
		var datepicker_options = {
			inline: true,
			language: 'ru',
		    changeYear: true,
		    changeMonth: true,
		    showOtherMonths: true
		};
		var minYear=$(input).attr('data-min-year');
		if(minYear) datepicker_options.minDate='01.01.'+minYear;
		else minYear='c-10';
		var maxYear=$(input).attr('data-max-year');
		if(maxYear) datepicker_options.maxDate='01.01.'+maxYear;
		else maxYear='c+10';
		var defaultDate=$(input).attr('data-default-date');
		if(defaultDate) datepicker_options.defaultDate=defaultDate;
		datepicker_options.yearRange=[minYear,maxYear].join(':');
		
		$(input).attr('type','text').datepicker(datepicker_options).addClass('date').val($(input).attr('value')).after('<i></i>');
		$(input).next('i').click(function() {
			$(this).prev('input').datepicker('show');
			//initElements($('#ui-datepicker-div'));
		});
	});

	$element.find('input[type="checkbox"], input[type="radio"]').checkboxradio();
	$element.find('input[data-mask]').each(function(index,input){
		$(input).inputmask($(this).attr('data-mask'),{
			'clearMaskOnLostFocus': true
		});
	});

	$element.find('.file-upload').each(function(index, block) {
		$(block).find('.label').click(function(e){
			e.preventDefault();
		});
	});

	$element.find('.modal-close, .close-btn, .modal .js-cancel').click(function(e) {
		e.preventDefault();
		e.stopPropagation();

		/*
		if ($element.find('.modal-wrapper:visible').length > 1) {
			$element.find('.modal-wrapper[data-transparent]').stop().animate({'opacity': 1}, __animationSpeed);
			hideModal(this, true);
		} else {
			hideModal(this, false);
		}
		*/
		if (!$(this).closest('.modal-wrapper').hasClass('modal-done')) {
			hideModal(this, false);
		} else {
			$('body').find('.modal-wrapper').each(function(index, modal) {
				if ($(modal).css('display') != 'none') {
					hideModal(modal, false);
				}
			});
		}		
	});

	$element.find('.tabs, .js-tabs').lightTabs();

	$element.find('.js-scroll').each(function(index, block) {
		if (!$(block).attr('data-on-demand')) {
			scrollInit(block);
		}
	});

	$('body').mouseup(function(e) {
		/*
		if ($('.modal-fadeout').css('display') == 'block' && !$('html').hasClass('html-mobile-opened')) {
			if (!$(e.target).closest('.contents').length && !$(e.target).closest('.ui-selectmenu-menu').length && !$(e.target).closest('.ui-datepicker').length) {
				hideModal();
			}
		}
		*/
		if ($('html').hasClass('html-mobile-opened')) {
			if (!$(e.target).closest('.menu-holder').length) {
				$('nav .close').click();
			}
		}

	}).keypress(function(e){
		if (!e)e = window.event;
		var key = e.keyCode||e.which;

		if ($('.modal-fadeout').css('display') == 'block') {			
			if (key == 27) {
				hideModal();
			} 
		}
		if ($('html').hasClass('html-mobile-opened')) {
			if (key == 27) {
				$('nav .close').click();
			}
		}
	});

	$element.find('.input-holder input').keydown(function() {
		if ($(this).val()) {
			$(this).parent('.input-holder').addClass('focused');
		}
	}).keyup(function() {
		if (!$(this).val()) {
			$(this).parent('.input-holder').removeClass('focused');
		}
	}).focusout(function() {
		if (!$(this).val()) {
			$(this).parent('.input-holder').removeClass('focused');
		}
	}).each(function(i, item) {
		if ($(item).val()) {
			$(item).parent('.input-holder').addClass('focused');
		}
	});

	$element.find('textarea.js-autoheight').each(function(i, textarea) {
		if (!$(textarea).data('autoheight-inited')) {
			$(textarea).attr('rows', 1);
			$(textarea).on('input', function() {
				$(this).css('height', 'auto');
        		$(this).css('height', $(this)[0].scrollHeight+'px');
			});
			if ($(textarea).css('display') != 'none') $(textarea).trigger('input');
			$(textarea).data('autoheight-inited', true);
		}
	});

	fadeoutInit();
}

var resizeCallbacks = [
];
function onResize() {
	__isMobile = ($(window).width() <= __widthMobile);
	__isMobileTablet = ($(window).width() <= __widthMobileTablet);
	__isMobileTabletMiddle = ($(window).width() <= __widthMobileTabletMiddle);
	__isMobileTabletSmall = ($(window).width() <= __widthMobileTabletSmall);

	fadeoutInit();

	$.each(resizeCallbacks, function(i, func) {
		func();
	});
}

function parseUrl(url) {
	if (typeof(url) == 'undefined') url=window.location.toString();
	var a = document.createElement('a');
	a.href = url;

	var pathname = a.pathname.match(/^\/?(\w+)/i);	

	var parser = {
		'protocol': a.protocol,
		'hostname': a.hostname,
		'port': a.port,
		'pathname': a.pathname,
		'search': a.search,
		'hash': a.hash,
		'host': a.host,
		'page': pathname?pathname[1]:''
	}		

	return parser;
} 

function showModal(modal_id, data) {
	var $modal = $('#' + modal_id);

	if (!$modal.length) {
		if (typeof(data) == 'undefined') data = {};
		data.code = modal_id.replace('modal-', '');
		$.ajax({
			type: 'post',
			url: '/ajax--act-GetModal/',
			data: data,
			success: function(response) {
				$('body').append(response);
				$modal = $('#' + modal_id);

				initElements($modal);
				$modal.find('form[data-submit]').on('submit', function(e) {
					e.preventDefault();
					e.stopPropagation();

					eval($(this).attr('data-submit') + '(this)');
				});
				showModal(modal_id);
			}
		});
		return true;
	}

	var dontHideOthers = (typeof(data) != 'undefined' && typeof(data.dontHideOthers) != 'undefined') ? data.dontHideOthers : false;
	if (typeof(dontHideOthers) == 'undefined' || !dontHideOthers) $('.modal-wrapper:visible').not($modal).attr('data-transparent', true).stop().animate({'opacity': 0}, __animationSpeed);

	var display = __isMobileTablet ? 'block' : 'table';
	if (modal_id == 'modal-geo' && __isMobileTablet && !__isMobileTabletMiddle) {
		display = 'table';
	}

	$('.modal-fadeout').stop().fadeIn(300);
	$modal.stop().fadeIn(450).css({
		'display': display,
		'top': $(window).scrollTop()
	});

	var oversize = $(window).height() < $modal.find('.contents').outerHeight(true);

	if ($modal.attr('data-long') || oversize) {
		$('html').addClass('html-modal-long');

		if (oversize && __isMobile) {
			var modalHeight = $modal.outerHeight();
			$('#layout').data('scrollTop', $(window).scrollTop()).addClass('js-modal-overflow').height(modalHeight);
			$modal.css('top', 0);
			$('html,body').scrollTop(0);
		}
	} else {
		$('html').addClass('html-modal');
	}

	$modal.find('.js-scroll').each(function(index, block) {
		scrollInit(block);
	});
}

function hideModal(sender, onlyModal) {
	var $modal = sender ? $(sender).closest('.modal-wrapper') : $('.modal-wrapper:visible');
	if (typeof(onlyModal) == 'undefined' || !onlyModal) {
		$('.modal-fadeout').stop().fadeOut(300);
		if ($('#layout').data('scrollTop')) {
			var savedScrollTop =$('#layout').data('scrollTop');
			$('#layout').removeClass('js-modal-overflow').height('auto').removeData('scrollTop');
			$('html,body').scrollTop(savedScrollTop);
		}
		$modal.stop().fadeOut(450, function() {
			$modal.css('opacity', 1);
			$('html').removeClass('html-modal html-modal-long');
		});
	} else {
		$modal.stop().fadeOut(450, function() {
			$modal.css('opacity', 1);
		});
	}
}

function closeModal(sender) {
	if ($('.modal-wrapper:visible').length > 1) {
		$('.modal-wrapper[data-transparent]').stop().animate({'opacity': 1}, __animationSpeed);
		hideModal(sender, true);
	} else {
		hideModal(sender, false);
	}
}

function showModalConfirm(header, btn, action) {
	if (typeof(header) != 'undefined' && header) $('#modal-confirm>.modal>.contents>h1').text(header);
	if (typeof(btn) != 'undefined' && btn) $('#modal-confirm-action-btn').text(btn);
	if (typeof(action) == 'function') {
		$('#modal-confirm-action-btn').click(function(e) {
			e.preventDefault();
			e.stopPropagation();

			action();
			hideModal(this, $('.modal-wrapper:visible').length > 1);
		});
	}
	showModal('modal-confirm', {dontHideOthers: true});
}

function scrollInit(block) {
	if (!$(block).data('inited')) {
		var maxHeight = $(block).attr('data-max-height');
		if (maxHeight < 0) maxHeight = $(block).parent().height() - Math.abs(maxHeight);
		if (maxHeight && $(block).outerHeight() > maxHeight) {
			$(block).css('max-height', maxHeight + 'px').jScrollPane({
					showArrows: false,
					mouseWheelSpeed: 20,
					autoReinitialise: true,
					verticalGutter: 0,
					verticalDragMinHeight: 36
				}
			);
		}
		$(block).data('inited', true);
	}
}

function fadeoutInit(node) {
	$node = $(typeof(node) == 'undefined' ? 'body' : node);
	$node.find('.js-fadeout').each(function(i, block) {
		if (!$(block).data('inited')) {
			var $holder = $('<div class="fadeout-holder"></div>').insertAfter($(block));
			$holder.html($(block));
			$(block).data('inited', true);
		}

		if (typeof($(block).attr('data-nowrap')) != 'undefined' && $(block).attr('data-nowrap') != false && $(block).attr('data-nowrap') != 'false') {
			$(block).addClass('nowrap');
		}
		$(block).scrollLeft(0);
		var w_child = 0;
		var range = document.createRange();

		$.each(block.childNodes, function(i, node) {
			if (node.nodeType != 3) {
				w_child += $(node).outerWidth(true);
			} else {
				if (typeof(range) != 'undefined') {
					range.selectNodeContents(node);
					var size = range.getClientRects();
					if (typeof(size) != 'undefined' && typeof(size[0]) != 'undefined' && typeof(size[0]['width'] != 'undefined')) w_child += size[0]['width'];
				}
			}
		});

		var maxWidth = $(block).attr('data-max-width');
		var cloneWidth = $(block).attr('data-clone-width');
		var mobileOnly = $(block).attr('data-mobile-only');

		if (!mobileOnly || (mobileOnly && __isMobileTablet)) {
			if (cloneWidth) {
				$(block).width($(cloneWidth).width());
			}
			var holderWidth = $(block).width();
			if (w_child > holderWidth && (!maxWidth || $(window).width() <= maxWidth)) {
				$(block).addClass('fadeout').removeClass('nowrap').swipe({
					swipeStatus: function(event, phase, direction, distance) {
						var offset = distance;

						if (phase === $.fn.swipe.phases.PHASE_START) {
							var origPos = $(this).scrollLeft();
							$(this).data('origPos', origPos);

						} else if (phase === $.fn.swipe.phases.PHASE_MOVE) {
							var origPos = $(this).data('origPos');

							if (direction == 'left') {
								var scroll_max = $(this).prop('scrollWidth') - $(this).width();
								var scroll_value_new = origPos - 0 + offset;
								$(this).scrollLeft(scroll_value_new);
								if (scroll_value_new >= scroll_max) $(this).addClass('scrolled-full');
								else $(this).removeClass('scrolled-full');

							} else if (direction == 'right') {
								var scroll_value_new = origPos - offset;
								$(this).scrollLeft(scroll_value_new);
								$(this).removeClass('scrolled-full');
							}

						} else if (phase === $.fn.swipe.phases.PHASE_CANCEL) {
							var origPos = $(this).data('origPos');
							$(this).scrollLeft(origPos);

						} else if (phase === $.fn.swipe.phases.PHASE_END) {
							$(this).data('origPos', $(this).scrollLeft());
						}
					},
					threshold: 70,
					preventDefaultEvents: false
				});
			} else {
				$(block).removeClass('fadeout');
			}
		}
	});
}

function editableSelectReinit(select) {
	if (typeof(select) == 'string') var $select = $('#' + select);
	else $select = $(select);

	var id = $select.attr('id');
	$('#' + id + '_es').remove();
	$select.data('editable-select', false);
	$select.editableSelect({ 
		effects: 'fade',
		source: $select.attr('source') ? $select.attr('source') : false
	}).on('change.editable-select', function(e) {
		var $holder = $(e.target).closest('.input-holder');
		if ($holder.find('.es-input').val()) {
			$(e.target).closest('.input-holder').addClass('focused');
		} else {
			$(e.target).closest('.input-holder').removeClass('focused');
		}
	});
	$('#' + id + '_input').show();
	return true;
}

function getOffsetSum(elem) {
	var t = 0, l = 0;
	while (elem) {
		t += t + parseFloat(elem.offsetTop);
		l += l + parseFloat(elem.offsetLeft);
		elem = elem.offsetParent;
	}
	return {top: Math.round(t), left: Math.round(l)};
}
function getOffsetRect(elem) {
	var box = elem.getBoundingClientRect();
	var body = document.body;
	var docElem = document.documentElement;
	var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
	var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
	var clientTop = docElem.clientTop || body.clientTop || 0;
	var clientLeft = docElem.clientLeft || body.clientLeft || 0;
	var t  = box.top +  scrollTop - clientTop;
	var l = box.left + scrollLeft - clientLeft;
	return {top: Math.round(t), left: Math.round(l)};
}
function getOffset(elem) {
	if (elem.getBoundingClientRect) {
		return getOffsetRect(elem);
	} else {
		return getOffsetSum(elem);
	}
}
function redirect(url) {
	window.location = url;
}
function reload(forceGet) {
	window.location.reload(forceGet);
}

// Animated scroll to target
function _scrollTo(target, offset) {
	var wh = $(window).height();
	if (typeof(offset) == 'undefined') offset = Math.round($(target).outerHeight() /2) - Math.round(wh / 2);
	else if (offset === false) offset = 0;
	$('html,body').animate({
		scrollTop: $(target).offset().top + offset
	}, 1000);
}

var sendSro;

(function ($) {
	$.fn.lightTabs = function() {
		var showTab = function(tab, saveHash) {
			if (!$(tab).hasClass('tab-act')) {
				var tabs = $(tab).closest('.tabs');

				var target_id = $(tab).attr('href');
		        var old_target_id = $(tabs).find('.tab-act').attr('href');
		        $(target_id).show();
		        $(old_target_id).hide();
		        $(tabs).find('.tab-act').removeClass('tab-act');
		        $(tab).addClass('tab-act');

		        if (typeof(saveHash) != 'undefined' && saveHash) history.pushState(null, null, target_id);
			}
		}

		var initTabs = function() {
            var tabs = this;
            
            $(tabs).find('a').each(function(i, tab){
                $(tab).click(function(e) {
                	e.preventDefault();

                	showTab(this, true);
                	fadeoutInit();

                	return false;
                });
                if (i == 0) showTab(tab);                
                else $($(tab).attr('href')).hide();
            });	

            $(tabs).swipe({
				swipeStatus: function(event, phase, direction, distance) {
					var offset = distance;

					if (phase === $.fn.swipe.phases.PHASE_START) {
						var origPos = $(this).scrollLeft();
						$(this).data('origPos', origPos);

					} else if (phase === $.fn.swipe.phases.PHASE_MOVE) {
						var origPos = $(this).data('origPos');

						if (direction == 'left') {
							var scroll_max = $(this).prop('scrollWidth') - $(this).width();
							var scroll_value_new = origPos - 0 + offset;
							$(this).scrollLeft(scroll_value_new);
							if (scroll_value_new >= scroll_max) $(this).addClass('scrolled-full');
							else $(this).removeClass('scrolled-full');

						} else if (direction == 'right') {
							var scroll_value_new = origPos - offset;
							$(this).scrollLeft(scroll_value_new);
							$(this).removeClass('scrolled-full');
						}

					} else if (phase === $.fn.swipe.phases.PHASE_CANCEL) {
						var origPos = $(this).data('origPos');
						$(this).scrollLeft(origPos);

					} else if (phase === $.fn.swipe.phases.PHASE_END) {
						$(this).data('origPos', $(this).scrollLeft());
					}
				},
				threshold: 70
			});	
        };

        return this.each(initTabs);
    };

	$(function () {
		initElements();

		var url_data = parseUrl();

		// CHECK HASH FOR TABS
		$('.tabs, .js-tabs').find('a').each(function(i, link) {
			if (url_data.hash == $(link).attr('href')) {
				$(link).click();
			}
		});

		// FOOTER
		resizeCallbacks.push(function() {
			if (__isMobileTabletSmall) {
				$('#mn-law').appendTo($('footer .topline>.holder'));
			} else {
				$('#mn-law').insertAfter('#copyright');
			}
		});

		// MATERIALS
		if ($('#materials').length) {
			resizeCallbacks.push(function() {
				if (__isMobile) {
					$('#materials ul>li.last .info h2').after($('#materials ul>li.last .photo'));
				} else {
					$('#materials ul>li.last').prepend($('#materials ul>li.last .photo'));
				}
			});
		}

		onResize();

		// BURGER
		$('#menu-main>ul>li.has-child').each(function(i, li) {
			$(this).data('index-origin', $('#menu-main>ul>li').index(this));
		});
		$('#menu-holder').click(function() {
			if ((__isMobile || __isMobileDesktopSmall) && !$('body').hasClass('mobile-opened')) {
				if (!$('header').children('.close').data('inited')) {
					if (!$('header>.close').length) {
						$('header').append('<div class="close"></div>');
					}
					$('header').children('.close').click(function(e) {
						e.stopPropagation();

						// remove sublists internal
						$('#menu-main>ul[data-temp-num]').each(function(i, list) {
							var $li = $(list).children('li.has-child');
							var index = $li.data('index-origin');
							if (index > 0) {
								$('#menu-main>ul>li:eq(' + (index - 1) + ')').after($li);
							} else {
								$('#menu-main>ul').prepend($li);
							}
							$(list).remove();
						});

						$('body').removeClass('mobile-opened');
						$('#layout').height('auto').removeClass('js-modal-overflow');
						//$('.modal-fadeout').stop().fadeOut(300);	
					}).data('inited', true);
				}

				// move sublists external
				$('#menu-main>ul>li.has-child').each(function(i, li) {
					$('#menu-main').append('<ul class="temp" data-temp-num="' + i + '"></ul>');
					$('#menu-main').children('ul.temp[data-temp-num="' + i + '"]').append(li);
				});

				$('body').addClass('mobile-opened');

				if ($('header>.holder').outerHeight() > $(window).height()) {
					$('html').addClass('html-mobile-long');
				} else {
					$('html').removeClass('html-mobile-long');
				}

				$('#layout').addClass('js-modal-overflow').height($('header').outerHeight());

				//$('.modal-fadeout').stop().fadeIn(300);
			}
		});

		// ANCHORS
		$('.js-anchor').click(function(e) {
			e.preventDefault();
			_scrollTo($(this).attr('href'));
		});

		// MODAL LINKS
		$('.js-modal-link').click(function(e) {
			e.preventDefault();
			showModal($(this).attr('href').substring(1));
		});

		// MORE BUTTONS
		$('.js-more-btn').click(function(e) {
			e.preventDefault();
			var func = $(this).attr('data-function');
			eval(func + '(this)');
		});

		// SLICKS
		$('.js-slider').each(function(i, slider) {
			var mobile = $(slider).attr('data-mobile');
			var adaptive = $(slider).attr('data-adaptive');
			var dots = $(slider).attr('data-dots') === 'false' ? false : true;
			var arrows = $(slider).attr('data-arrows') === 'true' ? true : false;
			var autoplay = $(slider).attr('data-autoplay') ? $(slider).attr('data-autoplay') : false;
			var slidesToShow = adaptive ? Math.floor($(slider).outerWidth() / $(slider).children('li').outerWidth()) : 1;

			if (mobile) {
				if ((mobile === 'true' && __isMobile) ||
					(mobile === 'middle' && __isMobileTabletMiddle) ||
					(mobile === 'small' && __isMobileTabletSmall) ||
					(mobile === 'mobile' && __isMobileSmall)) {					

					$(slider).slick({
						slidesToShow: slidesToShow,
						slidesToScroll: slidesToShow,
						dots: dots,
						arrows: arrows,
						autoplay: autoplay
					});
				}
			} else {
				$(slider).slick({
					slidesToShow: slidesToShow,
					slidesToScroll: slidesToShow,
					dots: dots,
					arrows: arrows,
					autoplay: autoplay
				});
			}
		});

		// LIGHTBOXES
		var galleries = new Array();
		$('.js-lightbox').each(function(i, a) {
			if (!$(a).is('[data-gallery]')) {
				$(a).magnificPopup({
					type: 'image',
					removalDelay: 300,
					callbacks: {
				        beforeOpen: function() {
				            $(this.contentContainer).removeClass('fadeOutUpBig').addClass('animated fadeInDownBig');
				        },
				        beforeClose: function() {
				        	$(this.contentContainer).removeClass('fadeInDownBig').addClass('fadeOutUpBig');
				        }
				    },
					midClick: true
				});
			} else {
				if (typeof(galleries[$(a).attr('data-gallery')]) == 'undefined') galleries.push($(a).attr('data-gallery'));
			}
		});
		$.each(galleries, function(i, gallery) {
			$('.js-lightbox[data-gallery="' + gallery + '"]').magnificPopup({
				type: 'image',
				removalDelay: 300,
				callbacks: {
			        beforeOpen: function() {
			            $(this.contentContainer).removeClass('fadeOutUpBig').addClass('animated fadeInDownBig');
			        },
			        beforeClose: function() {
			        	$(this.contentContainer).removeClass('fadeInDownBig').addClass('fadeOutUpBig');
			        }
			    },
				gallery: {
					enabled: true
				},
				midClick: true
			});
		});

		// FEEDBACK
		$('#feedback').click(function(e) {
			e.preventDefault();

			showModal($(this).attr('data-modal'));
		});

		// GEO
		$('#bl-geo .curr').click(function() {
			showModal('modal-geo');

			var $list = $('#modal-geo .cities');
			var curr_id = $('#bl-geo .curr>span[data-id]').attr('data-id');
			$list.data('city-id', curr_id);			

			$('#city-keyword').change(function() {
				if ($list.data('filtered_keyword') != $(this).val()) {
					geoCityFilter();
					$list.data('filtered_keyword', $(this).val());
				}

			}).keyup(function(e) {
				if (e.which == 27) {
					if($(this).val().length > 0) {
						if ($list.data('filtered_keyword') != $(this).val()) {
							geoCityFilter();
							$list.data('filtered_keyword', $(this).val());
						}
					}
				} else {
					if ($(this).val().length > 0) {
						var geo_tid = $(this).data('timeout_id');
						if (geo_tid) clearTimeout(geo_tid);
						geo_tid = setTimeout(function() {
							if ($list.data('filtered_keyword') != $('#city-keyword').val()) {
								geoCityFilter();
								$list.data('filtered_keyword', $('#city-keyword').val());
							}
						}, 500);
						$(this).data('timeout_id', geo_tid);
					}
				}

			}).focusout(function() {
				if ($(this).val().length > 0) {
					if ($list.data('filtered_keyword') != $(this).val()) {
						geoCityFilter();
						$list.data('filtered_keyword', $(this).val());
					}
				}
			}).val('');

			$list.find('ul>li').click(function() {
				if ($(this).hasClass('active')) return false;
				var city_id = $list.data('city-id');
				$list.find('ul>li[data-id=' + city_id + ']').removeClass('active');
				$list.data('city-id', $(this).attr('data-id'));
				$(this).addClass('active');
				$('#modal-geo button').removeAttr('disabled').click();
			});

			$list.find('ul>li>a').click(function(e) {
				e.preventDefault();
			});

			$('#modal-geo button').click(function() {
				var data = {
					city_id: $list.data('city-id')
				};
				$.ajax({
					type: 'post',
					url: '/ajax--act-SetCity/',
					data: data,
					dataType: 'json',
					success: function(response) {
						if (response.status == true) {
							if (response.path) {
								redirect(response.path);
							} else {
								reload();
							}

						} else {
							msgSetError($('#modal-geo .cities'), response.error);
						}
					}
				});
			});
		});

		function geoCityFilter() {
			var keyword = $('#city-keyword').val();

			var $list = $('#modal-geo .cities');

			$.ajax({
				type: 'POST',
				url: '/ajax--act-FilterCitiesByKeyword/',
				data: {'keyword': keyword},
				success: function(response) {
					if (response) {
						$list.children('ul').html(response);
						$list.find('ul>li').click(function() {
							if ($(this).hasClass('active')) return false;
							var city_id = $list.data('city-id');
							$list.find('ul>li[data-id=' + city_id + ']').removeClass('active');
							$list.data('city-id', $(this).attr('data-id'));
							$(this).addClass('active');
							$('#modal-geo button').removeAttr('disabled').click();
						});

					} else {
						$('#modal-geo .empty').stop().slideDown(__animationSpeed);
					}
				}
			}); 
		}

		// SLIDER
		if ($('#slider').length) {
			var $items = $('#slider .slides>li');
			var $navItems = $('#slider .nav>li');
			var sliderTid;
			$('#slider').data('indexCurr', 0);

			function sliderSlide(indexNext) {
				var $items = $('#slider .slides>li');
				var indexCurr = $('#slider').data('indexCurr');
				if (typeof(indexNext) == 'undefined') {
					indexNext = (indexCurr < ($items.length - 1)) ? (indexCurr - 0 + 1) : 0;
				}else if(indexNext == -1) {
					indexNext = (indexCurr > 0) ? (indexCurr - 1) : ($items.length - 1);
				}

				$items.eq(indexCurr).removeClass('active');
				$items.eq(indexNext).addClass('active');

				$navItems.eq(indexCurr).removeClass('active');
				$navItems.eq(indexNext).addClass('active');

				$('#slider').data('indexCurr', indexNext);
			}

			function sliderAutoSet() {
				sliderTid = setInterval(function() {
					sliderSlide();
				}, sliderAutoSeconds * 1000);
			}

			function sliderAutoHold() {
				clearInterval(sliderTid);
				sliderAutoSet();
			}

			$navItems.click(function(e) {
				e.preventDefault();
				sliderAutoHold();
				sliderSlide($navItems.index($(this)));
			});

			$('#slider').swipe({
				swipeLeft: function() {
					sliderAutoHold();
					sliderSlide();
				},
				swipeRight: function() {
					sliderAutoHold();
					sliderSlide(-1);
				},
				threshold: 35
			});

			$('#slider .slides>li').on({
				mouseenter: function() {
					clearInterval(sliderTid);
				},
				mouseleave: function() {
					sliderAutoHold();
				}
			});

			$('#slider .slides>li').each(function(index, li) {
				var $a = $(li).find('.js-attempt');
				$a.click(function(e) {
					e.preventDefault();

					var target = $(this).attr('href');
					if (index == 0 && $(target).length) {
						_scrollTo(target);
					} else {
						showModal($(this).attr('data-modal'));
					}
				});
			});

			sliderAutoSet();
		}

		// SERVICE BANNER
		if ($('#banner').length) {
			$('#banner .js-attempt').click(function(e) {
				e.preventDefault();

				var target = $(this).attr('href');
				if ($(target).length) {
					_scrollTo(target);
				} else {
					showModal($(this).attr('data-modal'));
				}
			});
		}

		// BLOCK SRO COMPANIES		
		if ($('#bl-prices-table').length) {
			function priceTableInit() {
				$('#bl-prices-table table .companies>td').click(function() {
					if (!$(this).hasClass('active')) {
						var index = $('#bl-prices-table table .companies>td').index(this);
						$('#bl-prices-table table tr:not(:has(.companies))').each(function(i, tr) {
							$(tr).children('td:eq(' + index + ')').addClass('active').siblings('td').removeClass('active');
						});

						$(this).addClass('active').siblings('td').removeClass('active');
					}
				});
				$('#bl-prices-table table .companies>td>a').click(function(e) {
					e.preventDefault();
				});
				$('#bl-prices-table table tr>td>.btn').click(function(e) {
					e.preventDefault();
					var company_id = $(this).attr('data-company');

					if ($('#modal-sro-company-id').length) {
						$('#modal-sro-company-id').val(company_id);
					}

					showModal('modal-sro', {
						data: {
							'Action': '/ajax--act-Sro/',
							'CompanyId': company_id
						}
					});
				});
			}

			function priceSetCompany(data, extended) {
				var $colgroup = $('#bl-prices-table table>colgroup');
				var $body = $('#bl-prices-table table>tbody');
				var $tr = $body.children('tr');

				var sumTotal = parseInt(data['SumJoin']) + parseInt(data['SumPurpose']) + parseInt(data['SumMember']);

				$colgroup.append('<col></col>');

				$tr.eq(0).append('<td rowspan="2"><a href="#">' + data['Title'] + '</a></td>');
				$tr.eq(2).append('<td>' + data['Number'] + '</td>');
				$tr.eq(3).append('<td>' + parseInt(data['SumJoin']).toLocaleString('ru') + ' р</td>');
				$tr.eq(4).append('<td>' + parseInt(data['SumPurpose']).toLocaleString('ru') + ' р</td>');
				$tr.eq(5).append('<td>' + parseInt(data['SumMember']).toLocaleString('ru') + ' р</td>');

				var index = 5;
				$.each(extended, function(title, sum) {
					index++;
					$tr.eq(index).append('<td>' + sum.toLocaleString('ru') + ' р</td>');
					sumTotal += sum;
				});

				$tr.eq(index + 1).append('<td><span>' + sumTotal.toLocaleString('ru') + ' р</span> <button class="btn" data-company="' + data['Id'] + '">Вступить в СРО</button></td>');

				return true;
			}

			function priceTableSlidePrev() {
				var $cols = $('#bl-prices-table table>colgroup>col');
				$prev = $('#prices-arr-prev');
				$next = $('#prices-arr-next');

				var slided = $('#bl-prices-table').data('slided');

				var vislimit = __isMobileTabletMiddle ? 3 : 2;

				var from = slided;
				var to = slided + vislimit;

				$cols.eq(to).hide();
				$cols.eq(from).show();
				$('#bl-prices-table table>tbody>tr').each(function(index, tr) {
					$(tr).children('td:eq(' + to + ')').hide();
					$(tr).children('td:eq(' + from + ')').show();
				});

				$('#bl-prices-table').data('slided', slided - 1);				
				if (from <= 1) {
					$prev.addClass('disabled');
				}
				$next.removeClass('disabled');
			}

			function priceTableSlideNext() {
				var $cols = $('#bl-prices-table table>colgroup>col');
				$prev = $('#prices-arr-prev');
				$next = $('#prices-arr-next');

				var slided = $('#bl-prices-table').data('slided');
				var total = $cols.length - 1;

				var vislimit = __isMobileTabletMiddle ? 3 : 2;

				var from = slided + 1;
				var to = slided + vislimit + 1;

				$cols.eq(from).hide();
				$cols.eq(to).show();
				$('#bl-prices-table table>tbody>tr').each(function(index, tr) {
					$(tr).children('td:eq(' + from + ')').hide();
					$(tr).children('td:eq(' + to + ')').show();
				});

				$('#bl-prices-table').data('slided', slided + 1);
				$prev.removeClass('disabled');
				if (total <= to) {
					$next.addClass('disabled');
				}
			}

			priceTableInit();
		}

		// BLOCK CALCULATOR
		if ($('#bl-calculator').length) {
			var answers = {};
			var totalSum = 0;

			function calcSetStep(params) {
				var $ul = $('#bl-calculator .wrap>.overflow .flex');
				var $li = $('<li' + (params['active'] ? ' class="active"' : '') + ' data-step="' + params['index'] + '" data-id="' + params.data['Id'] + '"></li>').prependTo($ul);
				$li.append('<h3>' + params.data['Title'] + '</h3>');
				var $variants = $('<ul></ul>').appendTo($li);
				var counter = 0;
				for (index in params.data['Variants']) {
					var variant = params.data['Variants'][index];
					counter++;
					$variants.append('<li><input type="radio" name="calc' + params['index'] + '" id="calc' + params['index'] + '_' + counter + '" value="' + variant['Id'] + '" data-operation="' + variant['Operation'] + '" data-additional-action="' + variant['AdditionalAction'] + '" data-extended-title="' + variant['ExtendedTitle'] + '"><label for="calc' + params['index'] + '_' + counter + '">' + variant['Title'] + '</label></li>');
				}
				$variants.find('input:radio').change(function() {
					if ($(this).prop('checked')) {
						var index = $(this).closest('li[data-step]').attr('data-id');
						answers[index] = $(this).val();
					}
					$('#bl-calculator .wrap>.overflow .info>.btn').removeAttr('disabled');
				});
				initElements($variants);
			}

			function calcInit(data) {
				var $ul = $('#bl-calculator .wrap>.overflow .flex');
				var $liFinal = $ul.children('li#calc-final');
				var $step = $('#bl-calculator .wrap>.overflow .info>.step');
				var $btn = $('#bl-calculator .wrap>.overflow .info>.btn');

				var exclusions = new Array();
				$.each(data, function(index, item) {
					if (typeof(item[0]) != 'undefined' && item[0]['Excluded']) {
						exclusions.push(+index);
					}
				});

				var stepsTotal = Object.keys(data).length;
				var realStepsTotal = stepsTotal - exclusions.length;
				var stepCurr = 1;
				while (exclusions.includes(stepCurr) && stepCurr <= stepsTotal) {
					stepCurr++;
				}

				$step.text('1 из ' + realStepsTotal);

				calcSetStep({
					active: true,
					index: stepCurr,
					data: data[stepCurr][0]
				});

				if ($('#bl-calculator').attr('data-base-sum')) {
					totalSum = parseInt($('#bl-calculator').attr('data-base-sum'));
				}

				var x = 0;
				var extended = {};
				var sid = $('#bl-calculator').attr('data-type-id') ? $('#bl-calculator').attr('data-type-id') : 0;

				// если проставлена базовая операция
				if ($('#bl-calculator').attr('data-base-operation')) {
					// выполняем арифметическую операцию
					var totalSumOld = totalSum;
					totalSum = eval(totalSum + $('#bl-calculator').attr('data-base-operation').replace(/х/g, 'x')); // с заменой русской х на латинскую x
				}

				// если проставлено базовое доп действие
				if ($('#bl-calculator').attr('data-base-additional')) {
					// выполняем доп операции
					eval($('#bl-calculator').attr('data-base-additional').replace(/х/g, 'x')); // с заменой русской х на латинскую x
				}

				// если проставлено базовое наименование доп услуги
				if ($('#bl-calculator').attr('data-base-extended-title')) {
					// фиксируем наименование доп услуги
					extended[$('#bl-calculator').attr('data-base-extended-title')] = totalSum - totalSumOld;
				}

				$btn.click(function() {
					if (typeof(answers[$ul.children('li[data-step='+stepCurr+']').attr('data-id')]) != 'undefined') {
						var $radio = $ul.find('li[data-step='+stepCurr+'] input:radio:checked');

						if ($radio.attr('data-operation')) {
							// выполняем арифметическую операцию
							var totalSumOld = totalSum;
							totalSum = eval(totalSum + $radio.attr('data-operation').replace(/х/g, 'x')); // с заменой русской х на латинскую x
						}
						if ($radio.attr('data-additional-action')) {
							// выполняем доп операции
							eval($radio.attr('data-additional-action').replace(/х/g, 'x')); // с заменой русской х на латинскую x
						}

						if ($radio.attr('data-extended-title')) {
							// фиксируем наименование доп услуги
							extended[$radio.attr('data-extended-title')] = totalSum - totalSumOld;
						}

						var stepNext = stepCurr - 0 + 1;
						// check next step for exclusion
						while (exclusions.includes(stepNext) && stepNext <= stepsTotal) {
							stepNext++;
						}

						// next step
						if (stepNext <= stepsTotal
							&& (typeof(data[stepNext][$radio.val()]) != 'undefined'
							|| typeof(data[stepNext][0]) != 'undefined')
							) {
							calcSetStep({
								active: false,
								index: stepNext,
								data: typeof(data[stepNext][$radio.val()]) != 'undefined' ? data[stepNext][$radio.val()] : data[stepNext][0]
							});

							$btn.attr('disabled', true);

							$ul.children('li[data-step="' + stepCurr + '"]').stop().fadeOut(__animationSpeed, function() {
								$ul.children('li[data-step="' + stepNext + '"]').stop().fadeIn(__animationSpeed);
							});

							stepCurr = stepNext;
							var realStepCurr = stepCurr - exclusions.length;

							$step.text((typeof(realStepCurr) != 'undefined' ? realStepCurr : '0') + ' из ' + realStepsTotal);

						// final step
						} else {
							$step.stop().fadeOut(__animationSpeed);
							$btn.stop().fadeOut(__animationSpeed);

							$ul.children('li[data-step="' + stepCurr + '"]').stop().fadeOut(__animationSpeed, function() {
								$('#calc-final-sum').text(totalSum.toLocaleString('ru') + ' рублей');
								$('#calc-form').data({
									answers: answers,
									totalSum: totalSum
								});
								$liFinal.stop().fadeIn(__animationSpeed);
							});

							// filter prices table if exist
							if ($('#bl-prices-table').length) {
								if (typeof(sid) != 'undefined' && sid > 0) {
									$.ajax({
										type: 'POST',
										url: '/blocks--act-BlockServicePricesTable/',
										data: {'ajax': true, 'type_id': sid},
										dataType: 'json',
										success: function(response) {
											if (response.length && typeof(priceSetCompany) != 'undefined') {
												$('#bl-prices-table table>colgroup>col:gt(0)').remove();
												$('#bl-prices-table table>tbody>tr').each(function(index, tr) {
													$(tr).children('td:gt(0)').remove();
												});

												// parse calculated extended services
												$.each(extended, function(title, sum) {
													$('#bl-prices-table table>tbody>tr.summary').before('<tr><td class="title">' + title + '</td></tr>');
												});

												// parse companies response
												$.each(response, function() {
													priceSetCompany(this, extended);
												});

												// count companies
												if (!__isMobile) {
													var vislimit = 3;

													$('#bl-prices-table table>colgroup>col:gt(' + vislimit + ')').hide();
													$('#bl-prices-table table>tbody>tr').each(function(index, tr) {
														$(tr).children('td').eq(1).addClass('active');
														$(tr).children('td:gt(' + vislimit + ')').hide();
													});
													if ($('#bl-prices-table table>colgroup>col').length > vislimit + 1) {
														$('#prices-arr-prev').stop().fadeIn(__animationSpeed);
														$('#prices-arr-next').stop().fadeIn(__animationSpeed);
													}
													$('#prices-arr-prev').click(function() {
														if (!$(this).hasClass('disabled')) {
															priceTableSlidePrev();
														}
													});
													$('#prices-arr-next').click(function() {
														if (!$(this).hasClass('disabled')) {
															priceTableSlideNext();
														}
													});
													$('#bl-prices-table').data('slided', 0);

												} else {
													if ($('#bl-prices-table table>colgroup>col').length == 2) {
														$('#bl-prices-table').addClass('one-slide');
													} else if ($('#bl-prices-table table>colgroup>col').length == 3) {
														$('#bl-prices-table').addClass('two-slide');
													} else if ($('#bl-prices-table table>colgroup>col').length == 4) {
														$('#bl-prices-table').addClass('tree-slide');
													}

													$('#bl-prices-table table>tbody>tr').each(function(index, tr) {
														$(tr).children('td:eq(1)').addClass('active');
													}).addClass('active');
													$('#bl-prices-table table>tbody>tr.companies').swipe({
														swipeStatus: function(event, phase, direction, distance) {
															var offset = distance;
															$block = $('#bl-prices-table');

															if (phase === $.fn.swipe.phases.PHASE_START) {
																var origPos = $(this).scrollLeft();
																$(this).data('origPos', origPos);

															} else if (phase === $.fn.swipe.phases.PHASE_MOVE) {
																var origPos = $(this).data('origPos');

																if (direction == 'left') {
																	var scroll_max = $(this).prop('scrollWidth') - $(this).width();
																	var scroll_value_new = origPos - 0 + offset;
																	$(this).scrollLeft(scroll_value_new);
																	if (scroll_value_new >= scroll_max) $block.addClass('scrolled-full');
																	else $block.removeClass('scrolled-full');

																} else if (direction == 'right') {
																	var scroll_value_new = origPos - offset;
																	$(this).scrollLeft(scroll_value_new);
																	$block.removeClass('scrolled-full');
																}

															} else if (phase === $.fn.swipe.phases.PHASE_CANCEL) {
																var origPos = $(this).data('origPos');
																$(this).scrollLeft(origPos);

															} else if (phase === $.fn.swipe.phases.PHASE_END) {
																$(this).data('origPos', $(this).scrollLeft());
															}
														},
														threshold: 70
													});	
												}

												priceTableInit();

												$('#bl-prices-table').hide().insertAfter('#bl-calculator');
												$('#bl-prices-table').slideDown(__animationSpeed * 2, function() {
													if (__isMobile) {
														var paddingEdge = $('#bl-prices-table>.holder').css('paddingLeft');
														var heightTh = $('#bl-prices-table table .companies>td.active').outerHeight();
														var offsetTop = $('#bl-prices-table>.holder>h2').outerHeight(true);
														$('#bl-prices-table .mob-edge').hide().css({'top': offsetTop}).height(heightTh - 2).width(paddingEdge).fadeIn(__animationSpeed);
													}
												});

												_scrollTo('#bl-prices-table', false);
											}
										}
									});
								}
							}
						}
					}
				});
			}

			$.ajax({
				type: 'POST',
				url: '/blocks--act-BlockCalculator/',
				data: {'ajax': true, 'steps': true, 'page_id': $('#bl-calculator').attr('data-page-id')},
				dataType: 'json',
				success: function(response) {
					calcInit(response);					
				}
			}); 
		}

		// FAQ
		if ($('#faq').length) {
			$('#faq ul>li .question .btn').click(function() {
				var $li = $(this).closest('li');
				if ($li.hasClass('opened')) {
					$(this).text('Ответ');
					$li.find('.answer').slideUp(__animationSpeed, function() {
						$li.removeClass('opened');
					});

				} else {
					$(this).text('Скрыть');
					$li.find('.answer').hide();
					$li.addClass('opened');
					$li.find('.answer').slideDown(__animationSpeed);
				}
			});
		}

		function faqMore(btn) {
			var $list = $('#faq');
			var $pagination = $('#pagination');
			var page = $list.data('page');
			if (typeof(page) == 'undefined') {
				var matches = url_data.pathname.match(/\-page\-(\d+)/i);
				page = (matches != null) ? Number(matches[1]) : 1;
			}

			$.ajax({
				type: 'post',
				url: '/faq--act-GetMoreByPage/',
				data: {'page': page + 1},
				dataType: 'json',
				success: function(response) {
					$list.data('page', page + 1);

					if (response.status == true) {
						var h_origin = $list.height();
						$list.find('ul').append(response.list);
						var h_actual = $list.height();
						$list.addClass('overflow').height(h_origin).animate({height: h_actual}, __animationSpeed, function() {
							$list.removeClass('overflow').height('auto');
						});
						$pagination.replaceWith(response.pagination);

						if (response.last) {
							$list.find('.btn-line').slideUp(__animationSpeed);
						}
					}
				}
			});			
		}

		// EDUCATION PROGRAMMS
		if ($('#bl-programms').length) {
			function filterPrograms(keyword) {
				var $list = $('#bl-programms .body-content table tbody tr');

				var tid;
				if (tid) clearTimeout(tid);
	            tid = setTimeout(function() {
	               	if (keyword) {
	               		$list.each(function(index, tr) {
		                   	var name = $(tr).children('td:first').text().toLowerCase();
		                   	if (name.indexOf(keyword) + 1) {
			                    $(tr).show();
			                } else {
			                    $(tr).hide();
			                }
		                });
	               	} else {
	               		$list.filter(':hidden').show();
	               	}
	            }, 400);
			}

			var $filterInput = $('#bl-programms .filter>input:text');
			$filterInput.on('focusout keyup change', function() {
				filterPrograms($filterInput.val().toLowerCase());
			});
			filterPrograms($filterInput.val().toLowerCase());
		}

		// DONE FORM
		function showDoneModal(header, text) {
			var $contents = $('#modal-done>.modal>.contents');
			$contents.children('h1, .h1').html(header);
			$contents.children('.text').html(text);
			showModal('modal-done');
		}

		// FEEDBACK FORM
		function sendFeedback(form) {
			msgUnset(form);
			checkResetStatus(form);
			if ($(form.agree).prop('checked')) {
				msgUnset(form.tel);

				if (checkElements(
					[form.tel], 
					[{1: true}]
				)) {
					form.submit_btn.disabled = true;
					var waitNode = msgSetWait(form);

					$(form).append('<input type="hidden" name="capcha" value="' + navigator.userAgent + '"/>');
					
					$.ajax({
						type: $(form).attr('method'),
						url: $(form).attr('action'),
						data: $(form).serialize(),
						dataType: 'json',
						success: function(response) {
							if(response.status == true) {
								showDoneModal(response.header, response.message);
							} else {
								msgSetError(form, response.message);
							}
							$(waitNode).remove();
							form.submit_btn.disabled = false;
						}
					});
				} else {
					msgSetError(form.tel, 'Введите, пожалуйста, телефон');
				}
			} else {
				// agreement
			}
		}

		// CALLBACK FORM
		function sendCallback(form) {
			msgUnset(form);
			checkResetStatus(form);
			if ($(form.agree).prop('checked')) {
				msgUnset(form.tel);

				if (checkElements(
					[form.tel], 
					[{1: true}]
				)) {
					form.submit_btn.disabled = true;
					var waitNode = msgSetWait(form);

					$(form).append('<input type="hidden" name="capcha" value="' + navigator.userAgent + '"/>');
					
					$.ajax({
						type: $(form).attr('method'),
						url: $(form).attr('action'),
						data: $(form).serialize(),
						dataType: 'json',
						success: function(response) {
							if(response.status == true) {
								showDoneModal(response.header, response.message);
							} else {
								msgSetError(form, response.message);
							}
							$(waitNode).remove();
							form.submit_btn.disabled = false;
						}
					});
				} else {
					//msgSetError(form.tel, 'Введите, пожалуйста, телефон');
				}
			} else {
				// agreement
			}
		}

		// TARGET FORM
		function sendTarget(form) {
			msgUnset(form);
			checkResetStatus(form);
			//if ($(form.agree).prop('checked')) {
				if (checkElements(
					[form.tel], 
					[{1: true}]
				)) {
					form.submit_btn.disabled = true;
					var waitNode = msgSetWait(form);

					$(form).append('<input type="hidden" name="capcha" value="' + navigator.userAgent + '"/>');
					
					$.ajax({
						type: $(form).attr('method'),
						url: $(form).attr('action'),
						data: $(form).serialize(),
						dataType: 'json',
						success: function(response) {
							if(response.status == true) {
								showDoneModal(response.header, response.message);
							} else {
								msgSetError(form, response.message);
							}
							$(waitNode).remove();
							form.submit_btn.disabled = false;
						}
					});
				}
			//} else {
				// agreement
			//}
		}

		// CONSULTATION FORM
		function sendConsultation(form) {
			msgUnset(form);
			checkResetStatus(form);
			if ($(form.agree).prop('checked')) {
				msgUnset(form.tel);

				if (checkElements(
					[form.tel], 
					[{1: true}]
				)) {
					form.submit_btn.disabled = true;
					var waitNode = msgSetWait(form);

					$(form).append('<input type="hidden" name="capcha" value="' + navigator.userAgent + '"/>');
					
					$.ajax({
						type: $(form).attr('method'),
						url: $(form).attr('action'),
						data: $(form).serialize(),
						dataType: 'json',
						success: function(response) {
							if(response.status == true) {
								showDoneModal(response.header, response.message);
							} else {
								msgSetError(form, response.message);
							}
							$(waitNode).remove();
							form.submit_btn.disabled = false;
						}
					});
				} else {
					msgSetError(form.tel, 'Введите, пожалуйста, телефон');
				}
			} else {
				// agreement
			}
		}

		// CALCULATOR FORM
		function sendCalculation(form) {
			msgUnset(form);
			checkResetStatus(form);
			msgUnset(form.tel);

			if (checkElements(
				[form.tel], 
				[{1: true}]
			)) {
				form.submit_btn.disabled = true;
				var waitNode = msgSetWait(form);

				$(form).append('<input type="hidden" name="capcha" value="' + navigator.userAgent + '"/>');

				var data = {
					tel: $(form.tel).val(),
					code: $(form.code).val(),
					answers: $(form).data('answers'),
					totalSum: $(form).data('totalSum'),
					pageId: $('#bl-calculator').attr('data-page-id'),
					capcha: $(form.capcha).val()
				};
					
				$.ajax({
					type: $(form).attr('method'),
					url: $(form).attr('action'),
					data: data,
					dataType: 'json',
					success: function(response) {
						if(response.status == true) {
							showDoneModal(response.header, response.message);
						} else {
							msgSetError(form, response.message);
						}
						$(waitNode).remove();
						form.submit_btn.disabled = false;
					}
				});
			} else {
				msgSetError(form.tel, 'Введите, пожалуйста, телефон');
			}
		}

		// SRO FORM
		sendSro = function(form) {
			msgUnset(form);
			checkResetStatus(form);
			if ($(form.agree).prop('checked')) {
				msgUnset(form.tel);

				if (checkElements(
					[form.tel], 
					[{1: true}]
				)) {
					form.submit_btn.disabled = true;
					var waitNode = msgSetWait(form);

					$(form).append('<input type="hidden" name="capcha" value="' + navigator.userAgent + '"/>');
					
					$.ajax({
						type: $(form).attr('method'),
						url: $(form).attr('action'),
						data: $(form).serialize(),
						dataType: 'json',
						success: function(response) {
							if(response.status == true) {
								showDoneModal(response.header, response.message);
							} else {
								msgSetError(form, response.message);
							}
							$(waitNode).remove();
							form.submit_btn.disabled = false;
						}
					});
				} else {
					//msgSetError(form.tel, 'Введите, пожалуйста, телефон');
				}
			} else {
				// agreement
			}
		}

		// FORMS
		$('form[data-submit]').on('submit', function(e) {
			e.preventDefault();
			e.stopPropagation();

			eval($(this).attr('data-submit') + '(this)');
		});

		// SOCIALS
		$('#social-shares>ul>li>a').click(function(e) {
			e.preventDefault();
			var $shares = $('#social-shares');
			var url = $shares.attr('data-share-url');
			var title = $shares.attr('data-share-title');
			var image = $shares.attr('data-share-image');
			var description = $shares.attr('data-share-description');
			var api_url = $(this).attr('data-api-url');
			
			api_url = api_url.split('%url%').join(url).split('%title%').join(title).split('%image%').join(image).split('%description%').join(description);
			window.open(api_url, title, 'width=640,height=480,status=no,toolbar=no,menubar=no');
		});

		// NEWS
		function newsMore(btn) {
			var $list = $('#materials');
			var $pagination = $('#pagination');
			var page = $list.data('page');
			if (typeof(page) == 'undefined') {
				var matches = url_data.pathname.match(/\-page\-(\d+)/i);
				page = (matches != null) ? Number(matches[1]) : 1;
			}

			$.ajax({
				type: 'post',
				url: '/news--act-GetMoreByPage/',
				data: {'page': page + 1},
				dataType: 'json',
				success: function(response) {
					$list.data('page', page + 1);

					if (response.status == true) {
						var h_origin = $list.height();
						$list.children('ul').append(response.list);
						var h_actual = $list.height();
						$list.addClass('overflow').height(h_origin).animate({height: h_actual}, __animationSpeed, function() {
							$list.removeClass('overflow').height('auto');
						});
						$pagination.replaceWith(response.pagination);

						if (response.last) {
							$list.find('.btn-line').slideUp(__animationSpeed);
						}
					}
				}
			});			
		}

		// STATUES
		function statuesMore(btn) {
			var $list = $('#materials');
			var $pagination = $('#pagination');
			var page = $list.data('page');
			if (typeof(page) == 'undefined') {
				var matches = url_data.pathname.match(/\-page\-(\d+)/i);
				page = (matches != null) ? Number(matches[1]) : 1;
			}

			$.ajax({
				type: 'post',
				url: '/statues--act-GetMoreByPage/',
				data: {'page': page + 1},
				dataType: 'json',
				success: function(response) {
					$list.data('page', page + 1);

					if (response.status == true) {
						var h_origin = $list.height();
						$list.children('ul').append(response.list);
						var h_actual = $list.height();
						$list.addClass('overflow').height(h_origin).animate({height: h_actual}, __animationSpeed, function() {
							$list.removeClass('overflow').height('auto');
						});
						$pagination.replaceWith(response.pagination);

						if (response.last) {
							$list.find('.btn-line').slideUp(__animationSpeed);
						}
					}
				}
			});			
		}

	})
})(jQuery)