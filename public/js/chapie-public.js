(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

		const chat_btn = $(".chapie-chat .icon");
		const chat_box = $(".chapie-chat .chapie-chat-box");
		const chat_user = $(".chapie-chat .chatuser");
		const chat_back_user = $(".chapie-chat .backchatuser");
		const user_list = $(".chapie-chat .people-list");
		const chat_start = $(".chapie-chat .chat");
		const chat_with = $(".chat-header .chat-with");
		const chat_message_count = $(".chat-header .chat-num-messages");
		const chat_with_status = $(".chapie-chat .status");
		const chat_history_box = $("#chat-history-box");

	
		chat_btn.click(() => {
			chat_btn.toggleClass("expanded");
			setTimeout(() => {
				chat_box.toggleClass("expanded");
			}, 100);
		});
	  
		chat_user.click(function() {
			const userId = $(this).attr('user-id');
			$(chat_history_box).html('');
			
			$.ajax({
				url: chapie_ajax_object.ajax_url,
				type: 'POST',
				data: {
					action: 'chapie_get_user_data',
					security: chapie_ajax_object.nonce,
					user_id: userId
				},
				success: function(response) {
					if (response.success) {
						console.log('User Data:', response.data);
						$(chat_with).text(response.data.username);
						$(chat_with).attr('user-id', response.data.ID);

						$(this).toggleClass("expanded");
						
						setTimeout(() => {
							chat_start.toggleClass("expanded");
							user_list.toggleClass("expanded");
						}, 800);
						// Handle the user data (e.g., display it on the page)
					} else {
						console.log('Error:', response.data);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log('AJAX error:', textStatus);
				}
			});
		});
	
		chat_back_user.click(() => {
			chat_back_user.toggleClass("expanded");
			setTimeout(() => {
				chat_start.toggleClass("expanded");
				user_list.toggleClass("expanded");
			}, 100);
		});
	
		var chat = {
			messageToSend: '',
			init: function() {
				this.cacheDOM();
				this.bindEvents();
				this.render();
			},
			cacheDOM: function() {
				this.$chatHistory = $('.chat-history');
				this.$button = $('.send-message');
				this.$textarea = $('#message-to-send');
				this.$chatHistoryList = this.$chatHistory.find('ul');
			},
			bindEvents: function() {
				this.$button.on('click', this.addMessage.bind(this));
				this.$textarea.on('keyup', this.addMessageEnter.bind(this));
			},
			render: function() {
				this.scrollToBottom();
				if (this.messageToSend.trim() !== '') {
					var template = Handlebars.compile($("#message-template").html());
					var context = {
						messageOutput: this.messageToSend,
						time: this.getCurrentTime()
					};
		
					this.$chatHistoryList.append(template(context));
					this.scrollToBottom();
					this.$textarea.val('');
		
					// Send the message via AJAX
					this.sendMessageToServer(this.messageToSend);
				}
			},
			addMessage: function() {
				this.messageToSend = this.$textarea.val();
				this.render();
			},
			addMessageEnter: function(event) {
				if (event.keyCode === 13) {
					this.addMessage();
				}
			},
			scrollToBottom: function() {
				this.$chatHistory.scrollTop(this.$chatHistory[0].scrollHeight);
			},
			getCurrentTime: function() {
				return new Date().toLocaleTimeString().
					replace(/([\d]+:[\d]{2})(:[\d]{2})(.*)/, "$1$3");
			},
			sendMessageToServer: function(message) {
				var reciverUser = $(chat_with).attr('user-id');
				$.ajax({
					url: chapie_ajax_object.ajax_url,
					type: 'POST',
					data: {
						action: 'chapie_send_message',
						security: chapie_ajax_object.nonce,
						reciever: reciverUser,
						message: message
					},
					success: function(response) {
						if (response.success) {
							var templateResponse = Handlebars.compile($("#message-response-template").html());
							var contextResponse = {
								response: response.data.message,
								time: this.getCurrentTime()
							};
							
							setTimeout(function() {
								// this.$chatHistoryList.append(templateResponse(contextResponse));
								this.scrollToBottom();
							}.bind(this), 500);
						} else {
							console.log('Error:', response.data);
						}
					}.bind(this),
					error: function(jqXHR, textStatus, errorThrown) {
						console.log('AJAX error:', textStatus);
					}
				});
			}
		};
	  
	  chat.init();
	  
	  var searchFilter = {
		options: { valueNames: ['name'] },
		init: function() {
		  var userList = new List('people-list', this.options);
		  var noItems = $('<li id="no-items-found">No items found</li>');
		  
		  userList.on('updated', function(list) {
			if (list.matchingItems.length === 0) {
			  $(list.list).append(noItems);
			} else {
			  noItems.detach();
			}
		  });
		}
	  };
	  
	  searchFilter.init();


	  setInterval(() => {
		var incoming_id = $(chat_with).attr('user-id');
		if (chat_box.hasClass("expanded")) {
			let xhr = new XMLHttpRequest();
			xhr.open("POST", chapie_ajax_object.ajax_url, true);
			xhr.onload = () => {
				if(xhr.readyState === XMLHttpRequest.DONE) {
					if(xhr.status === 200) {
						let data = xhr.responseText;
						let chatBox = document.getElementById('chat-history-box');
						chatBox.innerHTML = data;
						if(!chatBox.classList.contains("active")) {
							// scrollToBottom();
						}
						var messagesnum = jQuery(".chat-history-box .message_count").val();
						if(messagesnum > 0){
							jQuery(".chat-about .chat-num-messages").text(messagesnum + ' messages');
						}else{
							jQuery(".chat-about .chat-num-messages").text('');
						}
					}
				}
			};
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhr.send("action=get_chapie_chat&incoming_id=" + incoming_id);
		}
	}, 1000);
	  

})( jQuery );
