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

function getContainersIdListRequest(url,tagID,selectId){
	var userId = parseInt($("#"+selectId).val());
	if(typeof(userId)=='number'&&userId!=NaN){
		url=url+"/"+userId; // sets the request url
		$.get(url,function(response){
			$("#"+tagID).empty();
			$("#"+tagID).append("<option value=''>Select Container</option>");
			if(response.length!='undefined'){
				$.each(response,function(index,value)
						{
					$("#"+tagID).append("<option value="+value+">"+value+"-"+value+"</option>");
						});
			}
		});
	}
}