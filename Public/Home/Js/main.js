
var system = (function(){
	
	var headerNavControl = function(){
		// 获取页面元素
		var myElement = document.querySelector("#headerNav");
		// 创建 Headroom 对象，将页面元素传递进去
		var headroom  = new Headroom(myElement);
		// 初始化
		headroom.init(); 
	};
	
	var responsiveNavControl = function(){
		$( "span.menu" ).click(function() {
			$( ".head-nav ul" ).slideToggle(300, function() {});
		});
	};
	
	var navScrollControl = function(){
		$(".scroll").click(function(event){	
			
			$(this).parents("ul").hide();
			
			event.preventDefault();
			$('html,body').animate({
				scrollTop:$(this.hash).offset().top
			},1200);
		});
	};
	
	var sliderControl = function(){
		var flexslider = $(".flexslider");
		flexslider.each(function(){
			var $this = $(this),
				isNav = $this.attr("data-nav")=="1"?true:false,
				isPage = $this.attr("data-page")=="1"?true:false;
			
			$this.flexslider({
			       animation: "slide",
			       directionNav:isNav,
			       controlNav:isPage,
			       prevText:"<i class='fa fa-angle-left fa-4x'></i>",
			       nextText:"<i class='fa fa-angle-right fa-4x'></i>"
			});
		});
	};
	
	
	return {
		init:function(){
			headerNavControl();
			
			responsiveNavControl();
			
			sliderControl();
			
			navScrollControl();
		}
	};
})();

$(function(){
	system.init();
});
