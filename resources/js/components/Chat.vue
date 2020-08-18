<template>
	<div>
		<h4 class="d-flex justify-content-between align-items-center mb-2">
			<span class="text-muted">Chat</span>
		</h4>
		<focusable class="pt-2 pl-2 pr-2 mb-4" ref="focusable">
			<form @submit.prevent="send">
				<div class="input-group">
					<textarea
						@keydown.enter.exact.prevent="send"
						@focus="$refs.focusable.focus()"
						class="message-box" ref="message"
						v-model="message"
						placeholder="Enter your message"
						v-autosize="message" rows="1"
					></textarea>
				</div>
			</form>

			<template v-slot:footer>
				<button class="btn btn-outline-info btn-sm" @click="showAll">
					<i class="fa fa-history" aria-hidden="true"></i>
				</button>
				<button @click.prevent="send" class="btn btn-primary btn-sm float-right" :disabled="message.length === 0">Send</button>
			</template>
		</focusable>

		<transition name="fade">
			<div v-if="showMessagesLength > 0" class="chat-block" aria-live="polite" aria-atomic="true">
				<div class="mb-2" style="display: flex; flex-direction: column;">
					<button type="submit" @click="clearAll()" class="btn clear-all btn-outline-secondary">Hide all</button>
				</div>
				<div id="message-block" class="message-block">
					<transition-group name="list">
						<div
							v-for="message in messages"
							v-if="message.isShow"
							:key="message.id"
							class="toast show mb-2"
							role="alert"
							aria-live="assertive"
							aria-atomic="true"
							data-autohide="false"
						>
							<div class="toast-header">
								<strong class="mr-auto">{{message.author_name}}</strong>
								<small class="text-muted"><timer :created="message.date"></timer></small>
								<button
									type="button"
									title="Remove this notification"
									@click="remove(message.id)"
									class="ml-2 mb-1 close"
									data-dismiss="toast"
									aria-label="Close"
								>
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="toast-body"><vue-markdown :html="false" :anchorAttributes="{target: 'blank', rel: 'nofollow'}">{{message.message}}</vue-markdown></div>
						</div>
					</transition-group>
					<div id="anchor"></div>
				</div>
			</div>
		</transition>
	</div>
</template>

<script>
	import Timer from '@/js/components/Timer';
	import Focusable from '@/js/components/Focusable';
	import moment from 'moment';

	import Vue from 'vue';
	import VueAutosize from 'autosize-vue';
	Vue.use(VueAutosize);

	import VueMarkdown from 'vue-markdown';

	import Prism from 'prismjs';
	import 'prismjs/themes/prism.css';

	import 'prismjs/components/prism-markup-templating.js';
	import 'prismjs/components/prism-php';
	import 'prismjs/components/prism-bash';

	export default {
		props: {
			socket: {
				type: Object,
				required: true,
			},
			room: {
				type: Object,
				required: true,
			},
		},

		components: {
			Timer,
			VueMarkdown,
			Focusable,
		},

		data: () => ({
			message: '',
			messages: [],
			sound: null,
		}),

		created: function() {
			this.sound = new Audio('../sounds/intuition.mp3');
			this.sound.volume = 0.3;
		},

		mounted: function() {
			if (localStorage['messages-'+this.room.hash]) {
				this.messages = JSON.parse(localStorage['messages-'+this.room.hash]);
				for (var i = 0; i < this.messages.length; i++) {
					if (this.messages[i].isShow) {
						if (this.messages[i].notification) {
							setTimeout(this.remove.bind(this, this.messages[i].id), 5000);
						} else {
							setTimeout(this.hide.bind(this, this.messages[i].id), 10000);
						}
					}
				}
				this.save();
			}

			this.socket.listener('room.chat.message', (data) => {
				if (this.messages.length >= 30) {
					this.messages.shift();
				}

				this.messages.push({
					id: data.id,
					author_id: data.author_id,
					author_name: data.author_name,
					message: data.message,
					date: moment().format(),
					notification: data.notification,
					isShow: true,
				});

				if (data.notification) {
					setTimeout(this.remove.bind(this, data.id), 10000);
				} else {
					setTimeout(this.hide.bind(this, data.id), 20000);
				}

				this.save();

				if (data.author_id !== this.room.clientId) {
					this.sound.pause();
					this.sound.currentTime = 0;
					this.sound.play();
				}

				this.$nextTick(() => {
					Prism.highlightAll();
					this.scrollToBottom();
				});
			});

			this.$nextTick(() => {
				Prism.highlightAll();
				this.scrollToBottom();
			});
		},

		methods: {
			send() {
				if (this.message !== '') {
					this.socket.send({
						'action': 'room.chat.send',
						'room': this.room.hash,
						'name': this.$root.name,
						'message': this.message,
					});
					this.message = '';
				}
			},

			remove(id) {
				for (var i = 0; i < this.messages.length; i++) {
					if (this.messages[i].id === id) {
						this.messages.splice(i, 1);
						this.save();
						break;
					}
				}
			},

			hide(id) {
				for (var i = 0; i < this.messages.length; i++) {
					if (this.messages[i].id === id) {
						this.messages[i].isShow = false;
						this.save();
						break;
					}
				}
			},

			save() {
				localStorage['messages-'+this.room.hash] = JSON.stringify(this.messages);

				this.$nextTick(() => {
					var block = document.getElementById('anchor');
					if (block !== null) {
						block.scrollIntoView();
					}
				});
			},

			scrollToBottom() {
				var block = document.getElementById('anchor');
				if (block !== null) {
					block.scrollIntoView();
				}
			},

			clearAll() {
				for (var i = 0; i < this.messages.length; i++) {
					this.messages[i].isShow = false;
				}
				this.save();
			},

			showAll() {
				for (var i = 0; i < this.messages.length; i++) {
					this.messages[i].isShow = true;
				}
				this.save();

				this.$nextTick(() => {
					Prism.highlightAll();
					this.scrollToBottom();
				});
			},

			getDate(date) {
				moment.locale('en');
				return moment(date).fromNow();
			},
		},

		computed: {
			showMessagesLength() {
				let length = 0;
				for (var i = 0; i < this.messages.length; i++) {
					if (this.messages[i].isShow) {
						length++;
					}
				}
				return length;
			}
		},
	}
</script>

<style scoped>
	.chat-block {
		z-index: 10;
		position: fixed;
		bottom: 10px;
		right: 10px;
		max-height: calc(100vh - 20px);
	}

	.message-block {
		overflow: hidden;
		max-height: calc(100vh - 58px);
		overflow-anchor: none;
	}

	#anchor {
		overflow-anchor: auto;
		height: 1px;
	}

	.toast {
		min-width: 350px;
		box-shadow: none;
	}

	.toast-header {
		padding: 4px 10px;
	}

	.toast-body {
		padding: 10px;
	}

	@media (max-width: 576px) {
		.toast, .chat-block, .message-block {
			min-width: calc(100% - 20px);
		}
		.toast {
			max-width: 100%;
		}
	}

	textarea.message-box {
		position: relative;
		flex: 1 1 auto;
		width: 1%;
		min-width: 0;
		overflow: hidden;
		overflow-wrap: break-word;
		min-height: 20px;
		line-height: 20px;
		font-size: 14px;
		border: none;
		outline: 0;
		resize: none;
	}

	.show-all {
		cursor: pointer;
	}

	.clear-all {
		background-color: rgba(255,255,255,.85);
		border: 1px solid rgba(0,0,0,.1);
		backdrop-filter: blur(10px);
	}
	.clear-all:hover {
		background-color: rgba(108,117,125,.85);
	}

	.fade-enter-active {
		transition: opacity .5s;
	}
	.fade-leave-active {
		transition: opacity .5s;
	}
	.fade-enter, .fade-leave-to {
		opacity: 0;
	}

	.list-enter-active, .list-leave-active {
		transition: transform .5s, opacity .5s;
	}
	.list-enter, .list-leave-to {
		opacity: 0;
		transform: translateX(30px);
	}
</style>
