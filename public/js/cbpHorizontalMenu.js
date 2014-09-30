/**
 * cbpHorizontalMenu.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2013, Codrops
 * http://www.codrops.com
 */
var cbpHorizontalMenu = (
		function() {
		    var start=1;
			var listItems = $( '#cbp-hrmenu > ul > li' ),
				menuItems = listItems.children( 'a' ),
				body = $( 'container2' ),
				current = -1;
		
			
			function open(event) {
				if( current !== -1 ) 
				{   
		            var item = $( event.currentTarget ).parent( 'li' );
		            var next_idx = item.index();
		            if(next_idx !== current)
		            	listItems.eq( current ).removeClass( 'cbp-hropen' );
				}
				
		        if(start==0)
		        {
		        	var $item = $( event.currentTarget ).parent( 'li' );
					idx = $item.index();
		        }
		        else{
		            item = $(document.getElementById('mainTab')).parent('li');
		            idx = item.index();
		            item.addClass( 'cbp-hropen' );
		            current = idx;
		            start=0;
		            return false;
		        }
		
				if( current !== idx ) {
					item.addClass( 'cbp-hropen' );
					current = idx;
					body.off( 'click' ).on( 'click', close );
				}
		
				return false;
		
			}
			
			function init() {
		        if(start == 1){
		            open();
		        }
		
				menuItems.on( 'click', open );
		
				listItems.on( 'click', function( event ) { 
															event.stopPropagation(); 
													} 
							);
			}
		
			
		
			function close( event ) {
				listItems.eq( current ).removeClass( 'cbp-hropen' );
				current = -1;
			}
		
			return { init : init };
		
		}
)();