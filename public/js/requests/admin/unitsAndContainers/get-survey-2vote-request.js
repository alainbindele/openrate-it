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

var unitIndex = -1;
var old="";
var placeholder="hr";
var itemIndex=0;

function getSurvey2VoteRequest(tagID,tagID2){
	var surveyId = parseInt($('#surveySelectId').val());
	if(!isNaN(surveyId)){
		var the_data=surveyId;
		var url="/surveys/get-survey/"+the_data;
		$.get(url,function(survey){
			$(tagID).html('');
			$(tagID2).html('');
			$.each(survey, function( index, value ) {
				if(typeof (value)=='string'||typeof (value)=='boolean'||typeof (value)=='number')
					$(tagID).append("<div style:'background-color:rgb(240, 240, 240);'><label style='text-align: left' class='pure-input-1-2'><b>"+index+"</b>&nbsp :&nbsp "+value+"</label></div>");
				if(index=='units'){
					parse2VoteSurvey(value,tagID2,1);
				}
                if(index=='tags'){
                    $(tagID).append("<div"+
                        "style:'background-color:rgb(240, 240, 240)'>"+
                        "<label><b>Tags:</b></label>"+
                        "<ul style='text-align: left' class='pure-input-1-2'></ul></div>");
                    $.each(value,function(index,value){
                        $(tagID).find("ul").append("<li>"+value+"</li>");
                    });
                };
				if(index=='circles'){
					$(tagID).append("<div"+
							"style:'background-color:rgb(240, 240, 240)'>"+
							"<label><b>Circles:</b></label>"+
					"<ul style='text-align: left' class='pure-input-1-2'></ul></div>");
					$.each(value,function(index,value){
						$(tagID).find("ul").eq(1).append("<li>"+value+"</li>");
					});
				};

			});
		});
	}
}

/**
 * Returns the IDS of users authorized to vote for a specific Survey
 * and stores the list in the div pointed by tagID
 * @param tagID
 */
function getAuthorizedVotersList(tagID){
	var surveyId = parseInt($('#surveySelectId').val());
	if(!isNaN(surveyId)){
		var url = "/surveys/get-authorized-voters-list/"+surveyId;
		$.get(url,function(response){
			list= $.parseJSON(response);
			var options = $(tagID);
			options.empty();
			options.append("<option value=''>Select Voter</option>");
			$.each(list, function( index,value ) {
				options.append("<option value="+value+">"+index+" - "+value+"</option>");
			});
		});
	}
}


function parse2VoteSurvey(surveyJson,tagID2,rate){
	var h=0;
	$.each(surveyJson, function ( index, value ) {
		var i='0';
		var j='1';
		var type    =   value['type'];
		var title   =   value['title'];
		var name    =   index;
		var comboType='';
		var tmp="";
		var flag=0;
		$(tagID2).append("<h6>"+title+" (Type:"+type+")</h6>");


		if( type ==  'single'  )    comboType="radio";
		if( type ==  'multi'   )    comboType="checkbox";
		if( type ==  'likert'  )    tmp+='<select name="units['+name+'][0]" id="backing2b">';
		if( type ==  'thumb'   )    comboType="radio";
		if( type ==  'shultze' )	{
			if(rate)
				tmp+='<ul name="unit'+h+'" id="sortable'+h+'" class="ui-sortable">';
			else
				tmp+='<ul>';
		} 
		$.each(value['items'], function ( index2, value2 ) {

			if( type ==  'shultze' ){
				tmp+='<li name="'+name+'" id='+value2['USID']+' class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'+value2['label']+"(votes:"+value2['votes']+")"+'</li>';
				flag=1;
			}

			if( type == 'likert' ){
				tmp+='<option id="'+name+'-'+i+'" name="units['+name+']['+i+']" value="'+value2['USID']+'">'+value2['label']+"(votes:"+value2['votes']+")"+'</option>';
				flag=1;
			}	
			if( type ==  'thumb' ){
				tmp+="<label>"+value2['label']+"(votes:"+value2['votes']+")"+"</label>&nbsp";
				tmp+="<input type='"+comboType+"' id='"+name+"-"+i+"' name='units["+name+"]["+i+"]' value='"+value2['USID']+"' >&nbsp";
				tmp+="<br>";
			}   

			else if(flag=='0'){ // single or multi
				tmp+="<label>"+value2['label']+"(votes:"+value2['votes']+")"+"</label>&nbsp";
				tmp+="<input type='"+comboType+"' id='"+name+"-"+i+"' name='units["+name+"]["+i+"]' value='"+value2['USID']+"' >&nbsp";
				tmp+="<br>";
			}
			if(type!='single'&&type!='thumb'){i++;j++;}
		});

		if( type ==  'shultze' ){
			tmp+='</ul>';
			h++;
		}
		if( type == 'likert' ){
			tmp+='</select>\
				<div class="rateit" data-rateit-backingfld="#backing2"></div>';
		}
		tmp="<div style='padding:5px;'>"+tmp+"</div>";
		$(tagID2).append(tmp);
		i=0;
	});

	var sortableIndex=0;
		$.each($("#surveyInputDiv ul"),function(){
			$( "#sortable"+sortableIndex ).sortable().disableSelection();
			sortableIndex++;
		});
	// likert function to display stars...doesn't work properly yet :(
	// $(function () { $('#backing2b').rateit(); });
}



