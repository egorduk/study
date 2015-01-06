var PORT = 8008;

var options = {
   //'log level': 1
};

var express = require('express');
var app = express();
var http = require('http');
var server = http.createServer(app);
var io = require('socket.io').listen(server, options);
server.listen(PORT);

app.use('/static', express.static(__dirname + '/static'));

app.get('/', function (req, res) {
    res.sendfile(__dirname + '/index.html');
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

var mysql = require('mysql'), mysqlUtilities = require('mysql-utilities');
var connection = mysql.createConnection({
    host:     'localhost',
    user:     'root',
    password: '',
    database: 'study'
});

var arr = [];

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

//io.sockets.in('User_4666').emit('response', {msg: 'HELLO', from: 'WORLD'});

io.sockets.on('connection', function (client) {
    var clientID = (client.id).toString();
    arr[clientID] = clientID;

   // client.join("room");

    client.on("send message", function (data) {
        try {
            var date = new Date(), room = data.orderId;
            //var date_write = Date_toYMD(date);
           // console.log(date.getTimezoneOffset());
            connection.insert('webchat_message', {
                message: data.message,
                date_write: date,
                user_id: data.userId,
                user_order_id: data.orderId
            }, function(err, recordId) {
                connection.queryRow(
                    'SELECT login AS user_login FROM user WHERE id = ' + data.userId, function(error, row) {
                        if (error) {throw error}
                        client.emit("show new message", {date_write: date, message: data.message, user_login: row.user_login});
                        client.to(room).emit("show new message", {date_write: date, message: data.message, user_login: row.user_login});
                       // client.broadcast.emit("show new message", {date_write: date, message: data.message, user_login: row.user_login});
                    }
                );
                console.dir({insert: recordId});
            });
        } catch (e) {
            console.log(e);
            client.disconnect();
        }
    });

    client.on("join to room", function(data) {
        var room = data.room;
        console.log("Connected - " + data.name);
        client.join(room);
        client.to(room).emit("user in room", {name: data.name, userId: clientID});
    });

    client.on("request disconnect user", function(data) {
        var room = data.room;
        console.log("Disconnected - " + data.name);
        client.to(room).emit("response disconnect user", {name: data.name});
    });

    client.on("request view current online", function(data) {
        var room = data.room;
        client.to(room).emit("response view current online");
    });

    client.on("request user write", function(data) {
        var room = data.room;
        client.to(room).emit("response user write");
    });

    /*client.on("join", function (data) {
       // arrId[i] = clientID;
        //i++;

        //for (var s in io.sockets.sockets) {
            /*socket.get('nickname', function(nickname) {
                console.log(nickname);
            });*/
            //console.log(s);
        //}

        //arrId[clientID] = client;
        //console.log(io.sockets.clients());

       // console.log(io.of('/').connected);

        //var clients = findClientsSocket('room') ;
        //console.log(clients);

        //io.sockets.sockets['nickname'] = data.name;
        //console.log(io.sockets.sockets[data.name]);

      //  client.to('room').emit('join user', {name: data.name, userId: clientID});
    //});

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

    /*client.on('log connecting', function () {
        //client.emit('test', arrId);
        //console.log(arrId);
    });*/

    /*client.on('user online response', function () {
        client.to('room').emit('test1', clientID);
    });*/

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

    function findClientsSocket(roomId, namespace) {
        var res = []
            , ns = io.of(namespace ||"/");    // the default namespace is "/"

        if (ns) {
            for (var id in ns.connected) {
                if(roomId) {
                    var index = ns.connected[id].rooms.indexOf(roomId) ;
                    if(index !== -1) {
                        res.push(ns.connected[id]);
                    }
                } else {
                    res.push(ns.connected[id]);
                }
            }
        }
        return res;
    }

   // client.to('User_4666').emit('response', {message: 'HELLO', from: 'WORLD'});
});