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

var MongoClient = require('mongodb').MongoClient, format = require('util').format, userListDB, chatDB;

MongoClient.connect('mongodb://127.0.0.1:27017', function (err, db) {
    if (err) {throw err}
    userListDB = db.collection('users');
    chatDB = db.collection('chat');
});

//io.sockets.in('User_4666').emit('response', {msg: 'HELLO', from: 'WORLD'});

io.sockets.on('connection', function (client) {

    client.on('message', function (data) {
        try {
            //console.log(message.name);
            client.emit('message', data);
            client.broadcast.emit('message', data);
            var time = new Date().getTime();
            // saves messages
            chatDB.insert({message: data.message, from: data.name, time: time}, {w:1}, function (err) {
                if (err) {throw err}
            });
        } catch (e) {
            console.log(e);
            client.disconnect();
        }
    });

    /*client.on('join', function (data) {
        client.join(data.name);
        console.log(client.adapter);
    });*/

    client.on('log_connection', function (data) {
        //console.log(client.id);
        //chatDB.find().toArray(function(error, entries) {
        chatDB.find({}).toArray(function(error, entries) {
            if (error) {throw error}
            client.emit('response', entries);
        });
    });

   // client.to('User_4666').emit('response', {message: 'HELLO', from: 'WORLD'});
});