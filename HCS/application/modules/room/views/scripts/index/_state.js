/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/**
 * Functions to query the server performing long polling
 **/
var synrgic = synrgic || {};

synrgic.state = (function($){
    var version=0;
    
    function setlight(data){
    	var img;
    	var flipxtmp;
    	for(var key in data){    	    		
        	img = (data[key] == "off") ? "/images/room/Lights-off.png" : "/images/room/Lights-on.png";
        	flipxtmp="#"+key+"-img";     	
        	$(flipxtmp).attr("src", img);
        	flipxtmp="#"+key;
        	$(flipxtmp).val(data[key]);
        	$(flipxtmp).slider('refresh');
    	}    	
    }
    
    function setcurtains(data){
		var id;
		for(var key in data){ 
			id="#curtains"+key;
			$(id).text(data[key]);
		}  	
    }    

    function settv(data){ 	
    	$("#powState").text(data['state']);
    	$("#channel").text(data['channel']);
    	$("#volumeValue").text(data['volume']);
    	$("#playState").text(data['play']);
    } 
    
    function setradio(data){
        var position=(data["channel"]-89)*100/20;
    	$('#slider-1').val(position).slider("refresh");
    	$("#volumeValue").text(data['volume']);
    } 
    
    function settemp(data){
    		
	        $("#tmp-switch").val(data['state']);
	        $("#tmp-switch").slider('refresh');	
	        $("#osd").text(data['T']+'℃');
	    	$("#timer-1").val(data['hours1']);
	    	$("#timer-2").val(data['hours2']);
	        $("#Fan-swtich").val(data['fan']); 
	        $("#Fan-swtich").selectmenu("refresh");
	        
    } 
    
    function _handleUpdate(data)
    { 	
    	version=data['newVersion'];
    	
    	for(var i=0;i<8;i++)
    	{
    		var preset='#preset'+i;
    		$(preset).attr("style","background:#4EB7C1"); 
    		
    	}
   	
    	if(data['presetid']!=-1)
    	{       	
    		var preid="#preset"+data['presetid'];
    		$(preid).attr("style","background:green");   		
    	}
    	   	
    	switch(data['changeItem']){
    		case "light":
    			setlight(data['state']['light']);
    			break;
    		case "curtains":
    			setcurtains(data['state']['curtains']);
    			break;
    		case "tv":
    			settv(data['state']['tv']);
    			break;
    		case "radio":
    			setradio(data['state']['radio']);
    			break;    			
    		case "temp":
    			settemp(data['state']['temp']);
    			break;
    		case "all":
    			setlight(data['state']['light']);
    			setcurtains(data['state']['curtains']);
    			settv(data['state']['tv']);
    			setradio(data['state']['radio']);
    			settemp(data['state']['temp']);   			
    			break;    			
    		default:
    			break;
    		}
    }

    function _poll()
    {
    	var roomId=<?php  echo "$this->roomid" ?>;

    	$.ajax({
    	    url:"/room/index/data", 
    	    success: _handleUpdate,
    	    type: "POST",
    	    dataType: "json", 
    	    data: { "roomid" :roomId,
    		    "version" : version },
    	    complete: function() {
    	    	_poll();
    	    },
    	    timeout: 3000 });
    }

    function _monitor()
    {
    	_poll();
    }

    return {
    	monitor: _monitor,
    };
})(jQuery);

$(document).ready(function() {
	synrgic.state.monitor();
});

