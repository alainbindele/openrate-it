/*
 * Application name: OpenRate-it!
 * A general-purpose polling platform
 * Copyright (C) 2014  Alain Bindele (alain.bindele@gmail.com)
 * This file is part of OpenRate-it!
 * OpenRate-it! is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * OpenRate-it! is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

circlesTextFieldsNumber = 0;
function addCirclesInput(name) {
	if (circlesTextFieldsNumber < 10) {
		var id = name + circlesTextFieldsNumber;
		$('#addCircleText').append(
			'<input class="pure-input-1" type="text" ' +
			'name="'+ name+  // PRIMA era NAME!! in zend voglio passare il nome numerato!
			'" id="'+ name+
			'" class="txtfield" style="z-index:1; position:relative; " ' +
			'placeholder="Circle '+
			(circlesTextFieldsNumber+1)+'"size="68">');
		
		circlesTextFieldsNumber += 1;
		//document.addCirclesForm.howManyCircles.value = circlesTextFieldsNumber;
	} else {
		$('#warning').html("Only 10 circles allowed.");
		document.form.add.disabled = true;
	}
}



jQuery(document).on('ready', function() {
	jQuery('form#addCirclesForm').bind('submit', function(event){
		event.preventDefault();
		var response;
		var form = this;
		var jsonRequest={};
		var circles=[];
		
		function a(form){
			if(typeof(form.circles.length)!='undefined'){
				for (var i = 0; i < form.circles.length; i++) {
					if(form.circles.length>1){
						circles.push(form.circles[i].value);
					}
					else{
						circles.push(form.circles.value);
					}
				}return circles;
			}else{
				circles.push(form.circles.value);
				return circles;
			}
		}
		
		jsonRequest.circles		=	a(form);
		jsonRequest.user		=	form.user.value;
		jsonRequest.PHPSESSID 	= 	$.cookie('PHPSESSID');
        $.ajax({
            type: "POST",
            url: "/users/add-circle",
            data: JSON.stringify(jsonRequest),
            contentType: "application/json; charset=utf-8",
            dataType: "json"
        }).done(function(text) { 
        	$("#alert")
            .append("Request:<br><pre><div style='overflow:auto;max-height:100px'>"+JSON.stringify(jsonRequest,undefined,2)+"</div></pre>")
            .append("Response:<br><pre><div style='overflow:auto;max-height:100px'>"+JSON.stringify(text,undefined,2)+"</div></pre>").show();
        		})
        	.fail(function (jqXHR, exception) {
        	    if (jqXHR.status === 0) {
        	        alert('Not connect.\n Verify Network.');
        	    } else if (jqXHR.status == 404) {
        	        alert('Requested page not found. [404]');
        	    } else if (jqXHR.status == 500) {
        	        alert('Internal Server Error [500].');
        	    } else if (exception === 'parsererror') {
        	        alert('Requested JSON parse failed.');
        	    } else if (exception === 'timeout') {
        	        alert('Time out error.');
        	    } else if (exception === 'abort') {
        	        alert('Ajax request aborted.');
        	    } else {
        	        alert('Uncaught Error.\n' + jqXHR.responseText);
        	    }
        	});
		
		return true;
	});
});
