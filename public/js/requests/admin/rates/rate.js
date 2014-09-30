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

/**
 * Created by h4p0 on 09/11/13.
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
    jQuery('form#ratesForm').bind('submit', function(event){
        event.preventDefault();
        var response;
        var form = this;
        var jsonRequest = {};
        var start=0;
        jsonRequest =$('form#ratesForm').serializeObject();
        jsonRequest.phpsessid = $.cookie('PHPSESSID');
        var array = jQuery.makeArray($("ul[id^='sortable']"));
        
        if ( typeof jsonRequest.units == "undefined" ) { //there are no <input> types
        	jsonRequest.units={};
        }
        
        if ( typeof array !== "undefined" && array.length != 0){ // there is some shultze unit!
	        for(var i=start; i< array.length; i++) {
	        	var array2 = array[i].childNodes;
	        	var name=array[i].childNodes[i].attributes[0].value;
	        	jsonRequest.units[name] = [];
	        	for(var j=0; j< array2.length; j++) {
	        		//jsonRequest.units[name][j] = [];
	        		var item=[];
	        		item.push(array2[j].attributes.id.nodeValue);
	        		jsonRequest.units[name].push(item);
	        	}
	        }
        }
        $.ajax({
            type: "POST",
            url: "/surveys/add-rate",
            data: JSON.stringify(jsonRequest),
            contentType: "application/json; charset=utf-8",
            dataType: "json"
        }).done(function(text) {
                $("#alert")
                .append("<i>Request:</i><br><pre><div style='overflow:auto;max-height:150px'>"+JSON.stringify(jsonRequest,undefined,2)+"</div></pre>")
                .append("<i>Response:</i><br><pre><div style='overflow:auto;max-height:150px'>"+JSON.stringify(text,undefined,2)+"</div></pre>").show();
        })
        .fail(function (jqXHR, exception) {
            if (jqXHR.status === 0) {
            	$(".alert-warning").append('Not connect.\n Verify Network.').show();
            } else if (jqXHR.status == 404) {
            	$(".alert-warning").append('Requested page not found. [404]').show();
            } else if (jqXHR.status == 405) {
            $("#alert-warning").append('Requested page not found. [406]').show();
            }else if (jqXHR.status == 500) {
            	$(".alert-warning").append('Internal Server Error [500].').show();
            } else if (exception === 'parsererror') {
            	$(".alert-warning").append('Requested JSON parse failed.').show();
            } else if (exception === 'timeout') {
            	$(".alert-warning").append('Time out error.').show();
            } else if (exception === 'abort') {
            	$(".alert-warning").append('Ajax request aborted.').show();
            } else {
            	$(".alert-warning").append('Uncaught Error.\n' + jqXHR.responseText).show();
            }
        });

        return true;
    });
});
