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

/**
 * Get the survey in JSON format and put it in the DIV tagID
 * @param url
 * @param tagID
 * @param tagId2
 */
function getSurveyRequest(tagID,tagId2){
	var surveyId = parseInt($('#surveySelectId').val());
	if(!isNaN(surveyId)){
		itemIndex++;
		var url="/surveys/get-survey/"+surveyId;
		$.get(url,function(survey){
			$("#tagID").html("");
			$("#tagId2").html("");
			$.each($.parseJSON(survey), function( index, value ) {
				if(typeof (value)=='string'||typeof (value)=='boolean'||typeof (value)=='int')
					$("#tagID").append("<div style:'background-color:rgb(240, 240, 240);'><label style='text-align: left' class='pure-input-1-2'><b>"+index+"</b>&nbsp :&nbsp "+value+"</label></div>");
				else
					parseSurvey(value,tagId2,1,surveyId);
			});
		});
	}
}

var popupSurveyId;
var popupItemName;

function parseSurvey(surveyJson,tagId2,rate,surveyId){
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
		$(tagId2).append("<h6>"+title+" (Type:"+type+")</h6>");


		if( type ==  'single'  )    comboType="radio";
		if( type ==  'multi'   )    comboType="checkbox";
		if( type ==  'likert'  )    tmp+='<select name="units['+name+'][0]" id="backing2b">';
		if( type ==  'thumb'   )    comboType="radio";
		if( type ==  'shultze' )	{
			if(rate)
				tmp+='<ul name="unit'+h+'" id="sortable'+h+'">';
			else
				tmp+='<ul>';
		} 
		$.each(value['items'], function ( index2, value2 ) {

			if( type ==  'shultze' ){
				tmp+='<li name="'+name+'" id='+value2['USID']+' class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'+value2['label'].toUppercase()+"(votes:"+value2['votes']+")"+'</li>';
				flag=1;
			}

			if( type == 'likert' ){
				tmp+='<option id="'+name+'-'+i+'" name="units['+name+']['+i+']" value="'+value2['USID']+'">'+value2['label'].toUppercase()+"(votes:"+value2['votes']+")"+'</option>';
				flag=1;
			}	
			if( type ==  'thumb' ){
				tmp+="<label>"+value2['label'].toUppercase()+"(votes:"+value2['votes']+")"+"</label>&nbsp";
				tmp+="<input type='"+comboType+"' id='"+name+"-"+i+"' name='units["+name+"]["+i+"]' value='"+value2['USID']+"' >&nbsp";
				tmp+="<br>";
			}   

			else if(flag=='0'){ // single or multi
				tmp+="<label>"+value2['label'].toUppercase()+"(votes:"+value2['votes']+")"+"</label>&nbsp";
				tmp+="<input type='"+comboType+"' id='"+name+"-"+i+"' name='units["+name+"]["+i+"]' value='"+value2['USID']+"' >&nbsp";
				tmp+="<br>";
			}
			if(type!='single'&&type!='thumb'){i++;j++;}
		});

		if( type ==  'shultze' ){
			popupSurveyId=surveyId;
			popupItemName=name;
			tmp+='</ul>';
			h++;
		}
		if( type == 'likert' ){
			tmp+='</select>\
				<div class="rateit" data-rateit-backingfld="#backing2"></div>';
		}
		tmp="<div style='padding:5px;'>"+tmp+"</div>";
		$(tagId2).append(tmp);
		i=0;
	});


	var sortableIndex=0;
	$(function() {
		$.each($("#surveyInputDiv ul"),function(){
			$( "#sortable"+sortableIndex ).sortable();
			$( "#sortable"+sortableIndex ).disableSelection();
			sortableIndex++;
		});
	});
	//$(function () { $('#backing2b').rateit(); });
}


function removeItem(id){
    $("#"+id).parent().parent().empty();
}

function removeUnit(id){
    $("#unit"+id).remove();
    if(id<($("#units").children().size())){ //if I'm not deleting the last unit
        normalizeUnits(parseInt(id)+1);
    }
    unitIndex--;
}

function normalizeUnits(id){
    index=id;
    $("#units").children().each(function(){
        operatingUnitNumber=$(this).attr('id').replace('unit','');
        if(operatingUnitNumber>=id){

            $(this).attr("id","unit"+(operatingUnitNumber-1));
            $("#units\\[unit"+operatingUnitNumber+"\\]").attr("id","units[unit"+(operatingUnitNumber-1)+"]");
            $("#btnCloseUnit-"+operatingUnitNumber) .attr('onclick',"removeUnit("+(operatingUnitNumber-1)+")")
                                                    .attr('id',"btnCloseUnit-"+(operatingUnitNumber-1));
            $("#unit-unit-"+operatingUnitNumber).attr("placeholder","unit "+(operatingUnitNumber-1))
                                                .attr("name","units[unit"+(operatingUnitNumber-1)+"][title] ")
                                                .attr("id","unit-unit-"+(operatingUnitNumber-1));
            var itemN=1;
            $(this).find("input").each(function(){
                if($(this).attr("type")=="hidden"){
                    $(this).attr("name","units[unit"+(operatingUnitNumber-1)+"][type]");
                }
                else if($(this).attr("placeholder")=="unit "+(operatingUnitNumber-1)){
                    $(this).attr("id","unit-unit-"+(operatingUnitNumber-1));
                    $(this).attr("name","units[unit"+(operatingUnitNumber-1)+"][title]");
                }
                else{
                    alert("turning:"+$(this).attr("name")+" in: units[unit"+(operatingUnitNumber-1)+"][items][item"+itemN+"]");

                    $(this).attr("name","units[unit"+(operatingUnitNumber-1)+"][items][item"+itemN+"]");
                    itemN++;
                }
            });


            $(this).find(".input-group span button").each(function(){
                i=0;
               // alert($(this).attr("onclick"))
                if($(this).attr("onclick").search("removeUnit")>=0){
                    $(this).attr("onclick","removeUnit("+(operatingUnitNumber-1)+")")
                        .attr("id","btnCloseUnit-"+(operatingUnitNumber-1));
                }
                else{

                   $(this).attr("onclick","removeItem(btnCloseItem-"+(operatingUnitNumber-1)+"-"+i+");")
                          .attr("id","btnCloseItem-"+(operatingUnitNumber-1)+"-"+i);
                    i+=1;
                }
            });
            /*$(this).child(".input-group").children().each(function(){
                //$("btnCloseItem-"+operatingUnitNumber+"-1").attr("id","btnCloseItem-"+(operatingUnitNumber-1)+"-1");
                alert($(this).content());
            });*/




        }
    });
}

function getSurveysIdListRequest(tagID){
    var url = "/surveys/get-Surveys-Id-List";
	var userId = parseInt($('#surveyCreatorId').val());
	if(!isNaN(userId)){
		var completeUrl = url+"/"+userId;
		$.get(completeUrl,function(surveyIds){
			
			var options = $(tagID);
			var parent = $(tagID).parent();  
			
			$(tagID).html("<option value=''>Select Survey</option>");
			$.each(surveyIds, function( index, value ) {
				options.append("<option value="+value+">"+index+" - "+value+"</option>");
			});
			//workaround to set again searchable property since
			//recalling the function it increase the size of the menu
			style = options.attr("style");
			//options.searchable();
			options.attr("style",style);
		});
	}else{
		var parent = $(tagID).parent();
		
		$(tagID).html("<option value='none'>Select Survey</option>");

	}
}
