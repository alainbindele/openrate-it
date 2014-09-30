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
 * Created with JetBrains PhpStorm.
 * User: h4p0
 * Date: 22/07/13
 * Time: 22:46
 * To change this template use File | Settings | File Templates.
 */



function getNodeInfoRequest(url,tagID,nodeId){



	var the_data = nodeId;
	var request =  get_XmlHttp();
	//alert(tagID);
	itemIndex++;
	var url = url+"/"+the_data;
	request.open("GET", url, true); // sets the request
	// adds a header to tell the PHP script to recognize the data as is sent via POST
	request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	request.send();		// sends the request
	// Check request status
	// If the response is received completely, will be transferred to the HTML tag with tagID
	request.onreadystatechange = function() {
		if (request.readyState == 4) {
			var list={};
			list=request.responseText;
			list= $.parseJSON(request.responseText);
			var options = $("#"+tagID);

			options.empty();
			options.append("<option value=''>Select Container</option>");
			if(list.length!='undefined'){
				for(var i=0;i<list.length;i++){
					options.append("<option value="+list[i]+">"+list[i]+"</option>");

				}
			}
		}
	};
}





function drawGraph(){
	var width = 650,
	height = 450;
	var color = d3.scale.category20();
	var force = d3.layout.force()
	.charge(-100)
	.linkDistance(400)
	.size([width, height]);
	$("#graph > svg").remove();
	var svg = d3.select("#graph").append("svg")
	.attr("width", width)
	.attr("height", height)
	.attr("pointer-events", "all")
	.append('svg:g')
	.call(d3.behavior.zoom().on("zoom", redraw))
	.append('svg:g');

    /** FISHEYE EFFECT (for now disabled)
	var fisheye = d3.fisheye.circular()
	.radius(200)
	.distortion(2);
     */
	fill = d3.scale.category20();
	svg.append('svg:rect')
	.attr('width', width)
	.attr('height', height)
	.attr('fill', 'white');
	function redraw() {
		//console.log("here", d3.event.translate, d3.event.scale);
		svg.attr("transform",
				"translate(" + d3.event.translate + ")"
				+ " scale(" + d3.event.scale + ")");
	}

	d3.json("/admin/get-Graph-Json", function(error,graph) {

		force
		.nodes(graph.nodes)
		.links(graph.links);
		var linkedByIndex = {};
		graph.links.forEach(function(d) {
			linkedByIndex[d.source.index + "," + d.target.index] = 1;
		});
		function isConnected(a, b) {
			return linkedByIndex[a.index + "," + b.index] || linkedByIndex[b.index + "," + a.index] || a.index == b.index;
		}
		var link = svg.selectAll(".link")
		.data(graph.links)
		.enter().append("line")
		.attr("class", "link")
        .style("stroke-opacity",1)
            .style("stroke","rgb(0, 0, 0)")
		.style("stroke-width", function(d) {
			return 0.5; 
		});
		var node = svg.selectAll(".node")
		.data(graph.nodes)
		.enter().append("circle")
		.attr("class", "node")
		.attr("id", function(d) { return "n-"+d.name; })  // Useful for the LISTBOX
		.on("mouseover", fade(4))
		.on("mouseout", fade(0.3))
		.style("fill", function(d) {
			return fill(d.group);
		})
		.style("stroke", function(d) {
			return d3.rgb(fill(d.group)).darker();})
			.attr("r", function(d){
				if(d.nof<100)
					return(d.width+(0.01*d.nof));
				if(d.totVotes<100)
					return(d.width+(0.01*d.totVotes));
				if(d.weight)
					return(d.width+(0.1*d.weight));
				else
					return (d.width);})
					.style("fill", function(d) { return color(d.group); })
					.call(force.drag)
					;

		var info = svg.selectAll(".info")
		.data(graph.infos);

		function fade(opacity) {
			return function(d) {
				node.style("stroke-opacity", function(o) {
					thisOpacity = isConnected(d, o) ? 1 : opacity;
					this.setAttribute('fill-opacity', thisOpacity);
					return thisOpacity;
				});

				// link label
				edgelabels.style('opacity',function(o){
					return o.source === d || o.target === d ? 1 : 0;
				});
				link.style("stroke-opacity", opacity).style("stroke-opacity", function(o) {
					return o.source === d || o.target === d ? 1 : opacity;
				}).style("stroke-width", opacity).style("stroke-width", function(o) {
					return o.source === d || o.target === d ? 1.5 : 0.5;
				}).style("stroke",opacity).style("stroke", function(o) {
					return o.source === d || o.target === d ? d3.rgb(fill()).darker(5) : d3.rgb(fill()); });
			};
		}

		node.append("title")
		.text(function(d) { return d.name; });


		var edgepaths = svg.selectAll(".edgepath")
		.data(graph.links)
		.enter()
		.append('path')
		.attr({'d': function(d) {
			if(d.source.x=='undefined'){
				return 0;
			}else{
				var path= 'M '+d.source+' '+d.source+' L '+ d.target +' '+d.target;
			}
			return path;
		},
		'class':'edgepath',
		'fill-opacity':0,
		'stroke-opacity':0,
		'fill':'blue',
		'stroke':'red',
		'id':function(d,i) {return 'edgepath'+i;}})
		.style("pointer-events", "none");

		var edgelabels = svg.selectAll(".edgelabel")
		.data(graph.links)
		.enter()
		.append('text')
		.style("pointer-events", "none")
		.style('opacity',0)
		.attr({'class':'edgelabel',
			'id':function(d,i){return 'edgelabel'+i;},
			'dx':80,
			'dy':0,
			'font-size':10,
			'fill':'#aaa'});

		edgelabels.append('textPath')
		.attr('xlink:href',function(d,i) {return '#edgepath'+i;})
		.style("pointer-events", "none")
		.text(function(d,i){return d.text;});    


		drawPieGraph(graph.infos[0],graph.infos[1],graph.infos[2],graph.infos[3]);
		force.start();
		force.on("tick", function() {

			edgepaths.attr('d', function(d) { 
				var path='M '+d.source.x+' '+d.source.y+' L '+ d.target.x +' '+d.target.y;
				//console.log(d);
				return path;

			});       

			edgelabels.attr('transform',function(d,i){
				if (d.target.x<d.source.x){
					bbox = this.getBBox();
					rx = bbox.x+bbox.width/2;
					ry = bbox.y+bbox.height/2;
					return 'rotate(180 '+rx+' '+ry+')';
				}
				else {
					return 'rotate(0)';
				}
			});

			link.attr("x1", function(d) { return d.source.x; })
			.attr("y1", function(d) { return d.source.y; })
			.attr("x2", function(d) { return d.target.x; })
			.attr("y2", function(d) { return d.target.y; });

			node.attr("cx", function(d) { return d.x; })
			.attr("cy", function(d) { return d.y; });
		});

		node.on("click", function(d) {
			if(d.group==1){ //USER INFO
				$('#statisticsTabLink2').trigger('click');
				drawBarUsers(d.group,d.name,d.nof,d.noc,d.nos,d.niv,d.nov);
				getUserInfo(d.name,infoContainer1,infoContainer2);
			}
			if(d.group==2){ // SURVEY INFO
				$('#statisticsTabLink2').trigger('click');
				//alert("name:"+d.name+",hits:"+d.hits+",totVotes:"+d.totVotes);
				drawBarSurveys(d.group,d.name,d.hits,d.totVotes);
				getSurveyInfo(d.name,infoContainer1,infoContainer2);
			}
			if(d.group==3){ //CONTAINERS INFO
				$('#statisticsTabLink2').trigger('click');
				$('#surveyGraph').innerHTML='';
				//drawPieGraph(graph.infos[0],graph.infos[1],graph.infos[2],graph.infos[3]);
			}

		});
		node.on("mouseover", function(d) {

		});
        /*
		if(node[0].length<100){
			svg.on("mousemove", function() {
				fisheye.focus(d3.mouse(this));

				node.each(function(d) { d.fisheye = fisheye(d); })
				.attr("cx", function(d) { return d.fisheye.x; })
				.attr("cy", function(d) { return d.fisheye.y; })
				.attr("r", function(d) { return d.fisheye.z * 4.5; });

				link.attr("x1", function(d) { return d.source.fisheye.x; })
				.attr("y1", function(d) { return d.source.fisheye.y; })
				.attr("x2", function(d) { return d.target.fisheye.x; })
				.attr("y2", function(d) { return d.target.fisheye.y; });
			});
		}*/
		i=0;
		$.each($('[id^="n-"]'), function() {
			$("#nodeSearchSelect").append($("<option />").val(i).text(this.id).attr('id',this.id));
			i+=1;
		});

	});
}