
function SocketHelper(data) {
    this.channel = data.channel;
    this.orderId = data.orderId;
    this.userId = data.userId;
    this.clientLogin = data.clientLogin;
    this.authorLogin = data.authorLogin;
    this.authorId = data.authorId;
    this.userLogin = data.userLogin;
    this.join_to_channel_order_params  = { channel: this.channel, clientLogin: this.clientLogin, authorLogin: this.authorLogin, userLogin: this.userLogin, userId: this.userId };
    //this.join_to_channel_self_params  = { channel: this.channel };
    this.get_all_messages_params_client = { authorId: this.authorId };
    //this.get_all_messages_params_author = { userId: this.userId, clientId: this.clientId };

    this.addParams =  function (data) {
        this.mode = data.mode;
        this.messageText = data.messageText;
        this.orderNum = data.orderNum;
        this.create_new_message_params = { messageText: this.messageText, writerLogin: this.userLogin, userId: this.userId, mode: this.mode, responseLogin: this.authorLogin, orderId: this.orderId };
    };


}