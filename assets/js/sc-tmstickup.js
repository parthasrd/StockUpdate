(function($,undefined){
	var 
		def={
			stuckClass:'isStuck'
		}
		,doc=$(document),anim = false;

	$.fn.TMStickUp=function(opt){
		opt=$.extend(true,{},def,opt)

		$(this).each(function(){
			var $this=$(this)
				,posY//=$this.offset().top+$this.outerHeight()
				,isStuck=false
				,clone=$this.clone().appendTo($this.parent()).addClass(opt.stuckClass)
				,height//=$this.outerHeight()
				,stuckedHeight=clone.outerHeight()
				,opened//=$.cookie&&$.cookie('panel1')==='opened'
				,tmr

			$(window).resize(function(){
				//alert(clone.outerHeight())
				//alert($this.offset().top)
				clearTimeout(tmr)				
				clone.css({top:isStuck?0:-stuckedHeight,visibility:isStuck?'visible':'hidden'})
				tmr=setTimeout(function(){
					posY=$this.offset().top//+$this.outerHeight()				
					height=350
					stuckedHeight=clone.outerHeight()
					opened=$.cookie&&$.cookie('panel1')==='opened'
						
					clone.css({top:isStuck?0:-stuckedHeight})					
				},40)
			}).resize()

			clone.css({
				position:'fixed'				
				,width:'100%'
			})
			
			
			//clone.img({
//				transform: 'translate(0em, 4em)'				
//			})
					
			$this
				.on('rePosition',function(e,d){
					if(isStuck)
						clone.animate({marginTop:d},{easing:'linear'})
					if(d===0)
						opened=false
					else
						opened=true
				})
			
			doc
				.on('scroll',function(){
					var scrollTop=doc.scrollTop()
					
					
						
					if(scrollTop>=posY&&!isStuck){
						
						clone
							//.prop('id', 'stc'+1 )
							.stop()
							.css({visibility:'visible'})
							.animate({
								top:0
								,marginTop:opened?50:0
							},{

							})
							.find("#stc").attr("id","stc_1"); 
							
						isStuck=true
						
						
					}
					
					//alert()
					if(scrollTop<posY+height&&isStuck){
						$('.sf-menu ul').css('display', 'none');

						var sf = $('.search-form');
						if(sf.length > 0){
							sf.find('input').blur();
						}

						clone
							
							.stop()
							.animate({
								top:-stuckedHeight
								,marginTop:0
							},{
								duration:200
								,complete:function(){
									clone.css({visibility:'hidden'})
								}
							})
						.find("#stc_1").attr("id","stc");
						isStuck=false

					}			
				})				
				.trigger('scroll')
		})
	}
})(jQuery)



	   

 

