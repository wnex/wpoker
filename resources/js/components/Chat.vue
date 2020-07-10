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
						aria-label="Enter your name"
					></textarea>
					<div class="input-group-append">
						<button type="submit" class="btn btn-secondary">Send</button>
					</div>
				</div>
			</form>
		</div>
		<div v-if="messages.length" class="chat-block" aria-live="polite" aria-atomic="true">
			<div class="mb-2" style="display: flex; flex-direction: column;">
				<button type="submit" @click="clearAll()" class="btn btn-outline-secondary">Clear</button>
			</div>
			<div id="message-block" class="message-block">
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
						<button type="button" @click="remove(message.id)" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="toast-body"><vue-markdown :html="false" :anchorAttributes="{target: 'blank', rel: 'nofollow'}">{{message.message}}</vue-markdown></div>
				</div>
				<div id="anchor"></div>
			</div>
		</div>
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
		props: ['socket', 'hash'],

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
			if (localStorage.messages) {
				this.messages = JSON.parse(localStorage['messages-'+this.hash]);
				this.save();
			}

			this.socket.listener('room.chat.message', (data) => {
				this.messages.push({
					id: data.id,
					author_id: data.author_id,
					author_name: data.author_name,
					message: data.message,
					date: moment().format(),
				});

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
						'room': this.hash,
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
				localStorage['messages-'+this.hash] = JSON.stringify(this.messages);

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
		overflow-y: hidden;
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
</style>
