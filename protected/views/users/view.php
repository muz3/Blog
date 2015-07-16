


<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js" ></script>
<script src="https://cdn.socket.io/socket.io-1.0.0.js"></script>

<script type="text/javascript">

	//socket setup
	//type: ( 0:chat_request, 1:chat )
	var TYPING_TIMER_LENGTH = 500; // ms

	var connected = false;
	var socketConnected = false;
	var typing = false;
	var lastTypingTime;

	if ( socket ){
		console.log('test socket true');
		//socket.emit("chat_request", "test" );
	}
	else {
		var socket = io.connect('http://10.0.0.228:3001');
		connected = true;
		socketConnected = true;
		//var socket = io.connect('http://localhost:3001');
		//console.log('test socket false');
		
	}	
		
	var userID = "<?php echo Yii::app()->session['uid'] ?>";  //is there any better way to pass php variable to javascirpt???
	var receID = "<?php echo $receiver['id'] ?>";             //hasn't used
	// var unixTime = (new Date).getTime();
    //send chat request to server 	
    if ( socketConnected ) socket.emit('chat_request', {type:0, sender: userID , receiver: receID, message:"", time: (new Date).getTime() }  );

    //------
    //open its own socket for myself.
    socket.on(userID, function(data){
    	console.log('received incoming message');
    	//when received messages, check and display that message
    	if ( data['type'] != undefined && data['type'] == 1 ){
    		//display the received chat message
    		//$('#message-display').append( "<div class='message'>testing, this is received</div>" );
    		$( '.hidden-message .sender'  ).text( data['sender'] );
	    	$( '.hidden-message  .content'  ).text( data['message'] );
	    	$( '.hidden-message  .time'  ).text( data['time'] );
	    	var $div = $('.hidden-message');
	    	var $tmp = $div.clone().prop('class', 'message' );
	    	$('#message-display').append($tmp);

    	}
    	else if (  data['type'] != undefined && data['type'] == 2 ) {

    		//$("input#inputMessage").attr("placeholder", data['message']);
    		$('div#hidden-typing-notice').text(data['message']);

    	}
    	else if (  data['type'] != undefined && data['type'] == 3 ) {

    		//$("input#inputMessage").attr("placeholder", "");
    		$('div#hidden-typing-notice').text("");

    	}

    })


	//alert("hi");
	$(document).ready(function() {

		$('#chat-form').on('submit', function (e) {
			var formData = $(this).serializeArray();    	
    		sendMessage(formData);

    		$('#inputMessage').val("");
    		//e.stopPropagation();
    		return false;
		});


		$('input#inputMessage').on('input', function (e) {
			// var uid = "<?php echo Yii::app()->session['uid'] ?>";
     		//alert('uid: '+uid);	
    		updateTyping();

		});

		$('input#inputMessage').on('click', function (e) {
			//$('input#inputMessage').focus();
    		updateTyping();
		});


	});

	

    function sendMessage(formData){
    	console.log(' send message');
    	if ( formData[0]['name'] == 'type') {
    		//
    	}

    	var unixTime = (new Date).getTime() ;
    	var dataToSend = { type: formData[0]['value'], sender: formData[1]['value'], receiver: formData[2]['value'], message: formData[3]['value'], time: unixTime };
    	// console.log(dataToSend);

    	//clone hidden field
    	
    	$( '.hidden-message .sender'  ).text( formData[1]['value'] );
    	$( '.hidden-message  .content'  ).text( formData[3]['value'] );
    	$( '.hidden-message  .time'  ).text( unixTime );
    	var $div = $('.hidden-message');
    	var $tmp = $div.clone().prop('class', 'message' );
    	$('#message-display').append($tmp);

    	//send via socket
    	//socket.emit('chat_message', dataToSend);
    	if ( socketConnected ) socket.emit(formData[1]['value'], dataToSend);
    	//to stop typing
    	if ( socketConnected ) socket.emit(userID, {type: 3, sender:userID, receiver:receID, message: "stop typing", time: ""} );
    	typing = false;
    	//
    }



    
    //active and inactive feature to save resouces on our node server, so it can be more scale
	var IDLE_TIMEOUT = 30; //seconds
	var _idleSecondsCounter = 0;
	document.onclick = function() {
	    _idleSecondsCounter = 0;
	};
	document.onmousemove = function() {
	    _idleSecondsCounter = 0;
	};
	document.onkeypress = function() {
	    _idleSecondsCounter = 0;
	};
	window.setInterval(CheckIdleTime, 1000);

	function CheckIdleTime() {
	    _idleSecondsCounter++;

	    //handle overflow
	    if ( _idleSecondsCounter == Number.MAX_VALUE) _idleSecondsCounter = IDLE_TIMEOUT + 1;

	    var oPanel = document.getElementById("SecondsUntilExpire");
	    if (oPanel){
	    	oPanel.innerHTML = (IDLE_TIMEOUT - _idleSecondsCounter) + "";
	    }
	        
	    if (_idleSecondsCounter >= IDLE_TIMEOUT) {
	        // alert("Time expired!");
	        // document.location.href = "logout.html";
	        //socket = "";

	        if ( socketConnected ){
	        	// socket.disconnect();
	         //    console.log(socket);

	         socket.io.disconnect();
	            socketConnected = false;
	        }
	        
	    }
	    else{
	    	
	    	if ( !socketConnected ) {
	    		console.log(socket.io);
	    		socket.io.reconnect();
	    		socketConnected = true;
	    		if ( socketConnected ) socket.emit('chat_request', {type:0, sender: userID , receiver: receID, message:"", time: (new Date).getTime() }  );

	    		//socket.socket.reconnect();
	    		

	    	}
	    	
	    }
	}




    //-----------------------------
    // Keyboard events
    // $(document).keydown(function(e) {
    //     //console.log(e);
       
    //     //if the user pressed 'D'
    //     if(e.keyCode == 13) {
    //             console.log('caught');
    //     }
    // })

    //    $window.keydown(function (event) {
    //    // Auto-focus the current input when a key is typed
	//     if (!(event.ctrlKey || event.metaKey || event.altKey)) {
	//     	$currentInput.focus();
	//     }
	//     // When the client hits ENTER on their keyboard
	//     if (event.which === 13) {
	//     	if (username) {
	//     		sendMessage();
	//     		socket.emit('stop typing');
	//     		typing = false;
	//     	} else {
	//     		setUsername();
	//     	}
	//     }
	// });


    // Updates the typing event
	function updateTyping () {
	 	if (connected) {
	 		if (!typing) {
	 			typing = true;
	 			//socket.emit('typing');	 			
	 			if ( socketConnected ) socket.emit(userID, {type: 2, sender:userID, receiver:receID, message: userID+" is typing...", time: ""} );
	 		}
	 		lastTypingTime = (new Date()).getTime();

	 		setTimeout(function () {
	 			var typingTimer = (new Date()).getTime();
	 			var timeDiff = typingTimer - lastTypingTime;
	 			if (timeDiff >= TYPING_TIMER_LENGTH && typing) {
	 				//socket.emit('stop typing');
	 				if ( socketConnected ) socket.emit(userID, {type: 3, sender:userID, receiver:receID, message: "stop typing", time: ""} );
	 				typing = false;
	 			}
	 		}, TYPING_TIMER_LENGTH);
	 	}
	}


</script>




<div class="container">
	<h1>Question Page #</h1>
	<div class="row-fluid">
		<div class=" col-md-8 col-xs-8" >


			<p id="SecondsUntilExpire" style="background:yellow;"></P>
			<div id="message-display">

				<div class="message">
					<p class="question"> Question </P>
				</div>

				

			</div>

			
			<div class ="hidden-message">
					<p class="sender">   </p>
					<p class="time">  </p>
					<p class="content">  </p>
			</div>




			<div id="hidden-typing-notice"></div>
		
		</div>


		
	</div>


		

		<form id="chat-form" action="POST">
		
		 <div class="form-group" >	    	
		    
		    <input type="hidden" class="reset" name="type" value="1">
		    <input type="hidden" class="reset" name="userID" value="<?= Yii::app()->session['uid'] ?>">
		    <input type="hidden" class="reset" name="receID" value="<?= $receiver['id'] ?>">
		    <input class="form-control" class="reset" id='inputMessage' name="message" value="">
		    <button type="submit" id ="message-button" class="btn btn-default pull-right">Send</button>
		    
		 </div>
		 
		 

	</from>



</div>
 




