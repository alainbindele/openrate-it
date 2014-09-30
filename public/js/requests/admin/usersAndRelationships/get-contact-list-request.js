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
 * Creates a table containing the list of contacts 
 * @param list
 * @param del
 * @returns
 */
function parseList(list,del){
	html='<div><table style="margin-left:auto; margin-right:0px;" class="pure-table pure-table-horizontal">';
	html+=	'<tr >\
				<th>#id</th>\
				<th>Name</th>\
				<th>Surname</th>';
	if(del)
		html+=	'<th>Delete</th>';
			
	html+='</tr>';

		$.each(list,function( index, value ) {
			 html+="<tr><td>"+index+"</td>";
			$.each(value,function( name,surname) {
	            html+="<td>"+surname+"</td>";
	        });
			 if(del)
	            	html+="<td><input type='checkbox' value="+index+"></td>";
			html+="</tr>";
		});
			
	html+="</table></div>"
	return html;	
}

/**
 * Retrieve the contact list from database
 * @param url
 * @param tagID
 * @param id
 * @param del
 */
function getContactListRequest(url, tagID,id,del){
    // create pairs index=value with data that must be sent to server
    var userId = parseInt($("#"+id).val());
    var url = url+"/"+userId;
    $.get(url,function(response){
        $("#"+tagID).html(parseList(response,del));
    });
}


