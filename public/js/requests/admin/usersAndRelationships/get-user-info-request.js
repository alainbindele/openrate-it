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
 * Returns the user info and put them in divInfoContainer
 * @param userLabel
 * @param divInfoContainer
 * @param divInfoContainerToClean
 */
function getUserInfo(userLabel,divInfoContainer,divInfoContainerToClean){
	id=userLabel.split(":");
	var userID = parseInt(id[2]);
	if(!isNaN(userID)){
		$(surveyGraph).empty();
		// create pairs index=value with data that must be sent to server
		var url='/users/get-user/'+userID;
		$.get(url,function(user){			
			$(divInfoContainer).empty();
			$(divInfoContainerToClean).empty();
			$.each($.parseJSON(user), function( index, value ) {
				$(divInfoContainer).append(	"<div style:'background-color:rgb(240, 240, 240);'>" +
											"<label style='text-align: left' class='pure-input-1-2'>" +
											"<b>"+index+"</b>" +
											"&nbsp :&nbsp "+value+"</label>" +
											"</div>");
			});
		});
	}
}


