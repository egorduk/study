var PORT = 8008,  options = {
   //'log level': 1
};
var express = require('express'),
    http = require('http'),
    path = require('path'),
    app = express(),
    server = http.createServer(app),
    io = require('socket.io').listen(server, options);
server.listen(PORT);
/*app.use('/static', express.static(__dirname + '/static'));
app.get('/', function (req, res) {
    //res.sendfile(__dirname + '/index.html');
});*/

//var log4js = require('log4js');
//var logger = log4js.getLogger();
//var winston = require('winston');

/*var MongoClient = require('mongodb').MongoClient, format = require('util').format, userListDB, chatDB;
MongoClient.connect('mongodb://127.0.0.1:27017', function (err, db) {
    if (err) {throw err}
    userListDB = db.collection('users');
    chatDB = db.collection('chat');
});*/

var nodemailer = require('nodemailer'), userEmail = 'egorduk91@gmail.com', passEmail = 'rezistor';

var mysql = require('mysql'), mysqlUtilities = require('mysql-utilities'), connection = mysql.createConnection({
    host:     'localhost',
    user:     'root',
    password: '',
    database: 'study'
});

var arrName = [], arrRoom = [];

connection.connect(function (err, db) {
    if (err) {throw err}
    /*if (err) {
        var error = new Error('something broke');
        console.error( error.stack );
    }*/
   // userListDB = db.collection('users');
   // chatDB = db.collection('chat');
});

/*connection.queryRow(
    'SELECT * FROM user where LanguageId=?', [3],
    function(err, row) {
        console.dir({queryRow:row});
    }
);*/

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

io.sockets.on('connection', function (client) {
    var clientID = (client.id).toString();

    client.on("send message", function (data) {
        try {
            var date = new Date(), room = arrRoom[clientID], mode = data.mode, userId = data.userId, responseLogin = data.responseLogin, fullDate = getFullDate(date);
            console.dir(data);
            connection.insert('webchat_message', {
                message: data.message,
               // date_write: date,
                user_id: userId,
                user_order_id: room
            }, function(error, recordId) {
                if (error) {console.log(error)}
                connection.queryRow(
                    'SELECT login AS user_login FROM user WHERE id = ' + userId, function(error, row) {
                        if (error) {throw error}
                        var writerLogin = row.user_login, message = data.message;
                        client.emit("show new message", {date_write: fullDate, message: message, user_login: writerLogin});
                        client.to(room).emit("show new message", {date_write: fullDate, message: message, user_login: writerLogin});
                        var transporter = nodemailer.createTransport({
                            service: 'gmail',
                            auth: {
                                user: userEmail,
                                pass: passEmail
                            }
                        }, function(error) {
                            if (error) {console.log(error)}
                        });
                        if (mode) {
                            // Send about denied rules
                            connection.insert('ban_message', {
                                message_id: recordId
                            }, function(error) {
                                if (error) {console.log(error)}
                            });
                            transporter.sendMail({
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
                            });
                        } else {
                            // Send new email to other user
                            connection.queryRow(
                                'SELECT email AS user_email FROM user WHERE login = "' + responseLogin + '"', function(error, row) {
                                    if (error) {throw error}
                                    console.dir(row);
                                    /*transporter.sendMail({
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
                                    });*/
                                }
                            )
                        }
                    }
                );
                console.dir({insert: recordId});
            });
        } catch (error) {
            console.log(error);
            client.disconnect();
        }
    });

    client.on("join to room", function(data, handshakeData, accept) {
        arrName[clientID] = data.name;
        arrRoom[clientID] = data.room;
        console.log("Connected - " + data.name + " to room - " + data.room);
        client.join(data.room);
        client.to(data.room).emit("user in room", {name: data.name});
    });

    client.on("disconnect", function() {
        var room = arrRoom[clientID], name = arrName[clientID];
        console.dir("Disconnected - " + name);
        client.to(room).emit("response disconnect user");
        delete arrRoom[clientID];
        delete arrName[clientID];
       // client.leave(room); //Rooms are left automatically upon disconnection.
    });

    client.on("request view current online", function() {
        var room = arrRoom[clientID];
        client.to(room).emit("response view current online");
    });

    client.on("request user write", function() {
        var room = arrRoom[clientID];
        client.to(room).emit("response user write");
    });

    client.on("get all messages", function (data) {
        connection.queryHash(
            'SELECT wm.id, message, DATE_FORMAT(date_write,"%d.%m.%Y %T") AS date_write, login AS user_login FROM webchat_message AS wm INNER JOIN user ON wm.user_id = user.id' +
                ' WHERE wm.user_order_id = ' + data.orderId,
            function(error, rows) {
                //console.dir(rows);
               // console.dir({queryHash:row});
                if (error) {throw error}
                client.emit('response get all messages', rows);
            }
        );
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
});

/*io.use(function(socket, next) {
    try {
        var data = socket.handshake || socket.request;
        if (! data.headers.cookie) {
            return next(new Error('Missing cookie headers'));
        }
        console.log('cookie header ( %s )', JSON.stringify(data.headers.cookie));
        var cookies = cookie.parse(data.headers.cookie);
        console.log('cookies parsed ( %s )', JSON.stringify(cookies));
        if (! cookies[COOKIE_NAME]) {
            return next(new Error('Missing cookie ' + COOKIE_NAME));
        }
        var sid = cookieParser.signedCookie(cookies[COOKIE_NAME], COOKIE_SECRET);
        if (! sid) {
            return next(new Error('Cookie signature is not valid'));
        }
        console.log('session ID ( %s )', sid);
        data.sid = sid;
        sessionStore.get(sid, function(err, session) {
            if (err) return next(err);
            if (! session) return next(new Error('session not found'));
            data.session = session;
            next();
        });
    } catch (err) {
        console.error(err.stack);
        next(new Error('Internal server error'));
    }
});*/