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
 * Retrieve the circles from database for the user specified
 * @param url
 * @param tagID
 * @param selectId
 */
function getCirclesRequest(url, tagID,userId){
	var url = url+'/'+$("#"+userId).val();;
	$.get(url,function(response){
		var i=0;
		list=$.parseJSON(response);
		var html = '<div style="overflow: scroll;background: #eeeeee;border-radius: 5px;border:20px; margin: 20px;padding: 20px">';
		$.each(list,function( index,circle ) {
			html += '&nbsp <label> '+circle+' &nbsp </label>';
			html += "<input type='checkbox' name='circles' id='circles"+i+"' value="+circle+">";
			if(i%2==0 && i>0) html+='<br>';
			i+=1;
		});
		html += '</div>';
		$("#"+tagID).html(html);
	});
}


