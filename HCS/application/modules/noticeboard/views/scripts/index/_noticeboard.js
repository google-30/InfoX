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

synrgic.notices = (function($){

    var _noticeIcon;
    var _noticePanel;
    var _noticeBoard;
    var _noticeBoardContainer;
    var _defaultPanelData = "Fetching the current Weather..";
    var _pollTime = 0;
    var _alertCount=-1; // Indicate alerts are unknown initially
    var old=-1;
    var _popUp;

    function _toggleNoticeBoard()
    {

        _noticeBoardContainer.slideToggle(750);
        // TODO: Use jqueryUI slide event with left direction
    }

    function _acknowledgeNotice(id)
    {	
    	var element = $('[data-alert-id="' + id + '"]');
	var button = $(this);
	$.ajax({ url:"/noticeboard/index/acknowledge?id=" + id,
	         success: function(query,s){
		 	_updateAlert(element,{new : false,
		       			      id : id	});
		 	/*
			_noticeBoard.listview("refresh");
			button.unbind("click");
			button.click(function() { _deleteNotice(id); });            
            _alertCount=0; // Unpon acking an alert, we fetch all new alerts
            */
            _noticeBoard.listview("refresh").delay(1000).queue(function() {
                _deleteNotice(id);
                _alertCount=0; // Unpon acking an alert, we fetch all new alerts
                $(this).dequeue();
            });
		 }
	 });
    }

    function _deleteNotice(id)
    {
    	var element = $('[data-alert-id="' + id + '"]');
        _noticeBoard.listview('refresh');
	$.ajax({ 
		url:"/noticeboard/index/delete?id=" + id ,
		success: function (query,s) {
		/*
		    categoryid = element.parent().parent().attr("id");
		    category = element.parent().parent();
		    childDivs = $("#" + p_id + ' li' );
		    if( childDivs.length < 1 )
		    	category.hide('slide').promise().then(function() { category.remove(); });
			*/
		    element.hide('slide').promise().then(function() { element.remove();});
		    _noticeBoard.listview("refresh");
		    _alertCount--;
		}
	});
    }

    function _deleteAllNotices()
    {
	_noticeBoard.children().children().addClass('disabled'); 
	_noticeBoard.listview("refresh");
        $.ajax({
		 url:"/noticeboard/index/deleteAll",
		 success:function (query,s){ 
		 _noticeBoard.children().hide('slide').promise().then(
		     function() { 
		     	_noticeBoard.empty(); 
		     });
		 _alertCount=0;
		 }
       });	     
    }

    function _handleUpdate(data)
    {
	if(data["weather"]){
	    _defaultPanelData = data["weather"];
	}
	
	if(_alertCount>=0)
		old=_alertCount;

	if(data["alerts"]){
	    alerts=data["alerts"];
		if(old<0)
		{
			old=alerts.length;
		}			
	    _alertCount = alerts.length;
	    _updateNoticeMini(alerts);
	    _updateNoticeBoard(alerts);
	}
	
	if(old>_alertCount)
	{
		old=alerts.length;
	}	
	//alert("_alertCount="+_alertCount+";old="+old);

	if((_alertCount>0)&&(old!=-1)&&(_alertCount-old==1)){
		old=_alertCount;
	    _musicPlayer.show();
	    setTimeout("_musicPlayer.hide();",1000);		
	}

    }

    function _poll()
    {
	$.ajax({
	    url:"/noticeboard/index/data", 
	    success: _handleUpdate,
	    type: "POST",
	    dataType: "json", 
	    data: { "activeAlerts" : _alertCount,
		    "lastPolled" : _pollTime },
	    complete: function() {
	    	_pollTime = new Date();
		_poll();
	    },
	    timeout: 30000 });
    }

    function _monitor()
    {
	_poll();
    }

    function _init(panelid,boardid,iconid,boardcontainerid,noticepopupid,music)
    {
	_noticePanel = $(panelid);
	_noticeBoard = $(boardid);
	_noticeIcon = $(iconid);
	_noticeBoardContainer = $(boardcontainerid);
	_popUp = $(noticepopupid);
	_musicPlayer = $(music)
    }

    function _updateNoticeBoard(alerts)
    {
	// Cycle throuch the provided data inserting, updating or removing items
	// as we go. Items are sorted by id so we simply check for a matching
	// element in the dom. If none is found we remove them. Elements are 
	// have the id stored in the 'data-alert' attribute
	for( var i = 0; i < alerts.length; i++ ){

	    var theAlert = alerts[i];
	    var matches = _noticeBoard.find('li[data-alert-id=' + theAlert["id"] + ']');
	    if( matches.length == 0 ){
		// New alert
	 	// Search for category
	    	var cmatches = _noticeBoard.find("li[data-category='" + theAlert["category"] + "']");
		if (cmatches.length > 0){
		    element = _createAlert(theAlert);
		    element.appendTo(_noticeBoard);		    
		} else {
		    category = _createCategory(theAlert);
		    element = _createAlert(theAlert);
		    category.appendTo(_noticeBoard);
		    element.appendTo(_noticeBoard);

		}
	    } 
	}

	_noticeBoard.listview("refresh");
    }

    function _createCategory(theAlert)
    {
	var li = $('<li/>');
	li.attr('data-role','list-divider');
	li.attr('data-category',theAlert["category"]);
	li.html(theAlert["category"]);
	return li;
    }

    function _createAlert(theAlert)
    { 	
	var li = $('<li/>');
	li.attr('data-theme','b');

	if( theAlert["new"] ){
	    li.attr('data-icon','check');
	    li.attr('data-theme','a');
	} else {
	    li.attr('data-icon','trash');
	}
	li.attr('data-alert-id',theAlert["id"]);
	li.attr('data-alert-new',theAlert["new"]);


	var a = $('<a href="#noticePopUp" data-rel="popup" data-transition="pop" data-position-to"window">' + theAlert["title"] + '</a>');
	li.append(a);
	a.click(function() { _setPopup(theAlert); });

	li.append('<p>' + theAlert["message"] + '</p>');

	// We define this here
	// as an append as trying to add the click
	// listener manually was failing for some reason
	if( theAlert["new"] ){
	    li.append('<a href="#" onclick="synrgic.notices.acknowledge(' + theAlert.id + ')">');
	} else {
	    li.append('<a href="#" onclick="synrgic.notices.delete(' + theAlert.id + ')">');

	}
	
	return li;
    }

    function _updateAlert(element,theAlert)
    {
	if(theAlert["new"]){
	    element.find('span .ui-icon-trash').addClass('ui-icon-check').removeClass('ui-icon-delete');
	    var currentTheme = element.attr('data-theme');
	    element.removeClass('ui-body-' + currentTheme);
	    element.addClass('ui-body-a');
	    
	} else {
	    element.find('span .ui-icon-check').addClass('ui-icon-trash').removeClass('ui-icon-check');
	    var currentTheme = element.attr('data-theme');

	    // TODO: Clean up styles for selected item
	    element.removeClass('ui-body-' + currentTheme)
	    element.children().removeClass('ui-body-' + currentTheme)
	    element.addClass('ui-body-c');
	    element.children().addClass('ui-body-c');

	}
    }

    function _updateNoticeMini(alerts)
    {   	
	var newCount = 0;
	var newestAlert = null;
	for( var i = 0; i < alerts.length; i++ ){

	    var theAlert = alerts[i];

	    if ( theAlert["new"] == true ){
		newestAlert = theAlert;	
		newCount++;
	    }
	}
	
	if(newCount > 0 ){
	    _noticeIcon.html(newCount);
	    _noticeIcon.show();
	    _noticePanel.show();
	    _noticePanel.html(newestAlert["title"]);
	    _noticePanel.unbind("click");
	    _noticePanel.click(function() { _acknowledgeNotice(newestAlert.id); }); 
	} else {
		_noticeIcon.hide();
	    _noticePanel.html(_defaultPanelData);
	}	
    }

    function _setPopup(theAlert)
    {
	_popUp.html('<a href="#" data-rel="back" data-role="button" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>\
		    <ul data-role="listview" data-divider-theme=a>\
		      <li data-role="list-divider">' + theAlert["category"] + ":" + theAlert["title"]+ '</li>\
	    	     <li style="width:250px">Issued:' + theAlert["issued"] + '</li>\
	    	     <li> ' + theAlert["message"] + '</li>'
		     );
	_popUp.trigger("create");
    }


    return {
	init: _init,
	monitor: _monitor,
	deleteAll: _deleteAllNotices,
	acknowledge: function (id) { _acknowledgeNotice(id) ; },
	delete: function (id) { _deleteNotice(id); },
	toggleBoard: function() { _toggleNoticeBoard();}
    };
})(jQuery);

$(document).ready(function() {
	synrgic.notices.init("#noticePanel","#noticeBoardList","#noticeIcon","#noticeboard","#noticePopUp","#soundControl");
	synrgic.notices.monitor();
});

