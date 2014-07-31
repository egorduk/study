$(document).ready(function(){
	chat.init();
});

var chat = {
	data : {
		lastID 		: 0,
		noActivity	: 0
        //role		: 0
	},
	init : function(){
		chat.data.jspAPI = $('#chatLineHolder').jScrollPane({
			verticalDragMinHeight: 12,
			verticalDragMaxHeight: 12
		}).data('jsp');
		var working = false;
		//$('#submitForm').submit(function() {
        $("#btn-send-msg").click(function() {
			chat.data.noActivity = 0;
            var chatText = $('#chatText');
			var text = chatText.val();
		    if (text.length == 0 || working) {
				return false;
			}
			working = true;
			var tempID = 't' + Math.round(Math.random() * 1000000);
            var uid = chat.data.lastID;
            var arr = [];
			/*params = {
                //id: tempID,
                id: ++uid,
                text: text.replace(/</g,'&lt;').replace(/>/g,'&gt;'),
                msg: text
            };*/
            var nowDate = new Date();
            var a = nowDate.getTimezoneOffset();
            //console.log(a);
            var diff = (240 - Math.abs(a)) / 60;
            //var hours = nowDate.getHours() < 10 ? '0' + nowDate.getHours() : nowDate.getHours();
            var hours = nowDate.getHours();
            hours = parseInt(hours, 10);
            hours = hours + diff;
            hours = hours < 10 ? '0' + hours : hours;
            //console.log(hours);
            var minutes = nowDate.getMinutes() < 10 ? '0' + nowDate.getMinutes() : nowDate.getMinutes();
            var seconds = nowDate.getSeconds() < 10 ? '0' + nowDate.getSeconds() : nowDate.getSeconds();
            var time = hours + ':' + minutes + ':' + seconds;
            var day = nowDate.getDate() < 10 ? '0' + nowDate.getDate() : nowDate.getDate();
            var month = nowDate.getMonth();
            month++;
            month = month < 10 ? '0' + month : month;
            var year = nowDate.getFullYear();
            var date = day + '.' + month + '.' + year;
            var container = $(".container");
            var role_sender = container.data('role');
            var login_sender = container.data('login');
            var params = {msg: text, id: ++uid, date: date, time: time, role_sender: role_sender, login: login_sender};
            //var uid = chat.data.lastID;
            //params.id = ++uid;
            //params.id = tempID;
            arr.push(params);
            chat.addChatLine(arr);
            chatText.val('');
            //chat.render('chatLine', params);
			$.tzPOSTsubmitChat('sendMessage', text, function(response) {
				working = false;
                chatText.focus();
				//$('div.chat-' + tempID).remove();
				params['id'] = response.insertID;
            });
            //chat.addChatLine(arr);
            //chat.getChats();
			return false;
		});
		/*$.tzPOST('checkLogged', null, function(response) {
			if (response.isLogged) {
                //chat.login('Duk', 'avatar.jpg', 'admin');
				//chat.login(r.loggedAs.name, r.loggedAs.avatar, r.loggedAs.role);
                //console.oog('logged');
			}
		});*/
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
                        '<div id="chat-author" class="chat chat-', params.id,' rounded">' +
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
				else if (params.role_sender == 2)
					arr = [
                        '<div id="chat-client" class="chat chat-', params.id,' rounded">' +
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
                        '<div id="chat-system" class="chat chat-', params.id,' rounded">' +
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

			break;
			case 'user':
				arr = [
					'<div class="user" title="',params.login,'">' +
                        '<img src="',params.avatar,'" width="50" height="50" onload="this.style.visibility=\'visible\'" />' +
                    '</div>'
				];
			break;
		}
		return arr.join('');
	},
	addChatLine : function(params) {
		var paramsLength = params.length;
		for(var i = 0; i < paramsLength; i++) {
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
		if (paramsLength != 0) {
			chat.data.jspAPI.reinitialise();
			chat.data.jspAPI.scrollToBottom(true);
		}
	},
	getChats : function(callback) {
		$.tzGETgetChats('getChats', chat.data.lastID, function(response) {
			var arr = [], responseLength = response.messages.length;
			for(var i = 0; i < responseLength; i++) {
				arr.push(response.messages[i]);
			}
			chat.addChatLine(arr);
			if (responseLength) {
				chat.data.noActivity = 0;
				chat.data.lastID = response.messages[i-1].id;
			} else {
				chat.data.noActivity++;
			}
			if (!chat.data.lastID) {
				chat.data.jspAPI.getContentPane().html('<p class="noChats">Ничего еще не написано</p>');
			}
            var nextRequest = 1500;
			if (chat.data.noActivity > 3 && chat.data.noActivity < 30) {
				nextRequest = 2000;
			} else if(chat.data.noActivity > 30 && chat.data.noActivity < 60) {
				nextRequest = 4000;
            } else if(chat.data.noActivity > 60 && chat.data.noActivity < 120) {
                nextRequest = 6000;
			} else if(chat.data.noActivity > 120 && chat.data.noActivity < 240) {
				nextRequest = 8000;
			} else if(chat.data.noActivity > 240) {
                nextRequest = 10000;
            }
			setTimeout(callback, nextRequest);
            //console.log(nextRequest);
		});
	},
	getUsers : function(callback){
		$.tzGETgetUsers('getUsers', null, function(response){
			var users = [], responseLength = response.users.length;
            //console.log(response);
			for(var i = 0; i < responseLength; i++) {
				if (response.users[i]) {
					users.push(chat.render('user', response.users[i]));
				}
			}
			var message = '';
			if (responseLength < 1) {
				message = 'Online: 0';
			} else {
				message = 'Online: ' + responseLength;
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
	$.post('', {action : action, message : data}, callback, 'json');
};
$.tzGETgetChats = function(action,data,callback){
	$.post('', {action : action, lastId : data}, callback, 'json');
};
$.tzGETgetUsers = function(action, data, callback){
	$.post('', {action : action}, callback, 'json');
};

// Метод jQuery для замещающего текста:
$.fn.defaultText = function(value){
	var element = this.eq(0);
	element.data('defaultText',value);
	element.focus(function(){
		if (element.val() == value){
			element.val('').removeClass('defaultText');
		}
	}).blur(function(){
		if (element.val() == '' || element.val() == value){
			element.addClass('defaultText').val(value);
		}
	});
	return element.blur();
};