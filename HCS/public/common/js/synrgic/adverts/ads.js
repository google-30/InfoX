/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

var synrgic = synrgic || {};
synrgic.adverts = (function($) {
    var _settings = {
        updateInterval: 59,  //59 seconds
        refreshInterval: 15,
        popupInterval: 30, 
        popupTimeout: 60,  // 1 minutes
        speed: 1500, // slide speed
        durationInterval: 10,  // 10 seconds
        N: 3,
        H: 266,
        W: 266,
        BH:800,
        BW:266,
    };
    
    var _uid = 1;
    var _count = 0;
    function _init(settings) {
        $.extend(_settings, settings||{});   
    }
    
    function _scrollHeight(scroller, n) {
        var height = 0;
        scroller.find('.synrgic-ads-entry:lt('+n+')').each(function() {
            height += $(this).height();
        });
        return height;
    }

    function _scroll(scroller, speed, m) {
        var height = _scrollHeight(scroller, m);
        scroller.stop().animate({marginTop:'-='+height+'px'}, speed, function() {
            $(this).find('.synrgic-ads-entry:lt('+m+')').remove();
            $(this).css({'top':'0px', 'marginTop':'0px'});
        });
    }
   
    // cycle schedule
    function _cycleScheduleEntries(cycleq, entries, cyclingtime) {
        cycleq = _removeExpiredEntries(cycleq, cyclingtime);
        if(cycleq.length > 0) {
            //cycle one
            e = cycleq.shift();
            entries.push(e);
        }
        
        n = _calculateSize(cycleq);
        pending = [];
        while(entries.length>0) {
            e = entries.shift();
            if(cyclingtime < e.startTime) {
                pending.push(e);
            }
            else {
                // make sure that there is at least once show in queue
                n += e.size;
                if(n <= _settings.N) {
                    cycleq.push(e);
                }
                else {
                    entries.unshift(e);
                    // we should have a default adverts to fill the space
                    break;
                }
            }
        }
        
        //put back waiting for next cycle
        $.each(pending, function(i, e) {
            entries.push(e);
        });
        return cycleq;
    }

    function _removeExpiredEntries(entries, cyclingtime) {
        return $.grep(entries, function(e) {
            return _isActive(e, cyclingtime);
        });
    }
    
    function _isActive(e, cyclingtime) {
        return (cyclingtime >= e.startTime && cyclingtime  <= e.endTime);
    }
    
    function _calculateSize(entries) {
        n = 0;
        $.each(entries, function(i, e) {
            n += e.size;
        });
        return n;
    }

    function _makeAdverts(e, minh, minw) {
        return _replaceAdverts(null, e, minh, minw);
    }
    
    function _makeDefaultAdverts(size, minh, minw) {
        return _makeAdverts(_makeDummyScheduleEntry(size), minh, minw);
    }
    
    function _replaceAdverts(div, e, minh, minw) {
        var divid = e.id>0?('ads-'+e.id):('ads-def-'+e.id);
        if(div == null) {
            div = $('<div></div>').addClass('synrgic-ads-entry');
            div.attr('id', divid).width(minw).height(minh*e.size);
            _wrapScheduleEntry(e, div.innerHeight(), div.innerWidth()).appendTo(div);
        }
        else {
            div.attr('id', divid).width(minw).height(minh*e.size);
            $('.inner-wrap', div).replaceWith(_wrapScheduleEntry(e, div.innerHeight(), div.innerWidth()));
        }
        div.data('sentry', e);                                  
        div.data('duration', e.duration * _settings.durationInterval);
        div.data('size', e.size);
        return div;
    }
    
    function _wrapScheduleEntry(e, h, w) {
        var div = $('<div></div>').addClass('inner-wrap');
        var img = $('<img></img>').attr('src', e.img)
                                  .width(w).height(h);
        if(e.clickUrl == null || e.clickUrl == '') {
            div.append(img);
        }
        else {
            $('<a></a>').attr('href', e.clickUrl)
                        .attr('target', '_bank')
                        .append(img).appendTo(div);
        }
        return div;
    }
    
    function _makeDummyScheduleEntry(size) {
        var n = _count++;
        var img = "/example/images/ads/"+((n%_settings.N)+1)+".png";
        return {'id':-n, 'size':size, 'img':img, 'clickUrl':null, 'duration':0};
    }
    
    function _calculateTimeout(board, seconds) {
        $('.synrgic-ads-entry', board).each(function(i) {
            var dur = $(this).data('duration');
            dur -= seconds;
            $(this).data('duration', dur);
        });
    }
    
    function _makeScroller(board) {
        var scrollerid = 'scroller-'+_uid++;
        $('<div></div>', {'id': scrollerid}).addClass('synrgic-ads-board-scroller').appendTo(board);
        return scrollerid;
    }
    
    function _makePopbox(board) {
        var pboxid = 'pboxid-'+_uid++;
        var pbox = $('<div></div>', {'id': pboxid})
                    .addClass('synrgic-ads-pbox')
                    .appendTo(board);
        var commands = $('<div></div>');
        var close = $('<a></a>', {'id': pboxid+'-close', 'href':'#'})
                        .addClass('synrgic-ads-pbox-toolbar-button')
                        .text('Close');
        var disable = $('<a></a>', {'id':pboxid+'-disable', 'href':'#'})
                        .addClass('synrgic-ads-pbox-toolbar-button')
                        .text('Disable');
        var remove = $('<a></a>', {'id':pboxid+'-remove', 'href':'#'})
                        .addClass('synrgic-ads-pbox-toolbar-button')
                        .text('Remove');
        commands.append(close).append(disable).append(remove);
        $('<div></div>', {'id': pboxid+'-toolbar'})
            .addClass('synrgic-ads-pbox-toolbar')
            .append(commands).appendTo(pbox);
        $('<div></div>', {'id': pboxid+'-content'})
            .addClass('synrgic-ads-pbox-content').appendTo(pbox);
        pbox.hide();
        return pboxid;
    }
    
    function _contains(ent, entries) {
        var contained = false;
        $.each(entries, function(i, e) {
            if(ent.id == e.id) {
                contained = true;
                return false;
            }
        });
        return contained;
    }
    
    function _remove(ent, entries) {
        var p = -1;
        $.each(entries, function(i, e) {
            if(ent.id == e.id) {
                p = i;
                return false;
            }
        });
        if(p!=-1) {
            entries.splice(p,1);
        }
        return entries;
    }
    
    //for cycle2
 
    //merge the space of the successive timedout adverts & mark them reusable
    function _mergeAdvertsDivs(board, minh, minw) {
        var div = null;
        var timedout = [];
        
        $('.synrgic-ads-entry', board).each(function(i) {
            var dur = $(this).data('duration');
            if(dur <= 0) {
                var e = $(this).data('sentry');
                if(e.id > 0) {
                    timedout.push(e);
                }
                if(div != null) {
                    // merge it to the top
                    size = div.data('size') + $(this).data('size');
                    div.data('size', size).width(minw).height(size*minh);
                    $(this).addClass('x-removed');
                }
                else {
                    div = $(this);
                    div.addClass('x-blank');
                }
            }
            else {
                div = null;
            }
        });
        $('.x-removed', board).remove();
        
        return timedout;
    }
    
    function _findBestFitDiv(board, size) {
        var epsilon = _settings.N+1;
        var div = null;
        $('.x-blank', board).each(function(i) {
            diff = $(this).data('size') - size;
            if(diff == 0) {
                // found best fit
                div = $(this);
                return false;
            }
            else if(diff > 0 && diff < epsilon) {
                div = $(this);
                epsilon = diff;
            }
        });
        return div;
    }
    
    function _flipDirection() {
        var dirs = ['tb', 'lr', 'bt', 'rl'];
        var i = Math.floor(Math.random()*4);
        return dirs[i];
    }
    
    function _flip(div, flipSpeed) {
        div.flip({
            'direction':_flipDirection(), 
            'speed': 600,
        });
        return div;
    }
    
    function _addAdverts(div, entry, minh, minw) {
        var diff = div.data('size') - entry.size;
        if(diff == 0) {
            _replaceAdverts(div, entry, minh, minw);
            div.removeClass('x-blank');
            div.addClass('x-new');
        }
        else {
            // split it
            _makeAdverts(entry, minh, minw).addClass('x-new').insertBefore(div);
            div.data('size', diff).height(minh*diff);
        }
    }
    
    // fill the gaps using default adverts
    function _pasteDefaultAdverts(board, minh, minw) {
        $('.x-blank', board).each(function(i) {
            size = $(this).data('size');
            while(size>1) {
                _makeDefaultAdverts(1, minh, minw).insertAfter($(this));
                size--;
            }
            _replaceAdverts($(this), _makeDummyScheduleEntry(size), minh, minw);
        }).removeClass('x-blank');
    }
    
    function _doFlipChain(ads, flipSpeed, callback, args) {
        if(ads.length > 0) {
            var a = ads.shift();
            a.flip({
                direction: _flipDirection(),
                duration: flipSpeed,
                onEnd: function() {
                    //alert(a.data('sentry').id);
                    _doFlipChain(ads, flipSpeed, callback, args);
                },
            });
        }
        else {
            if(callback !== undefined) {
                callback.apply(null, args);
            }
        }
    }
    
    function _effectAdverts(board, speed) {
        var ads = [];
        $('.x-new', board).each(function(i) {
            ads.push($(this));
        }).removeClass('x-new');
        _doFlipChain(ads, 100, undefined, [ads, speed]);
    }
    
    function _effectDefaultAdverts(board, speed) {
        var ads = [];
        $("[id^=ads-def-]", board).each(function(){
           ads.push($(this));
        });
        _doFlipChain(ads, 100, undefined, [ads, speed]);
    }
    
    function _effectAdvertsx(ads, speed) {
        $.each(ads, function(i, a) {
            $(this).fadeTo(200+i*300, 0.1+i*0.2, function() {
                $(this).fadeIn('fast').fadeTo('fast', 1);
            });
        });
    }
    
    function _create(elm, options, adverts) {
        var settings = $.extend($.extend({}, _settings), options||{});// copy global and set my own
        var board = elm.addClass('synrgic-ads-board');
        var scrollerid = _makeScroller(board);
        var pboxid = _makePopbox(board);
        var scroller = $('#'+scrollerid);
        var pbox = $('#'+pboxid);
        
        return  {
            _adverts:adverts,
            _board: board,
            _scrollerid: scrollerid,
            _scroller: scroller,
            _pbox: pbox,
            _pboxid: pboxid,
            _schedule : {
                    id:0, 
                    timestamp:0,
                    time:0, 
                    entries:[],
                },
            _updateScheduleUrl: undefined,
            _pops: [],
            _removed:[],
            _popupDisabled: false,
            _popupTimer: null,
            _popupCount: 0,
            _timer: null,
            _entries: [],
            _cycleQ: [],
            _cycleTimer: null,
            _updateTimer: null,
            _clock: null,  // count time
            _ticks: 0,
            _settings: settings,
            
            init: function() {
                var h = this._settings.N * this._settings.H;
                this._board.width(this._settings.BW).height(this._settings.BH);
                this._scroller.width(this._settings.BW).height(this._settings.BH);
                for(i=0;i<3;i++) {
                    _makeDefaultAdverts(1, this._H(), this._W()).appendTo(this._scroller);
                }
                this.update();
                this.show();                
                return this;
            },

            _getPopList: function() {
                return this._pops;
            },
    
            _getNonpopList: function() {
                return this._entries;
            },
            
            _W: function() {
                return Math.max(this._board.width(), this._settings.W);
            },
            _H: function() {
                return this._settings.H;
            },
            
            _popH: function() {
                return this._settings.H;
            },
            _popW: function() {
                return Math.max(this._pbox.width(), Math.floor(this._settings.W*0.6));
            },
            
            _currentTime: function() {
                return this._schedule.time + this._ticks;
            },
            
            _resetClock: function() {
                if(this._clock != null) {
                    clearInterval(this._clock);
                    this._ticks = 0;
                }
                var thisObj = this;
                this._clock = setInterval(function() {
                    thisObj._ticks += 1;
                    _calculateTimeout(thisObj._board, 2);
                }, 2000); // one seccond
            },
    
            _cycle1: function() {
                // cyclically show adverts
                var out = this._scroller.find('.synrgic-ads-entry').length;
                this._cycleQ = _cycleScheduleEntries(this._cycleQ, this._getNonpopList(), this._currentTime());
                var thisObj = this;
                $.each(this._cycleQ, function(i, e) {
                    _makeAdverts(e, thisObj._H(), thisObj._W())
                      .fadeTo((i+1)*600, 0.2*(i+1), function() {$(this).fadeIn('slow').fadeTo('slow', 1);})
                      .appendTo(thisObj._scroller);
                });
                
                n = _settings.N - _calculateSize(this._cycleQ);
                if(n>0) {
                    _makeDefaultAdverts(n, this._H(), this._W()).appendTo(this._scroller);
                }
                _scroll(this._scroller, _settings.speed, out);
                return this;
            },
            
            _cycle2: function() {
                var scroller = this._scroller;
                var timedout = _mergeAdvertsDivs(scroller, this._H(), this._W());
                var currTime = this._currentTime();
                var entries = this._entries;
                var cycleqChanged = false;

                //cycle timedout adverts out first
                $.each(timedout, function(i, e) {
                    if(_isActive(e, currTime)) {
                        entries.push(e);
                    }
                });
                    
                // update the advert board
                var pending = [];
                
                while(entries.length>0) {
                    e = entries.shift();
                    if(currTime < e.startTime) {
                        //going to next cycle
                        pending.push(e);
                    }
                    else {
                        // find the best fit block and put curr ads in it
                        var div = _findBestFitDiv(scroller, e.size);
                        if(div == null) {
                            //no enough space & put curr ads back to queue
                            //and wait for next cycle
                            entries.unshift(e);
                            break;
                        }
                        else {
                            _addAdverts(div, e, this._H(), this._W());
                            cycleqChanged = true
                        }
                    }
                }
                
                //put back the pending ones and wait for next cycle
                $.each(pending, function(i, e) {
                    entries.push(e);
                });
                
                if(cycleqChanged) {
                    // do an effect on the new ads
                    _effectAdverts(scroller, _settings.speed);
                }
                    
                // let default ads occupy the blank blocks
                _pasteDefaultAdverts(scroller, this._H(), this._W());
                _effectDefaultAdverts(scroller, _settings.speed);
            },
            
            _cycle: function() {
                //this._cycle1();
                this._cycle2();
            },
            
            show: function() {
                if(this._cycleTimer != null) {
                    clearTimeout(this._cycleTimer);
                }
                this._cycle();
                var thisObj = this;
                thisObj._cycleTimer = setTimeout(function() {
                    thisObj._cycleTimer = null;
                    thisObj.show();
                }, _settings.refreshInterval * 1000);
                return this;
            },
            
            _popup:function() {
                this._pops = _removeExpiredEntries(this._pops, this._currentTime());
                if(this._pops.length>0) {
                    var pbox = this._pbox;
                    var e = this._pops.shift();
                    var content = $('#'+this._boxid+'-content');
                    _makeAdverts(e, this._popH(), this._popW()).appendTo($('#'+this._pboxid+'-content').empty());
                    bot = $(window).height() - $(window.top).height()-pbox.height();
                    left = Math.floor(($(window.top).width() - pbox.width())/2);
                    pbox.css({'position':'fixed', 'marginBottom': '-'+bot+'px'});
                    this._pops.push(e);
                    pbox.show().fadeTo('slow', 0.6, function() {
                        $(this).fadeIn('slow').fadeTo('slow', 1.0);
                    });
                    this._pausePopup();
                }
                else {
                    this._closePopup();
                }
            },
            
            _pausePopup:function() {
                if(this._timer == null) {
                    var thisObj = this;
                    this._timer = setTimeout(function() {
                        pbox.hide();
                        thisObj._timer = null;
                        if(thisObj._popupTimer != null) {
                            clearTimeout(thisObj._popupTimer);
                        }
                        thisObj._popupTimer = setTimeout(function() {
                            // pause popup a while
                            thisObj.popup();
                        }, _settings.popupTimeout * 1000);
                    }, _settings.popupTimeout * 1000);
                }
            },
            
            popup:function() {
                if(!this._popupDisabled) {
                    var thisObj = this;
                    if(thisObj._popupTimer != null) {
                        clearTimeout(thisObj._popupTimer);
                    }
                    this._popupTimer = setTimeout(function() {
                        thisObj._popupTimer = null;
                        thisObj._popup();
                        thisObj.popup();
                    }, _settings.popupInterval * 1000);
                }
                return this;
            },
            
            _closePopup:function() {
                if(this._popupTimer != null) {
                    clearTimeout(this._popupTimer);
                }
                if(this._timer != null) {
                    clearTimeout(this._timer);
                    this._timer = null;
                }
                this._pbox.hide();
                thisObj._popupTimer = setTimeout(function() {
                    // pause popup a while
                    thisObj.popup();
                }, _settings.popupTimeout * 1000);
            },
            
            _disablePopup:function() {
                if(!this._popupDisabled) {
                    if(this._popupTimer != null) {
                        clearTimeout(this._popupTimer);
                        this._popupTimer = null;
                    }
                    if(this._timer != null) {
                        clearTimeout(this._timer);
                        this._timer = null;
                    }
                    this._pbox.hide();
                    this._popupDisabled = true;
                }
            },
            
            _removePopup:function() {
                if(this._popupTimer != null) {
                    clearTimeout(this._popupTimer);
                }
                this._removed.push(this._pops.pop());
                this._popup();
                this.popup();
            },
            
            bind:function() {
                var thisObj = this;
                $('#'+this._pboxid+'-close').bind('click', function() {
                    thisObj._closePopup();
                });
                $('#'+this._pboxid+'-disable').bind('click', function() {
                    thisObj._disablePopup();
                });
                $('#'+this._pboxid+'-remove').bind('click', function() {
                    thisObj._removePopup();
                });
                return this;
            },

            update: function() {
                thisObj = this;
                $.ajax({
                    url: this.getUpdateScheduleUrl(),
                    dataType: 'json',
                    data: {'timestamp': this._schedule.timestamp},
                    success: function(sch) {
                        thisObj._setSchedule(sch);
                        //thisObj.show();
                        setTimeout(function() {
                            thisObj.update();
                        }, _settings.updateInterval * 1000);
                    },
                    error: function(ctx, sch) {
                        thisObj.show();
                    },
                });
                return this;
            },
            
            setUpdateScheduleUrl: function(url) {
                this._updateScheduleUrl = url;
            },
            
            getUpdateScheduleUrl: function() {
                return (this._updateScheduleUrl === undefined || this._updateScheduleUrl == "")?
                    '/adverts/index/updschedule' : this._updateScheduleUrl;
            },
            
            _setSchedule: function(sch) {
                if(sch.timestamp != this._schedule.timestamp) {
                    var pops = [];
                    var entries = [];
                    this._schedule = sch;
                    this._cycleQ = [];
                    this._resetClock();                    
                    thisObj = this;
                    $.each(sch.entries, function(i, e) {
                        if(e.playMode == 'popup') {
                            if(!_contains(e, thisObj._removed) && !_contains(e, thisObj._pops)) {
                                pops.push(e);
                                _remove(e, thisObj._entries);
                                _remove(e, thisObj._cycleQ);
                            }
                        }
                        else {
                            if(!_contains(e, thisObj._entries) && !_contains(e, thisObj._cycleQ)) {
                                entries.push(e);
                                _remove(e, thisObj._pops);
                            }
                        }
                    });
                    if(entries.length>0) {
                        this._entries = entries.concat(this._entries);
                    }
                    if(pops.length>0) {
                        this._pops = this._pops.concat(pops);
                    }
                    console.log(sch);
                    console.log(this);
                }
            },
        };
    }

    return {
        init: _init,
        play: function(elm, options) {
            return _create(elm, options, this).init().popup().bind();
        },
    };
})(jQuery);

