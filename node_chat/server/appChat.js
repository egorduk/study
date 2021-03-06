var PORT = 8111, options = {
    //'log level': 1
};
var express = require('express'),
    http = require('http'),
    path = require('path'),
    dateFormat = require('dateformat'),
    app = express(),
    server = http.createServer(app),
    io = require('socket.io').listen(server, options);

server.listen(PORT);

app.use('/static', express.static(__dirname + '/static'));
app.get('/', function (req, res) {
    res.sendFile(__dirname + '/index.html');
});

/*var pool = mysql.createPool({
 canRetry: true,
 database: 'study',
 host: 'localhost',
 user: 'root',
 password: '',
 port: 3306,
 connectionLimit: 8,
 waitForConnections: true,
 queueLimit: 0
 });
 // Attempt to catch disconnects
 pool.on('connection', function(connection) {
 console.log('Connection established');

 // Below never get called
 connection.on('error', function(err) {
 console.error(new Date(), 'MySQL error', err.code);
 });
 connection.on('close', function(err) {
 console.error(new Date(), 'MySQL close', err);
 });
 });
 module.exports = pool;*/

//var log4js = require('log4js');
//var logger = log4js.getLogger();
//var winston = require('winston');

/*var MongoClient = require('mongodb').MongoClient, format = require('util').format, userListDB, chatDB;
 MongoClient.connect('mongodb://127.0.0.1:27017', function (err, db) {
 if (err) {throw err}
 userListDB = db.collection('users');
 chatDB = db.collection('chat');
 });*/
/*
 var transporter,
 nodemailer = require('nodemailer'),
 userEmail = 'egorduk91@gmail.com',
 passEmail = 'rezistor';*/

var mysql = require('mysql'),
    mysqlUtilities = require('mysql-utilities'),
    connection = mysql.createConnection({
        host:     'localhost',
        user:     'root',
        password: '',
        database: 'study'
    });

var arrUserLogin = [],
    arrUserId = [],
    arrOrderChannel = [],
    arrUid = [];


connection.connect(function (err, db) {
    if (err) {
        throw err;
    }
    // userListDB = db.collection('users');
});

// Mix-in for Data Access Methods and SQL Autogenerating Methods
mysqlUtilities.upgrade(connection);

// Mix-in for Introspection Methods
mysqlUtilities.introspection(connection);

/*connection.queryHash(
 'SELECT * FROM webchat_message', [],
 function(err, result) {
 //console.dir({rows: result});
 }
 );*/

// Release connection
//connection.end();

var validator = require('validator');

io.sockets.on('connection', function (client) {
    var clientID = (client.id).toString();

    console.log('ClientID - ' + clientID);

    client.on('request_join_channel', function(data) {
        //console.dir(data);
        var channel = data.channel;

        arrUserLogin[clientID] = data.userLogin;
        arrUserId[clientID] = data.userId;
        arrOrderChannel[clientID] = channel;
        //arrUid[data.userId] = clientID;

        client.join(channel);

        console.log('Connected - ' + arrUserLogin[clientID] + " to channel ORDER - " + channel);

        client.emit('response_join_channel');
        client.to(channel).emit('response_user_online', data);

        /*Init variable for send mailing*/
        /*transporter = nodemailer.createTransport({
         service: 'gmail',
         auth: {
         user: userEmail,
         pass: passEmail
         }
         }, function(error) {
         if (error) {throw error}
         });*/
        /*var typeConnection = data.type;
         if (typeConnection == 'client_index') {
         } else if (typeConnection == 'client_select_author') {
         *//* var params = { 'min-price': 100, 'max-price': 999999, 'max-day': 999, 'min-day': 1 };
         client.emit("response set params", {data: params});*//*
         }*/
    });

    /*client.on("join to channel messages", function(data) {
     checkCredentials({userId: data.channel, token: data.token}, function(a) {
     console.log(a);
     if (a) {
     var channel = data.channel;
     arrUserLogin[clientID] = data.userLogin;
     arrChannelOrder[clientID] = channel;
     client.join(channel);
     console.log("Connected - " + arrLogin[clientID] + " to channel messages - " + channel);
     connection.update('user', {is_active: 1}, {login: data.userLogin, is_active: 0}, function(error) {
     if (error) {throw error}
     //client.to(channel).emit("request set online status");
     });
     } else {
     createErrorLog({userId: data.channel, channel: data.channel, token: data.token, type: 'join_channel_messages'});
     }
     });
     });

     client.on("join to channel self", function() {
     var channel = arrUserId[clientID];
     client.join(channel);
     console.log("Connected - " + arrLogin[clientID] + " to channel self - " + channel);
     });*/

    client.on('request_add_new_message', function (data) {
        var orderId = arrOrderChannel[clientID],
            userId = arrUserId[clientID],
            responseId = data.responseId,
            fullDate = getFullDate(new Date()),
            writerLogin = arrUserLogin[clientID],
            messageText = data.messageText,
            mode = checkIsValidateMessage(messageText);
        console.dir(data);

        connection.insert('webchat_message', {
            message: messageText,
            response_id: responseId,
            writer_id: userId,
            user_order_id: orderId
        }, function(error, recordId) {
            if (error) {throw(error)}
            var data = { dateWrite: fullDate, messageText: messageText, writerLogin: writerLogin };
            client.emit("show new message", data)
                .to(arrUid[responseId]).emit("show new message", data);
            /* connection.select('user', 'id', {login: responseLogin}, '', function(error, row) {
             if (error) {throw error}
             client.to(row[0].id).emit("response get new message from client", {dateWrite: fullDate, messageText: messageText, userLogin: writerLogin, userId: userId, messageId: recordId, orderNum: orderNum});
             });*/
            if (mode) {
                // Send about denied rules
                connection.insert('ban_message', {
                    message_id: recordId
                }, function(error) {
                    if (error) {throw(error)}
                    console.log('New banned message - ' + recordId);
                });
                /*transporter.sendMail({
                 from: 'Test email <egorduk91@gmail.com>',
                 address: userEmail,
                 to: 'a_1300@mail.ru',
                 subject: 'Warning message',
                 text: 'Warning message detected! Message ID - ' + recordId
                 //html: '<i>html</i>'
                 }, function(error, info){
                 if (error) {
                 console.log(error);
                 } else {
                 console.log('Message sent: ' + info.response);
                 }
                 });*/
            } else {
                // Send new email to other user
                /*connection.queryRow(
                 'SELECT email AS user_email FROM user WHERE login = "' + responseLogin + '"', function(error, row) {
                 if (error) {throw error}
                 console.dir(row);
                 transporter.sendMail({
                 from: 'Test email <egorduk91@gmail.com>',
                 address: userEmail,
                 to: row.user_email,
                 subject: 'New message',
                 text: 'New message! User login - ' + writerLogin + ', message - ' + message
                 //html: '<i>html</i>'
                 }, function(error, info){
                 if (error) {
                 console.log(error);
                 } else {
                 console.log('Message sent: ' + info.response);
                 }
                 });
                 }
                 )*/
            }
            // }
            // );
            console.dir({insertedID: recordId});
        });
        //client.disconnect();
    });

    client.on('request_get_all_messages', function (data) {
        var orderId = arrOrderChannel[clientID],
            userId = arrUserId[clientID];
        //otherUserId = data.authorId ? data.authorId : data.clientId;

        connection.queryHash(
            'SELECT wm.id, wm.message messageText, DATE_FORMAT(date_write,"%d.%m.%Y %T") dateWrite, u.login writerLogin, u.id writerId ' +
            'FROM webchat_message wm ' +
            'INNER JOIN user u ON wm.writer_id = u.id ' +
            //' INNER JOIN user u1 ON wm.response_id = u1.id' +
            //' WHERE wm.user_order_id = ' + orderId + ' AND (wm.writer_id = ' + otherUserId + ' OR wm.response_id = ' + otherUserId + ')' +
            'WHERE wm.user_order_id = ' + orderId + ' AND (wm.writer_id = ' + userId + ' OR wm.response_id = ' + userId + ') ' +
            'AND (wm.writer_id = ' + userId + ' OR wm.response_id = ' + userId + ')',

            function(error, rows) {
                //console.dir(rows);
                if (error) {throw error}

                /*for (var key in rows) {
                    if (checkIsValidateMessage(rows[key].messageText)) {
                        rows[key].messageText = replaceIncorrectMessage(rows[key].messageText);
                    }
                }*/

                client.emit('response_get_all_messages', rows);
                /* connection.update('webchat_message', { is_read: 1 }, { user_order_id: orderId, response_id: userId, is_read: 0 }, function(error) {
                 if (error) {throw error}
                 });*/
            }
        );
    });

    client.on("disconnect", function() {
        var channel = arrOrderChannel[clientID];

        console.log("Disconnected - " + arrUserLogin[clientID] + " from channel ORDER - " + channel);

        client.to(channel).emit("response disconnect user");

        delete arrOrderChannel[clientID];
        delete arrUserLogin[clientID];
        delete arrUid[arrUserId[clientID]];
        delete arrUserId[clientID];
        // client.leave(room); //Rooms are left automatically upon disconnection.
    });

    client.on('request_view_online_user', function() {
        var channel = arrOrderChannel[clientID];

        var res = []
            // the default namespace is "/"
            , ns = io.of("/");

        if (ns) {
            for (var id in ns.connected) {
                if(channel) {
                    //var index = ns.connected[id].rooms.indexOf(channel);
                    console.dir(ns.connected[id].rooms);
                    /*if(index !== -1) {
                        res.push(ns.connected[id]);
                    }*/
                } else {
                    res.push(ns.connected[id]);
                }
            }
        }

        console.dir(res);


        //client.to(channel).emit('response_user_online', data);
    });

    client.on("request user write", function() {
        var channel = arrOrderChannel[clientID];
        client.to(channel).emit("response user write");
    });

    client.on("request confirm work", function(data) {
        var orderId = data.orderId,
            dateConfirm = dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss"),
            comment = data.comment,
            degree = data.degree;
        if (validator.isLength(comment, 0, 100) && validator.isInt(degree) && (degree <= 5 || degree >= 1)) {
            connection.select('status_order', 'id AS status_id', {code: 'g'}, '', function(error, row) {
                if (error) {throw error}
                var statusId = row[0].status_id;
                connection.update('user_order', {date_confirm: dateConfirm,
                    status_order_id: statusId,
                    client_comment: comment,
                    client_degree: degree}, {id: orderId}, function(error) {
                    if (error) {throw error}
                    createSystemMsg(orderId, 'confirm-work');
                });
            });
        } else {
            console.dir('Error - ' + data);
        }
    });

    client.on("request client bid", function(data) {
        // console.dir(data);
        var day = data.day,
            price = data.replace(/\s/g, ""),
            userId = data.userId,
            orderId = data.orderId;
        if (validator.isLength(price, 2, 6) && validator.isInt(price) && validator.isInt(day) && validator.isLength(day, 1, 3) && (day > 0 || day < 999)) {

        }
    });

    client.on("request get new messages from db", function () {
        var userId = arrOrderChannel[clientID];
        //connection.select('webchat_message', 'message, user_order_id AS orderId, date_write, user_id', {is_read: 0, user_id: userId, channel: channel}, '', function(error, rows) {
        connection.queryHash(
            'SELECT wm.id AS messageId, uo.num AS orderNum, message, DATE_FORMAT(date_write,"%d.%m.%Y %T") AS dateWrite, login AS userLogin, wm.user_id AS userId FROM webchat_message wm' +
            ' INNER JOIN user u ON user_id = u.id' +
            ' INNER JOIN user_order uo ON wm.user_order_id = uo.id' +
            ' WHERE wm.is_read = 0 AND channel REGEXP "_' + userId + '$"', function(error, rows) {
                if (error) {throw error}
                //console.dir(rows);
                client.emit('response_get_new_messages', rows);
            });
    });

    client.on("request read message", function (data) {
        var messageId = data.messageId, userId = arrOrderChannel[clientID];
        connection.select('webchat_message', 'id', {user_id: userId, id: messageId, is_read: 0}, '', function(error, row) {
            if (error) {throw error}
            if (row.length) {
                connection.update('webchat_message', {is_read: 1}, {id: messageId}, function(error) {
                    if (error) {throw error}
                    console.log('Read message - ' + messageId);
                    client.emit("response read message");
                })
            }
        })
    });

    client.on("request hide bid", function (data) {
        var orderId = data.orderId, bidId = data.bidId;
        connection.update('user_bid', {is_show_client: 0}, {id: bidId, user_order_id: orderId, is_show_client: 1}, function(error) {
            if (error) {throw error}
            client.emit('response hide bid');
            var channel = arrOrderChannel[clientID];
            createSystemMsg({orderId: orderId, type: 'hide_author_bid', channel: channel});
        });
    });

    client.on("request confirm author bid", function (data) {
        var bidId = data.bidId, orderId = data.orderId;
        connection.select('status_order', 'id AS status_id', {code: 'ca'}, '', function(error, row) {
            if (error) {throw error}
            var statusId = row[0].status_id;
            connection.update('user_bid', {is_select_client: 1}, {id: bidId}, function(error) {
                if (error) {throw error}
                connection.update('user_order', {status_order_id: statusId}, {id: orderId}, function(error) {
                    if (error) {throw error}
                    client.emit('response confirm author bid');
                    createSystemMsg({orderId: orderId, type: 'confirm_author_bid', channel: data.channel});
                    //sendMail('confirm_author_bid', data);
                });
            });
        });
    });

    client.on("request cancel author bid", function (data) {
        var bidId = data.bidId, orderId = data.orderId;
        connection.select('status_order', 'id AS status_id', {code: 'sa'}, '', function(error, row) {
            if (error) {throw error}
            var statusId = row[0].status_id;
            connection.update('user_bid', {is_select_client: 0}, {id: bidId}, function(error) {
                if (error) {throw error}
                connection.update('user_order', {status_order_id: statusId}, {id: orderId}, function(error) {
                    if (error) {throw error}
                    client.emit('response cancel author bid');
                    //client.to(channel).emit('response cancel author bid');
                    createSystemMsg({orderId: orderId, type: 'cancel_author_bid', channel: data.channel});
                    //sendMail('cancel_author_bid', data);
                });
            });
        });
    });

    client.on("request auction bid", function (data) {
        console.log(data);
        var orderId = data.orderId,
            price = data.price.replace(/\s/g, ""),
            day = data.day,
            authorLogin = data.authorLogin,
            channel = arrOrderChannel[clientID];
        if (validator.isLength(price, 3, 7) && validator.isLength(day, 1, 3) && validator.isInt(price) && validator.isInt(day)) {
            connection.select('user', 'id, email', {login: authorLogin}, '', function(error, row) {
                if (error) {throw error}
                var authorId = row[0].id;
                data.email = row[0].email;
                connection.insert('auction_bid', {
                    price: price,
                    day: day,
                    //date_auction: dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss"),
                    user_id: authorId,
                    user_order_id: orderId
                }, function(error, insertedId) {
                    if (error) {throw error}
                    client.emit('response auction bid');
                    client.to(channel).emit('response auction bid', {price: data.price, day: day, bidId: insertedId});
                    createSystemMsg({orderId: orderId, type: 'create_auction_bid', channel: channel});
                    //sendMail('create_auction_bid', data);
                });
            });
        } /*else if (validator.isLength(price, 0, 2) || !validator.isInt(price)) {
         errorMessage = 'Минимум 100 руб.';
         errorField = 'price';
         } else if (validator.isLength(day, 0) || !validator.isInt(day)) {
         errorMessage = 'Минимум 1 дн.';
         errorField = 'day';
         }
         client.emit('response auction bid', {errorMessage: errorMessage, errorField: errorField});*/
    });


    function checkCredentials(data, callback) {
        var token = data.token, userId = data.userId;
        connection.select('user', 'token', {id: userId, token: token}, '', function(error, row) {
            if (error) {throw error}
            if (row.length) {
                callback(1);
            } else {
                callback(0);
            }
        });

    }

    function sendMail(type, data) {
        var  from = 'Test email <egorduk91@gmail.com>', to, subject, text, html;
        if (type == "confirm_author_bid") {
            connection.select('user', 'email', {login: data.responseLogin}, '', function(error, row) {
                if (error) {throw error}
                //to = row[0].email;
                connection.select('user_order', 'theme, num', {id: data.orderId}, '', function(error, row) {
                    if (error) {throw error}
                    console.log(row[0]);
                });
            });
            to = 'a_1300@mail.ru';
            subject = 'Ваша ставка принята';
            text = 'Text of message';
        } else if (type == "cancel_author_bid") {
            connection.select('user', 'email', {login: data.responseLogin}, '', function(error, row) {
                if (error) {throw error}
                //to = row[0].email;
                connection.select('user_order', 'theme, num', {id: data.orderId}, '', function(error, row) {
                    if (error) {throw error}
                    console.log(row[0]);
                });
            });
            to = 'a_1300@mail.ru';
            subject = 'Ваша ставка отклонена';
            text = 'Text of message';
        } else if (type == "create_auction_bid") {
            to = data.email;
            connection.select('user_order', 'theme, num', {id: data.orderId}, '', function(error, row) {
                if (error) {throw error}
                console.log(row[0]);
            });
            to = 'a_1300@mail.ru';
            subject = 'Заказчик предлагает свои условия';
            text = 'Text of message';
        }
        /*transporter.sendMail({
         from: from,
         address: userEmail,
         to: to,
         subject: subject,
         text: text
         //html: '<i>html</i>'
         }, function(error, info){
         if (error) {
         console.log(error);
         } else {
         console.log('Message sent: ' + info.response);
         }
         });*/
    }

    function createSystemMsg(data) {
        var fullDate = getFullDate(new Date()),
            date_msg = dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss"),
            system = "Система",
            message,
            type = data.type,
            orderId = data.orderId,
            channel = data.channel;
        if (type == 'confirm-work') {
            message = "System msg - confirm work";
        } else if (type == 'confirm_author_bid') {
            message = "System msg - confirm author bid";
        } else if (type == 'cancel_author_bid') {
            message = "System msg - cancel author bid";
        } else if (type == 'create_auction_bid') {
            message = "System msg - create auction bid";
        } else if (type == 'hide_author_bid') {
            message = "System msg - hide author bid";
        }
        connection.insert('webchat_message', {
            message: message,
            date_write: date_msg,
            user_id: 17,
            user_order_id: orderId,
            channel: channel
        }, function(error) {
            if (error) {throw(error)}
            client.to(channel).emit("show system message", {login: system, date_msg: fullDate, message: message});
            client.emit("show system message", {login: system, date_msg: fullDate, message: message});
        });
    }

    function createErrorLog(data) {
        console.dir(data);
        connection.insert('error_log', {
            content: data.type,
            user_id: data.userId,
            token: data.token,
            channel: data.channel
        }, function(error) {
            if (error) {throw(error)}
        })
    }

    function checkIsValidateMessage(a) {
        var patternCheckEmail = /([a-z0-9_-]+\.)*[a-z0-9_-]+(@|sobaka|_|собака)[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}/i,
            patternCheckPhone = /((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,16}/i;
        return patternCheckEmail.test(a) || patternCheckPhone.test(a);
    }

    function replaceIncorrectMessage(a) {
        var patternCheckEmail = /([a-z0-9_-]+\.)*[a-z0-9_-]+(@|sobaka|_|собака)[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}/i,
            patternCheckPhone = /((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,16}/i,
            r = a.replace(patternCheckEmail, "Warning");
        return r.replace(patternCheckPhone, " Warning ");
    }
});

function getFullDate(d) {
    var year, month, day, min, sec, hour;
    year = String(d.getFullYear());
    month = String(d.getMonth() + 1);
    day = String(d.getDate());
    min = String(d.getMinutes());
    hour = String(d.getHours());
    sec = String(d.getSeconds());
    if (sec.length == 1) {
        sec = "0" + sec;
    }
    if (min.length == 1) {
        min = "0" + min;
    }
    if (hour.length == 1) {
        hour = "0" + hour;
    }
    if (month.length == 1) {
        month = "0" + month;
    }
    if (day.length == 1) {
        day = "0" + day;
    }
    return day + "." + month + "." + year + ' ' + hour + ':' + min + ':' + sec;
}
// client.to('User_4666').emit('response', {message: 'HELLO', from: 'WORLD'});
//});