var PORT = 8008;

var options = {
//    'log level': 0
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

connection.queryHash(
    'SELECT * FROM webchat_message', [],
    function(err, result) {
        //console.dir({rows: result});
    }
);

// Release connection
//connection.end();

//io.sockets.in('User_4666').emit('response', {msg: 'HELLO', from: 'WORLD'});

io.sockets.on('connection', function (client) {
    client.on('message', function (data) {
        try {
            //console.log(message.name);
            client.emit('message', data);
            client.broadcast.emit('message', data);
            //var time = new Date().getTime();
            var date = new Date();
            // saves messages
            /*chatDB.insert({message: data.message, from: data.name, time: time}, {w:1}, function (err) {
                if (err) {throw err}
            });*/
            connection.insert('webchat_message', {
                message: data.message,
                date_write: date,
                user_id: 1,
                user_order_id: 6
            }, function(err, recordId) {
                console.dir({insert:recordId});
            });
        } catch (e) {
            console.log(e);
            client.disconnect();
        }
    });

    client.on('join', function (data) {
        client.join(data.name);
        console.log(client.adapter);
    });

   /* client.on('log_connection', function (data) {
        //console.log(client.id);
        //chatDB.find().toArray(function(error, entries) {
        chatDB.find({}).toArray(function(error, entries) {
            if (error) {throw error}
            client.emit('response', entries);
        });
    });*/

   // client.to('User_4666').emit('response', {message: 'HELLO', from: 'WORLD'});
});