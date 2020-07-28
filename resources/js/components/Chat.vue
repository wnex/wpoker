<template>
	<div>
		<h4 class="d-flex justify-content-between align-items-center mb-2">
			<span class="text-muted">Chat</span>
		</h4>
		<div class="card p-2 mb-4">
			<form @submit.prevent="send">
				<div class="input-group">
					<textarea
						rows="1"
						@keydown.enter.exact.prevent="send"
						class="form-control"
						v-model="message"
						placeholder="Enter your message"
					></textarea>
					<div class="input-group-append">
						<button type="submit" class="btn btn-secondary">Send</button>
					</div>
				</div>
			</form>
		</div>
		<transition name="fade">
			<div v-if="messages.length" class="chat-block" aria-live="polite" aria-atomic="true">
				<div class="mb-2" style="display: flex; flex-direction: column;">
					<button type="submit" @click="clearAll()" class="btn clear-all btn-outline-secondary">Clear</button>
				</div>
				<div id="message-block" class="message-block">
					<transition-group name="list">
						<div
							v-for="message in messages"
							:key="message.id"
							class="toast show"
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
									title="Hide this notification"
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
	import moment from 'moment';

	import VueMarkdown from 'vue-markdown';

	import Prism from 'prismjs';
	import 'prismjs/themes/prism.css';

	import 'prismjs/components/prism-markup-templating.js';
	import 'prismjs/components/prism-php';
	import 'prismjs/components/prism-bash';

	export default {
		props: ['socket', 'room'],

		components: {
			Timer,
			VueMarkdown,
		},

		data: () => ({
			message: '',
			messages: [],
		}),

		created: function() {
			setTimeout(this.scrollToBottom, 500);
		},

		mounted: function() {
			if (localStorage['messages-'+this.room.hash]) {
				this.messages = JSON.parse(localStorage['messages-'+this.room.hash]);
				for (var i = 0; i < this.messages.length; i++) {
					if (this.messages[i].notification) {
						setTimeout(() => {
							this.remove(data.id);
						}, 5000);
					}
				}
				this.save();
			}

			this.socket.listener('room.chat.message', (data) => {
				this.messages.push({
					id: data.id,
					author_id: data.author_id,
					author_name: data.author_name,
					message: data.message,
					date: moment().format(),
					notification: data.notification,
				});

				if (data.notification) {
					setTimeout(() => {
						this.remove(data.id);
					}, 10000);
				}

				this.save();

				this.$nextTick(() => {
					Prism.highlightAll();
				});
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
				this.messages = [];
				this.save();
			},

			getDate(date) {
				moment.locale('en');
				return moment(date).fromNow();
			},
		},

		computed: {
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
	}

	@media (max-width: 576px) {
		.toast, .chat-block, .message-block {
			min-width: calc(100% - 20px);
		}
		.toast {
			max-width: 100%;
		}
	}

	textarea {
		min-height: 38px;
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