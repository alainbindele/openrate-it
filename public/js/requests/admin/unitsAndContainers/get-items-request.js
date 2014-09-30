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
var pollType="hr";
var itemIndex=0;
var thumbselected=0;
function printItem(tagID,pollType,unitIndex,itemIndex,placeholder){
    if(pollType=='single'){
        $(tagID).append("<input style='background-color : #CED8F6;' name='units[unit"+unitIndex+"][items][item"+itemIndex+"]'\
				id='item-"+placeholder+"' placeholder='"+placeholder+"' class='pure-input-1'>");
    }
    if(pollType=='multi'){
        $(tagID).append("<input style='background-color : #CECEF6;' name='units[unit"+unitIndex+"][items][item"+itemIndex+"]'\
				id='item-"+placeholder+"'\
				placeholder='"+placeholder+"' class='pure-input-1'>");
    }
    if(pollType=='shultze'){
        $(tagID).append("<input style='background-color : #E3CEF6;' name='units[unit"+unitIndex+"][items][item"+itemIndex+"]'\
				id='item-"+placeholder+"'\
				placeholder='"+placeholder+"' class='pure-input-1'>");
    }
    if(pollType=='likert'){
        $(tagID).append("<input style='background-color : #F2E0F7;' name='units[unit"+unitIndex+"][items][item"+itemIndex+"]'\
				id='item-"+placeholder+"'\
				placeholder='"+placeholder+"' class='pure-input-1'>");
    }
    if(pollType=='thumb' && thumbselected==0){
        $(tagID).append("<input readonly value='thumb' style='background-color : #F6CEEC;' name='units[unit"+unitIndex+"][items][item"+itemIndex+"]'\
				id='item-"+placeholder+"'\
				placeholder='"+placeholder+"' class='pure-input-1'>");
        thumbselected=1;
    }

    if(pollType=='unit'){
        $(tagID).append("<hr>");
        $(tagID).append("<input style='background-color : #EFF5FB;' name='units[unit"+unitIndex+"][title]'\
				id='unit-"+placeholder+"'\
				placeholder='unit "+unitIndex+"' class='pure-input-1'>");
        thumbselected=0;
    }


}

function retriveItemsRequest(tagID,pollType){
    tagID="#"+tagID;
    if(pollType=="unit"){
        if(old=='hr'){
            alert("Could not create empty units!");
            return;
        }
        unitIndex++;
        placeholder=pollType+"-"+itemIndex;
        //if(pollType!='thumb' && thumbselected == 0 )
        printItem(tagID,pollType,unitIndex,itemIndex,placeholder);
        old="hr";
        itemIndex=0;
        return;
    }

    if(pollType!=old && (pollType!="hr" && old!="hr")){
        alert("Poll types must be grouped in a uniform way and separated by HR separator (+ button)");
        return;
    }
    old=pollType;


    if(itemIndex=='0'){//Is the first unit of type "pollType" (must add a hidden field with the type of poll)
        $(tagID).append( "<input name=units[unit"+unitIndex+"][type] type='hidden' value='"+pollType+"'>" );
    }
    itemIndex++;
    placeholder=pollType+"-"+itemIndex;
    printItem(tagID,pollType,unitIndex,itemIndex,placeholder);

};
