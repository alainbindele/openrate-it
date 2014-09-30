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


jQuery(document).on('ready', function() {
	jQuery('#addCommentForm').bind('submit', function(event){
		event.preventDefault();
		var form = this;
		var jsonRequest={};
		
		jsonRequest.userId		=	form.commentUserId.value;
		jsonRequest.surveyId	=	form.commentSurveyId.value;
		jsonRequest.comment		=	form.commentId.value;
		jsonRequest.PHPSESSID 	= 	$.cookie('PHPSESSID');
        
        $.ajax({
            type: "POST",
            url: "/surveys/add-comment-to-survey",
            data: JSON.stringify(jsonRequest),
            contentType: "application/json; charset=utf-8",
            dataType: "json"
        }).done(function(text) {
        	$("#alert").append("Your Message has been sent!<br><pre>"+JSON.stringify(text,undefined,2)+"</pre>").show();
        }).fail(function (jqXHR, exception) {
            if (jqXHR.status === 0) {
            	$("#alert-warning").append('Not connect.\n Verify Network.').show();
            } else if (jqXHR.status == 404) {
            	$("#alert-warning").append('Requested page not found. [404]').show();
            } else if (jqXHR.status == 500) {
            	$("#alert-warning").append('Internal Server Error [500].').show();
            }else if (jqXHR.status == 405) {
             	$("#alert-warning").append('Method Not Allowed [405].').show();
            } else if (exception === 'parsererror') {
            	$("#alert-warning").append('Requested JSON parse failed.').show();
            } else if (exception === 'timeout') {
            	$("#alert-warning").append('Time out error.').show();
            } else if (exception === 'abort') {
            	$("#alert-warning").append('Ajax request aborted.').show();
            } else {
            	$("#alert-warning").append('Uncaught Error.\n' + jqXHR.responseText).show();
            }
        });
		
		return true;
	});
});
