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

(function($){
    $.fn.serializeObject = function(){

        var self = this,
            json = {},
            push_counters = {},
            patterns = {
                "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                "key":      /[a-zA-Z0-9_]+|(?=\[\])/g,
                "push":     /^$/,
                "fixed":    /^\d+$/,
                "named":    /^[a-zA-Z0-9_]+$/
            };


        this.build = function(base, key, value){
            base[key] = value;
            return base;
        };

        this.push_counter = function(key){
            if(push_counters[key] === undefined){
                push_counters[key] = 0;
            }
            return push_counters[key]++;
        };

        $.each($(this).serializeArray(), function(){

            // skip invalid keys
            if(!patterns.validate.test(this.name)){
                return;
            }

            var k,
                keys = this.name.match(patterns.key),
                merge = this.value,
                reverse_key = this.name;

            while((k = keys.pop()) !== undefined){

                // adjust reverse_key
                reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');

                // push
                if(k.match(patterns.push)){
                    merge = self.build([], self.push_counter(reverse_key), merge);
                }

                // fixed
                else if(k.match(patterns.fixed)){
                    merge = self.build([], k, merge);
                }

                // named
                else if(k.match(patterns.named)){
                    merge = self.build({}, k, merge);
                }
            }

            json = $.extend(true, json, merge);
        });

        return json;
    };
})(jQuery);


jQuery(document).on('ready', function() {
		jQuery('form#addContainersForm').bind('submit', function(event){
		event.preventDefault();
		
		var response;
		var form = this;
        var jsonRequest = {};

        var circles=[];

        function a(form){
        	if(typeof(form.circles.length)!='undefined'){
	            for (var i = 0; i < form.circles.length; i++) {
	                if(form.circles[i].checked)
	                    circles.push(form.circles[i].value);
	            }return circles;
        	}else{
                if(form.circles.checked)
                        circles.push(form.circles.value);
                return circles;
        	}
        }

        jsonRequest =$('form#addContainersForm').serializeObject();
        jsonRequest.phpsessid = $.cookie('PHPSESSID');
        jsonRequest.circles	=	a(form);


		alert("JSON="+JSON.stringify(jsonRequest));
		$(".controllerdiv1").append("<br>JSON="+JSON.stringify(jsonRequest,null, 2)+"<br>");
        
        $.ajax({
            type: "POST",
            url: "/surveys/add-container",
            data: JSON.stringify(jsonRequest),
            contentType: "application/json; charset=utf-8",
            dataType: "json"
        }).done(function(text) { 
	    		response="success";
	    		//alert(response+":"+JSON.stringify(text));
	    		$(".controllerdiv1").append(JSON.stringify(text));
	    		var dialogContent=	'<div id="addContainer-dialog-confirm" title="Action Confirm required" style="display: none;">Would you reload the page?</div>';
	    		$("#addContainerDialogConfirmDiv").html(dialogContent);
	    		showDialog("addContainer");
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
