$(document).ready(function(){
	chat.init();
});

var chat = {
	data : {
		lastID 		: 0,
		noActivity	: 0,
		role		: 0
	},
	init : function(){
		chat.data.jspAPI = $('#chatLineHolder').jScrollPane({
			verticalDragMinHeight: 12,
			verticalDragMaxHeight: 12
		}).data('jsp');
		var working = false;
		$('#submitForm').submit(function() {
			chat.data.noActivity = 0;
			var text = $('#chatText').val();
		    if (text.length == 0 || working) {
				return false;
			}
			working = true;
			var tempID = 't' + Math.round(Math.random() * 1000000);
			params = {
					id			: tempID,
					text		: text.replace(/</g,'&lt;').replace(/>/g,'&gt;')
				};
			$.tzPOSTsubmitChat('sendMessage', text, function(response) {
				working = false;
				$('#chatText').val('').focus();
				$('div.chat-' + tempID).remove();
				params['id'] = response.insertID;
            });
			return false;
		});
		$.tzPOST('checkLogged', null, function(response) {
			if (response.isLogged) {
                //chat.login('Duk', 'avatar.jpg', 'admin');
				//chat.login(r.loggedAs.name, r.loggedAs.avatar, r.loggedAs.role);
                console.oog('logged');
			}
		});
		(function getChatsTimeoutFunction(){
			chat.getChats(getChatsTimeoutFunction);
		})();
		(function getUsersTimeoutFunction(){
			chat.getUsers(getUsersTimeoutFunction);
		})();
		
	},
	login : function(name, avatar, role) {
		/*chat.data.name = name;
		chat.data.role = role;
		$('#submitForm').fadeIn();
		$('#chatText').focus();	*/
	},
	render : function(template, params) {
		var arr = [];
		switch(template) {
			case 'chatLine':
				if (params.role_sender == 1)
					arr = [
                        '<div class="chat chat-', params.id,' rounded">' +
                            '<div class="row">' +
                                '<div class="col-md-2">' +
                                    '<span class="login">', params.login,':</span></br>' +
                                    '<span class="datetime">' +
                                        '<span class="date">', params.date + '</br>' + params.time,'</span>' +
                                    '</span>' +
                                '</div>' +
                                '<div class="col-md-10" style="margin-left:20px">' +
                                    '<span class="text">', params.msg,'</span>' +
                                '</div>' +
                            '</div>' +
                        '</div>'
                    ];
				else
					arr = [
                        '<div id="chat-client" class="chat chat-', params.id,' rounded">' +
                            '<div class="row">' +
                                '<div class="col-md-2">' +
                                    '<span class="login">', params.login,':</span></br>' +
                                    '<span class="datetime">' +
                                        '<span class="date">', params.date + '</br>' +params.time,'</span>' +
                                    '</span>' +
                                '</div>' +
                                '<div class="col-md-10" style="margin-left:20px">' +
                                    '<span class="text">', params.msg,'</span>' +
                                '</div>' +
                            '</div>' +
                        '</div>'
                    ];
			break;
			/*case 'user':
				arr = [
					'<div class="user" title="',params.name,'"><img src="',
					params.avatar,'" width="50" height="50" onload="this.style.visibility=\'visible\'" /></div>'
				];
			break;*/
		}
		return arr.join('');
	},
	addChatLine : function(params) {
		var d = new Date();
		for(var i = 0; i < params.length; i++) {
			/*if (params[i].time)  {
				d.setUTCHours(params[i].time.hours, params[i].time.minutes, params[i].time.seconds);
			}
			params[i].time = (d.getHours() < 10 ? '0' : '' ) + d.getHours() + ':' + (d.getMinutes() < 10 ? '0':'') + d.getMinutes() + ':' + d.getSeconds();
			if (params[i].date) {
				d.setUTCMonth(params[i].date.month);
				d.setUTCDate(params[i].date.day);
				d.setUTCFullYear(params[i].date.year);
				params[i].date = (d.getDate() < 10 ? '0' : '' ) + d.getDate() + '.' + (d.getMonth() < 10 ? '0' : '' ) + d.getMonth() + '.' + d.getUTCFullYear();
			} else {
				params[i].date = (d.getDate() < 10 ? '0' : '' ) + d.getDate() + '.' + ((d.getUTCMonth()+1) < 10 ? '0' : '' ) + (d.getUTCMonth()+1) + '.' + d.getUTCFullYear();
            }*/
			var markup = chat.render('chatLine', params[i]), exists = $('#chatLineHolder .chat-' + params[i].id);
			if (exists.length) {
				exists.remove();
			}
			if (!chat.data.lastID) {
				$('#chatLineHolder p').remove();
			}
			if (params[i].id.toString().charAt(0) != 't') {
				var previous = $('#chatLineHolder .chat-' + (+params[i].id - 1));
				if (previous.length) {
					previous.after(markup);
				} else chat.data.jspAPI.getContentPane().append(markup);
			} else chat.data.jspAPI.getContentPane().append(markup);
		}
		if (params.length != 0) {
			chat.data.jspAPI.reinitialise();
			chat.data.jspAPI.scrollToBottom(true);
		}
	},
	getChats : function(callback) {
		$.tzGETgetChats('getChats', chat.data.lastID, function(response) {
			var arr = [];
			for(var i = 0; i < response.messages.length; i++) {
				//chat.addChatLine(r.chats[i]);
				arr.push(response.messages[i]);
			}
			chat.addChatLine(arr);
			if (response.messages.length) {
				chat.data.noActivity = 0;
				chat.data.lastID = response.messages[i-1].id;
			} else {
				chat.data.noActivity++;
			}
			if (!chat.data.lastID) {
				chat.data.jspAPI.getContentPane().html('<p class="noChats">Ничего еще не написано</p>');
			}
			if (chat.data.noActivity > 3 && chat.data.noActivity < 30) {
				nextRequest = 2000;
			} else if(chat.data.noActivity > 30 && chat.data.noActivity < 60) {
				nextRequest = 4000;
            } else if(chat.data.noActivity > 60 && chat.data.noActivity < 120) {
                nextRequest = 6000;
			} else if(chat.data.noActivity > 120 && chat.data.noActivity < 240) {
				nextRequest = 8000;
			}
            else if(chat.data.noActivity > 240) {
                nextRequest = 10000;
            }
            else {
                var nextRequest = 1500;
            }
			setTimeout(callback,nextRequest);
		});
	},
	getUsers : function(callback){
		$.tzGETgetUsers('getUsers',null,function(response){
			var users = [];
			for(var i=0; i< response.users.length;i++) {
				if (response.users[i]) {
					users.push(chat.render('user', response.users[i]));
				}
			}
			var message = '';
			if (response.total < 1) {
				message = 'Online: 0';
			}
			else {
				message = 'Online: ' + response.total;
			}
			users.push('<p class="count">' + message + '</p>');
			$('#chatUsers').html(users.join(''));
			setTimeout(callback, 5000);
		});
	}
};

$.tzPOST = function(action, data, callback){
	$.post('', {action : action}, callback, 'json');
};
$.tzPOSTsubmitChat = function(action, data, callback){
	$.post('', {action:action, text:data}, callback, 'json');
};
$.tzGETgetChats = function(action,data,callback){
	$.post('', {action:action, lastId:data}, callback, 'json');
};
$.tzGETgetUsers = function(action,data,callback){
	$.post('', {action:action}, callback, 'json');
};

// Метод jQuery для замещающего текста:
$.fn.defaultText = function(value){
	var element = this.eq(0);
	element.data('defaultText',value);
	element.focus(function(){
		if(element.val() == value){
			element.val('').removeClass('defaultText');
		}
	}).blur(function(){
		if(element.val() == '' || element.val() == value){
			element.addClass('defaultText').val(value);
		}
	});
	return element.blur();
}