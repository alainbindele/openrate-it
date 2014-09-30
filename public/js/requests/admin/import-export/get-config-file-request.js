
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


/*
 * url  : url of the remote resource 
 * tagID: id of the div where to put config file textual infos
 */
function getFileLoadedRequest(url,tagID){
	
	var the_data="";
	if(url=="/admin/get-script-config-file/")
		the_data = $('#scriptsLoaded').val();
	else if(url=="/admin/get-votes-config-file/")
		the_data = $('#votesLoaded').val();
	var url = url+the_data;
	//cleaning actual content
	$("#"+tagID).html("");
	if($('#frame_files-preview')){
		$('#frame_files-preview').remove();
		$('#EditAreaArroundInfos_files-preview').remove();
	}
	//replace the text area with returned content
	$('#'+tagID).load(url,function(){
		//call script to visualize highlighting
		editAreaLoader.init({
	    	id : "files-preview"		// textarea id
	    	,syntax: "php"			// syntax to be uses for highgliting
	    	,start_highlight: true		// to display with highlight mode on start-up
	    	,min_width:800
	    	,min_height:400
	    	,allow_resize:"both"
	    });
	});
}
