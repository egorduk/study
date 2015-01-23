var PORT = 8008,  options = {
   //'log level': 1
};
var express = require('express'), app = express(), http = require('http'), server = http.createServer(app), io = require('socket.io').listen(server, options);
server.listen(PORT);
app.use('/static', express.static(__dirname + '/static'));
app.get('/', function (req, res) {
    //res.sendfile(__dirname + '/index.html');
});

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
            var date = new Date(), room = arrRoom[clientID], mode = data.mode, userId = data.userId;
            console.dir(data);
            //var date_write = Date_toYMD(date);
           // console.log(date.getTimezoneOffset());
            connection.insert('webchat_message', {
                message: data.message,
                date_write: date,
                user_id: userId,
                user_order_id: room
            }, function(error, recordId) {
                if (error) {console.log(error)}
                connection.queryRow(
                    'SELECT login AS user_login FROM user WHERE id = ' + userId, function(error, row) {
                        if (error) {throw error}
                        var userLogin = row.user_login, message = data.message;
                        client.emit("show new message", {date_write: date, message: message, user_login: userLogin});
                        client.to(room).emit("show new message", {date_write: date, message: message, user_login: userLogin});
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
                            connection.insert('ban_message', {
                                message_id: recordId,
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
                            transporter.sendMail({
                                from: 'Test email <egorduk91@gmail.com>',
                                address: userEmail,
                                to: 'a_1300@mail.ru',
                                subject: 'New message',
                                text: 'New message! User login - ' + userLogin + ', message - ' + message
                                //html: '<i>html</i>'
                            }, function(error, info){
                                if (error) {
                                    console.log(error);
                                } else {
                                    console.log('Message sent: ' + info.response);
                                }
                            });
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

    client.on("join to room", function(data) {
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
            'SELECT wm.id, message, date_write, login AS user_login FROM webchat_message AS wm INNER JOIN user ON wm.user_id = user.id' +
                ' WHERE wm.user_order_id = ' + data.orderId,
            function(error, rows) {
               // console.dir(rows);
               // console.dir({queryHash:row});
                //console.dir(rows);
                if (error) {throw error}
                client.emit('response get all messages', rows);
            }
        );
    });

    /*function Date_toYMD(d) {
        var year, month, day;
        year = String(d.getFullYear());
        month = String(d.getMonth() + 1);
        if (month.length == 1) {
            month = "0" + month;
        }
        day = String(d.getDate());
        if (day.length == 1) {
            day = "0" + day;
        }
        return year + "-" + month + "-" + day;
    }*/

   // client.to('User_4666').emit('response', {message: 'HELLO', from: 'WORLD'});
});