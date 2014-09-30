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


 $(function() {
        	  $("#upload-scripts-button").click( function(){
            		
                  var content = '<div id="upload-scripts-dialog-confirm" title="Action Confirm required">\
                  	<h4>Load Script File in PHP format</h4>\
                      <form enctype="multipart/form-data" action="/admin/upload-scripts" method="POST">\
                          <input type="hidden" name="MAX_FILE_SIZE" value="30000" />\
                          <input name="userfile" type="file" value="Select"/><br>\
                          <input type="submit" value="Send" />\
                      </form>\
                      </div>';
                  $("#uploadScriptsDiv").html(content);
                  showDialog("upload-scripts");
              });
        	$("#upload-users-button").click( function(){
        		
                var content = '<div id="upload-users-dialog-confirm" title="Action Confirm required">\
                	<h4>Load Users File in JSON format</h4>\
                    <form enctype="multipart/form-data" action="/admin/upload-users" method="POST">\
                        <input type="hidden" name="MAX_FILE_SIZE" value="30000" />\
                        <input name="userfile" type="file" value="Select"/><br>\
                        <input type="submit" value="Send" />\
                    </form>\
                    </div>';
                $("#uploadUsersDiv").html(content);
                showDialog("upload-users");
            });
            
            $("#upload-surveys-button").click( function(){
                var content = '<div id="upload-surveys-dialog-confirm" title="Action Confirm required">\
                	<h4>Load Surveys File in JSON format</h3>\
                    <form enctype="multipart/form-data" action="/admin/upload-surveys" method="POST">\
                        <input type="hidden" name="MAX_FILE_SIZE" value="30000" />\
                        <input name="userfile" type="file" value="Select"/><br>\
                        <input type="submit" value="Send" />\
                    </form>\
                    </div>';
                $("#uploadSurveysDiv").html(content);
                showDialog("upload-surveys");
                        });
            $("#upload-votes-button").click( function(){
                var content = '<div id="upload-votes-dialog-confirm" title="Action Confirm required">\
                    <h4>Load Votes File in JSON format</h3>\
                    <form enctype="multipart/form-data" action="/admin/upload-votes" method="POST">\
                        <input type="hidden" name="MAX_FILE_SIZE" value="30000" />\
                        <input name="userfile" type="file" value="Select"/><br>\
                        <input type="submit" value="Send" />\
                    </form>\
                    </div>';
                $("#uploadVotesDiv").html(content);
                showDialog("upload-votes");
            });
          });