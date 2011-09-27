/* Copyright (c) 2010 WordImpressed.com jFlow Plus derived from Kean Loong Tan's orgininal jFlow http://www.wordimpressed.com
 * Licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * jFlow 1.2 (Plus)
 * Version: jFlow Plus
 * Requires: jQuery 1.2+
 * 
 * modified by StudioPress to add loop; scroll- up, down, left; cover- up, down, left, right; fade; wipe
 */

(function($) {
	$.fn.jFlow = function(options) {
		var opts = $.extend({}, $.fn.jFlow.defaults, options);
		var randNum = Math.floor(Math.random()*11);
		var jFC = opts.controller;
		var jFS =  opts.slideWrapper;
		var jSel = opts.selectedWrapper;
		var maxi = $(jFC).length;
		var cur = (opts.effect == 'up' || opts.effect == 'left') ? maxi - 1 : 0;
		var timer;
		// sliding function
		var slide = function (dur, i) {
			$(opts.slides).children().css({
				overflow:"hidden"
			});
			$(opts.slides + " iframe").hide().addClass("temp_hide");
			if (opts.vertical)
				animation = { marginTop: "-" + (i * $(opts.slides).find(":first-child").height()) + "px" };
			else
				animation = { marginLeft: "-" + (i * $(opts.slides).find(":first-child").width()) + "px" };
			
			$(opts.slides).animate(
				animation,
				opts.duration*(dur),
				opts.easing,

				function(){
					$(opts.slides).children().css({
						overflow:"hidden"
					});
					$(".temp_hide").show();
				}
			);
		}
		var fade = function (i) {
			$(opts.slides+' > .jFlowSlideContainer:eq(' + ((i == 0 ? maxi : i) - 1) + ')').fadeOut(opts.duration,'linear',function(){
				$(opts.slides+' > .jFlowSlideContainer').hide();
				if (i < maxi)
					$(opts.slides+' > .jFlowSlideContainer:eq(' + i + ')').fadeIn(opts.duration / 2,opts.easing);
				else
					$(opts.slides+' > .jFlowSlideContainer:eq(0)').fadeIn(opts.duration / 2,opts.easing);
			});
		}
		var wipe = function (i) {
			var slide = $(opts.slides+' > .jFlowSlideContainer:eq(' + i + ')');

			gseffect({
					marginTop: (slide.height() * (i == 0 ? -1 : -2)) + 'px',
					marginLeft: (slide.width() * -1) + 'px'
				},
				slide,
				i);
		}
		var cover = function (dir,i) {
			var slide = $(opts.slides+' > .jFlowSlideContainer:eq(' + i + ')');
			var tstart,tleft;

			switch(dir) {
				case 'up':
					tstart = (i == 0 ? 1 : 0);
					tleft = 0;
					break;
				case 'down':
					tstart = (i == 0 ? -1 : -2);
					tleft = 0;
					break;
				case 'left':
					tstart = (i == 0 ? 0 : -1);
					tleft = 1;
					break;
				case 'right':
					tstart = (i == 0 ? 0 : -1);
					tleft = -1;
					break;
			}

			gseffect({
					marginTop: (slide.height() * tstart) + 'px',
					marginLeft: (slide.width() * tleft) + 'px'
				}, 
				slide,
				i);
		}
		var gseffect = function (start,slide,i) {
			if (i == 0)
				$(opts.slides+' > .jFlowSlideContainer:eq(' + (maxi - 1) + ')').css({ position: 'absolute' });
			slide.css({ zIndex: 10 });
			slide.css(start);
			slide.show();
			opts.isanimated = 1;
			slide.animate({
					marginTop: (slide.height() * (i == 0 ? 0 : -1)) + 'px',
					marginLeft: '0px'
				},
				opts.duration,
				opts.easing,
				function() {
					$(opts.slides+' > .jFlowSlideContainer:eq(' + ((i == 0 ? maxi : i) - 1) + ')').hide().css({ position: 'relative' });
					slide.css({
						zIndex: 0,
						marginTop: '0px',
						marginLeft: '0px'
					});
					opts.isanimated = 0;
				}
			);					 
		}
		var gsanimate = function (dur,cur) {
			var cov = opts.effect.split('-');
			if (cov[0] == 'cover')
				cover(cov[1],cur);
			else if (opts.effect == 'fade')
				fade(cur);
			else if (opts.effect == 'wipe')
				wipe(cur);
			else
				slide(dur,cur);
		}
		$(this).find(jFC).each(function(i){
			$(this).click(function(){
				dotimer();
				if ($(opts.slides).is(":not(:animated)")) {
					$(jFC).removeClass(jSel);
					$(this).addClass(jSel);
					var dur = Math.abs(cur-i);
					gsanimate(dur,i);
					cur = i;
				}
			});
		});
		$(opts.slides).before('<div id="'+jFS.substring(1, jFS.length)+'"></div>').appendTo(jFS);
		$(opts.slides).find("div").each(function(){
			$(this).before('<div class="jFlowSlideContainer"></div>').appendTo($(this).prev());
		});
		//initialize the controller
		opts.vertical=(opts.effect=='up'||opts.effect=='down')?1:0;
		$(jFC).eq(cur).addClass(jSel);
		var resize = function (x){
			$(jFS).css({
				position:"relative",
				width: opts.width,
				height: opts.height,
				overflow: "hidden"
			});
			//opts.slides or #mySlides container
			$('.genesis-slider-scroll '+opts.slides).css({
				position:"relative",
				width: $(jFS).width()*(opts.vertical?1:$(jFC).length)+"px",
				height: $(jFS).height()*(opts.vertical?$(jFC).length:1)+"px",
				overflow: "hidden"

			});
			// jFlowSlideContainer
			$(opts.slides).children().css({
				position:"relative",
				width: $(jFS).width()+"px",
				height: $(jFS).height()+"px",
				"float":"left",
				overflow:"hidden"
			});
			if (opts.vertical) {
				$('.genesis-slider-scroll '+opts.slides).css({
					marginTop: "-" + (cur * $(opts.slides).find(":eq(0)").height() + "px")
				});
			} else {
				$('.genesis-slider-scroll '+opts.slides).css({
					marginLeft: "-" + (cur * $(opts.slides).find(":eq(0)").width() + "px")
				});
			} 
		}
		// sets initial size
		resize();
		// resets size
		$(window).resize(function(){
			resize();						  
		});
		$(opts.prev).click(function(){
			dotimer();
			doprev();
		});
		$(opts.next).click(function(){
			dotimer();
			donext();		
		});
		var doprev = function (x){
			if (opts.isanimated)
				return;

			var dur = 1;
			if (cur > 0)
				cur--;
			else if (maxi > 1 && opts.loop) {
				if (opts.vertical) {
					$(opts.slides).css({
						marginTop: "-" + $(opts.slides+' > .jFlowSlideContainer:first').height() + "px"
					});
				} else {
					$(opts.slides).css({
						marginLeft: "-" + $(opts.slides+' > .jFlowSlideContainer:first').width() + "px"
					});
				}
				$(opts.slides+' > .jFlowSlideContainer').last().clone(true).insertBefore(opts.slides+' > .jFlowSlideContainer:first');
				$(opts.slides+' > .jFlowSlideContainer').last().remove();
			} else {
				cur = maxi -1;
				dur = cur;
			}
			$(jFC).removeClass(jSel);
			gsanimate(dur,cur);
			$(jFC).eq(cur).addClass(jSel);
		}
		var donext = function (x){
			if (opts.isanimated)
				return;
				
			var dur = 1;
			if (cur < maxi - 1)
				cur++;
			else if (maxi > 1 && opts.loop) {
				first = $(opts.slides+' > .jFlowSlideContainer:first').clone(true);
				$(opts.slides).append(first);
				$(opts.slides+' > .jFlowSlideContainer:first').remove();
				if (opts.vertical) {
					$(opts.slides).css({
						marginTop: "-" + ((maxi - 2) * $(opts.slides+' > .jFlowSlideContainer:first').height()) + "px"
					});
				} else {
					$(opts.slides).css({
						marginLeft: "-" + ((maxi - 2) * $(opts.slides+' > .jFlowSlideContainer:first').width()) + "px"
					});
				}
			} else {
				cur = 0;
				dur = maxi -1;
			}
			$(jFC).removeClass(jSel);
			gsanimate(dur,cur);
			$(jFC).eq(cur).addClass(jSel);
		}
		var dotimer = function (x){
			if((opts.auto) == true) {
				if(timer != null) 
					clearInterval(timer);

				timer = setInterval(function() {
					if (opts.effect=='left' || opts.effect=='up')
						$(opts.prev).click();
					else
						$(opts.next).click();
				}, opts.timer);
			}
		}
//Pause/Resume function fires at hover
		dotimer();
			$(opts.slides).hover(
			function() {
			clearInterval(timer);
			},
		function() {
			dotimer();
			}
		);
	};
	$.fn.jFlow.defaults = {
		controller: ".myController", // must be class, use . sign
		slideWrapper : "#slides", // must be id, use # sign
		selectedWrapper: "jFlowSelected",  // just pure text, no sign
		easing: "swing",
		width: "100%",
		loop: 0,
		effect: "right",
		prev: ".slider-previous", // must be class, use . sign
		next: ".slider-next" // must be class, use . sign
	};
})(jQuery);