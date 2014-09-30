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
var surveyID='';
var items=[];



function plotSurveyData(dataXX,divID,items){

	var chart = new Highcharts.Chart({
		chart: {
			renderTo: 'surveyGraph',
			type: 'column',
			margin: 75,
			options3d: {
				enabled: true,
				alpha: 15,
				beta: 15,
				depth: 50,
				viewDistance: 25
			}
		},
		//alert(dataXX);
		plotOptions: {
			column: {
				depth: 25
			}
		},

		title: {
			text: 'Statistics of survey'
		},
		subtitle: {
			text: 'Number of votes '
		},
		xAxis: {
			categories: items,
			title: {
				text: 'Items'
			}
		},
		yAxis: {
			min: 0,
			title: {
				text: 'Votes',
				align: 'high'
			},
			labels: {
				overflow: 'justify'
			}
		},
		tooltip: {
			valueSuffix: ' votes'
		},
		plotOptions: {				
			bar: {
				dataLabels: {
					enabled: true
				}
			}
		},
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'top',
			x: -20,
			y: 50,
			floating: true,
			borderWidth: 1,
			backgroundColor: '#FFFFFF',
			shadow: true
		},
		credits: {
			enabled: false
		},
		series: dataXX
	});
	// Activate the sliders
	$('#R0').on('change', function(){
		chart.options.chart.options3d.alpha = this.value;
		showValues();
		chart.redraw(false);
	});
	$('#R1').on('change', function(){
		chart.options.chart.options3d.beta = this.value;
		showValues();
		chart.redraw(false);
	});

	function showValues() {
		$('#R0-value').html(chart.options.chart.options3d.alpha);
		$('#R1-value').html(chart.options.chart.options3d.beta);
	};
	showValues();
}

/*
 * surveyID : id of survey 
 * tagID    : id of the div where to putgeneric survey textual info
 * tagID	: id of the div where to put units/items info
 */
function getSurveyInfo(surveyID,tagID,tagID2,tagID3){
	id=surveyID.split(":");
	surveyID = parseInt(id[1]);
	if(!isNaN(surveyID)){
		var url="/surveys/get-survey/"+surveyID;
		$.get(url,function(survey){
			$(tagID).html('');
			$(tagID2).html('');

			$.each(survey, function( index, value ) {
				if(typeof (value)=='string'||typeof (value)=='boolean'||typeof (value)=='number')
					$(tagID).append("<div style:'background-color:rgb(240, 240, 240);'><label style='text-align: left' class='pure-input-1-2'><b>"+index+"</b>&nbsp :&nbsp "+value+"</label></div>");
				if(index=='units'){
					parseSurvey(value,tagID2,0,surveyID);
					plotSurvey(value,tagID3);
				}
				if(index=='circles'){
					$(tagID).append("<div"+
							"style:'background-color:rgb(240, 240, 240)'>"+
							"<label><b>Circles:</b></label>"+
					"<ul style='text-align: left' class='pure-input-1-2'></ul></div>");
					$.each(value,function(index,value){
						$(tagID).find("ul").append("<li>"+value+"</li>");
					});
				};
			});
		});
	}
}


function plotSurvey(surveyJson,tagID){
	var data=[];
	var itemN=1;
	var maxItemNum=1;
	$.each(surveyJson, function ( unitName, unit ) {
		var type    =   unit['type'];
		var item    = {};
		item['name']=unit['title'];
		item['data']=[];
		itemN=1;
		$.each(unit['items'], function ( index2, value2 ) {
			tmp=[];
			tmp=[value2['label'],parseInt(value2['votes'])];
			item['data'].push(tmp);
			itemN+=1;
		});
		//alert(itemN);
		if(itemN>maxItemNum)maxItemNum=itemN;
		data.push(item);

	});
	//
	for(var i=0;i<=maxItemNum;i++) items[i]='item'+i;
	if(data.length>=1){
		plotSurveyData(data,'surveyGraph',items);
	}


}