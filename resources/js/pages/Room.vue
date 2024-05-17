<template>
	<div class="row">
		<div class="col-md-4 order-md-2 mb-4" style="position: inherit;">
			<users-list :socket="socket" :users="users" :room="room" :average="average"></users-list>

			<chat :socket="socket" :room="room"></chat>
		</div>
		<div class="col-md-8 order-md-1">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<div class="d-flex justify-content-between align-items-center gap-2">
					<h4 v-if="!changeRoomNameSwitcher">{{room.name}}</h4>
					<form v-if="room.isOwner && changeRoomNameSwitcher" @submit.prevent="saveRoomName">
						<text-error :text="changeRoomNameErrorText"></text-error>
						<div class="input-group">
							<input type="text" v-model="room.name" class="form-control" placeholder="Enter room name">
							<div class="input-group-append">
								<button type="submit" class="btn btn-secondary">Save</button>
							</div>
						</div>
					</form>

					<span
						v-if="room.name !== '' && room.isOwner && !changeRoomNameSwitcher"
						class="badge bg-secondary-subtle text-secondary-emphasis"
						@click="editRoomName"
						data-bs-toggle="modal" data-bs-target="#passwordModal"
						title="Edit room's name"
						role="button"
					>
						<i class="fa fa-fw fa-pencil" aria-hidden="true"></i>
					</span>
					<span v-if="room.name !== '' && room.isOwner && !changeRoomNameSwitcher" class="badge bg-secondary-subtle text-secondary-emphasis" role="button" @click="setPassword" title="Set password">
						<i class="fa fa-fw" :class="{'fa-unlock': !room.hasPassword, 'fa-lock': room.hasPassword}" aria-hidden="true"></i>
					</span>
					<span v-if="room.name !== '' && !room.isOwner" class="badge bg-secondary-subtle text-secondary-emphasis" role="button">
						<i class="fa" :class="{'fa-unlock': !room.hasPassword, 'fa-lock': room.hasPassword}" aria-hidden="true"></i>
					</span>
					<span v-if="room.name !== '' && !changeRoomNameSwitcher" class="badge bg-secondary-subtle text-secondary-emphasis" role="button" data-bs-toggle="modal" data-bs-target="#qrcodeModal">
						<i class="fa fa-fw fa-share" aria-hidden="true"></i>
					</span>


					<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<h1 class="modal-title fs-5" id="passwordModalLabel">New message</h1>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<form>
										<div class="mb-3">
											<label for="recipient-name" class="col-form-label">Recipient:</label>
											<input type="text" class="form-control" id="recipient-name">
										</div>
										<div class="mb-3">
											<label for="message-text" class="col-form-label">Message:</label>
											<textarea class="form-control" id="message-text"></textarea>
										</div>
									</form>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
									<button type="button" class="btn btn-primary">Save</button>
								</div>
							</div>
						</div>
					</div>

					<!-- Modal -->
					<div class="modal fade" id="qrcodeModal" tabindex="-1" aria-labelledby="qrcodeModalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<h1 class="modal-title fs-5" id="qrcodeModalLabel">Share QR Code</h1>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body" style="text-align: center;">
									<qrcode-vue :value="fullPageUrl" :size="300" level="H" />
									<a :href="fullPageUrl">{{ fullPageUrl }}</a>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
								</div>
							</div>
						</div>
					</div>

				</div>
				<span v-show="room.name !== ''" class="badge bg-secondary-subtle text-secondary-emphasis"><stopwatch ref="stopwatch"></stopwatch></span>
			</div>

			<cards ref="cards" :socket="socket" :canVote="canVote" :room="room" :users="users"></cards>

			<transition name="fade">
				<div v-if="room.isOwner" class="row ml-0">
					<div class="d-flex gap-2">
						<button v-if="stage === 0" class="btn mr-3 col-md-3 mb-3 btn-primary" @click="startVote">Start vote</button>
						<button v-if="stage === 1 || stage === 2" class="btn mr-3 mb-3 col-md-3 btn-primary" @click="resetVote">Reset</button>
						<button v-if="canSkipButton" class="btn mr-3 mb-3 col-md-3 btn-primary" @click="skipVote">Skip</button>
						<button v-if="canReVoteButton" class="btn mr-3 col-md-3 mb-3 btn-primary" @click="revote">Revote</button>
					</div>
				</div>
			</transition>

			<transition name="fade">
				<div v-if="room.task" class="row ml-0 mr-0 mt-3 mb-3 pt-0">
					<h4 class="d-flex justify-content-between align-items-center mb-3">
						<span>Сurrent task</span>
					</h4>

					<div class="mb-2 col-12">
						<vue-markdown
							:html="false"
							:anchorAttributes="anchorAttributes"
							:source="room.task.text"
							class="alert alert-success mb-0 p-2"
						></vue-markdown>
					</div>
				</div>
			</transition>

			<task-list ref="tasksList" :socket="socket" :room="room" @approve="onApprove"></task-list>
		</div>
	</div>
</template>

<script>
	import Stopwatch from '@/js/components/Stopwatch.vue';
	import Chat from '@/js/components/Chat.vue';
	import TaskList from '@/js/components/TaskList.vue';
	import Cards from '@/js/components/Cards.vue';
	import UsersList from '@/js/components/UsersList.vue';
	import TextError from '@/js/components/TextError.vue';
	import VueMarkdown from 'vue-markdown';
	import QrcodeVue from 'qrcode.vue'

	export default {
		props: ['socket', 'hash'],

		components: {
			Stopwatch,
			Chat,
			TaskList,
			Cards,
			UsersList,
			TextError,
			VueMarkdown,
			QrcodeVue,
		},

		data: () => ({
			name: '',
			room: {
				id: null,
				name: '',
				hash: null,
				task: null,
				clientId: null,
				isOwner: false,
				hasPassword: false,
				cardset: null,
			},
			users: [],
			stage: 0,
			average: null,
			canVote: false,
			soundStart: null,
			soundFinal: null,
			enterCountWrongPassword: 0,
			changeRoomNameSwitcher: false,
			changeRoomNameErrorText: null,
		}),

		created() {
			this.room.hash = this.hash;

			if (localStorage.name) {
				this.name = localStorage.name;
			}

			this.socket.onOpen(this.enterRoom);

			this.soundStart = new Audio('../sounds/all-eyes-on-me.mp3');
			this.soundStart.volume = 0.1;
			this.soundFinal = new Audio('../sounds/rhodesmas.mp3');
			this.soundFinal.volume = 0.1;
		},

		destroyed() {
			this.socket.offOpen(this.enterRoom);
			this.socket.offGroup('room');
		},

		beforeRouteUpdate(to, from, next) {
			this.enterRoom();
			next();
		},

		beforeRouteLeave(to, from, next) {
			this.leaveRoom();
			next();
		},

		mounted: function() {
			this.socket.group('room');

			this.socket.listener('room.entered.user', (data) => {
				this.users.push({
					id: data.id,
					name: data.name,
					isOwner: data.isOwner,
					hasVote: data.hasVote,
					active: true,
				});
			});

			this.socket.listener('room.left.user', (data) => {
				for (var i = 0; i < this.users.length; i++) {
					if (this.users[i].id === data.id) {
						break;
					}
				}

				this.users.splice(i, 1);
			});

			this.socket.listener('room.parameters', (data) => {
				this.room.id = data.id;
				this.room.name = data.name;
				this.room.isOwner = data.isOwner;
				this.room.clientId = data.client_id;
				this.room.task = data.task;
				this.room.hasPassword = data.hasPassword;
				this.room.cardset = data.cardset || {name: 'Default'};
				this.hasVote = data.hasVote;
				this.stage = data.stage;

				this.$refs.stopwatch.clearAll();
				this.$refs.stopwatch.startTimer(Date.now() - data.time*1000);

				this.$root.setTitle(this.room.name);
				this.setUsers(data.users);
				this.saveVisitHistory();
			});

			this.socket.listener('room.update', (data) => {
				this.room.name = data.name;
				this.room.hasPassword = data.hasPassword;
				this.room.cardset = data.cardset || {name: 'Default'};
				this.$root.setTitle(this.room.name);
				this.room.isOwner = data.owner_client_id == this.room.clientId;

				// Изменение овнера
				for (let i = 0; i < this.users.length; i++) {
					const user = this.users[i];
					if (user.id == data.owner_client_id && !user.isOwner) {
						this.users[i].isOwner = true;
						continue;
					}

					if (user.id != data.owner_client_id && user.isOwner) {
						this.users[i].isOwner = false;
						continue;
					}
				}
			});

			this.socket.listener('room.user.changeName', (data) => {
				for (var i = 0; i < this.users.length; i++) {
					if (this.users[i].id === data.id) {
						this.users[i].name = data.name;
						break;
					}
				}

				if (data.id === this.room.clientId) {
					this.name = data.name;
				}
			});

			this.socket.listener('room.user.changeHasVote', (data) => {
				for (var i = 0; i < this.users.length; i++) {
					if (this.users[i].id === data.id) {
						this.users[i].hasVote = data.hasVote;
						break;
					}
				}

				if (data.id === this.room.clientId) {
					this.hasVote = data.hasVote;
					this.changedStage();
				}
			});

			this.socket.listener('room.vote.start', (data) => {
				this.stage = data.stage;
				this.room.task = data.task;

				this.soundStart.pause();
				this.soundStart.currentTime = 0;
				this.soundStart.play();
			});

			this.socket.listener('room.vote.revote', (data) => {
				this.stage = data.stage;
				this.room.task = data.task;

				this.average = null;
				for (var i = 0; i < this.users.length; i++) {
					this.$set(this.users[i], 'vote', undefined);
					this.$set(this.users[i], 'voteView', undefined);
					this.$set(this.users[i], 'isVoted', false);
				}

				this.soundStart.pause();
				this.soundStart.currentTime = 0;
				this.soundStart.play();
			});

			this.socket.listener('room.vote', (data) => {
				for (var i = 0; i < this.users.length; i++) {
					if (this.users[i].id === data.id) {
						this.$set(this.users[i], 'isVoted', true);
						break;
					}
				}
			});

			this.socket.listener('room.vote.you', (data) => {
				for (var i = 0; i < this.users.length; i++) {
					if (this.users[i].id === data.id) {
						this.$set(this.users[i], 'vote', data.vote);
						this.$set(this.users[i], 'voteView', data.voteView);
						this.$set(this.users[i], 'isVoted', true);
						break;
					}
				}
			});

			this.socket.listener('room.kicked.you', (data) => {
				this.$router.push({name: 'home', query: {kicked: true}});
			});

			this.socket.listener('room.vote.final', (data) => {
				if (this.stage !== 2) {
					this.soundFinal.pause();
					this.soundFinal.currentTime = 0;
					this.soundFinal.play();
				}

				this.canVote = false;
				this.stage = 2;

				this.setUsers(data.users);
				this.average = this.getAverage();

				if (this.room.isOwner) {
					this.$refs.cards.startApprove();
				}
			});

			this.socket.listener('room.vote.reset', (data) => {
				this.stage = 0;
				this.average = null;
				for (var i = 0; i < this.users.length; i++) {
					this.$set(this.users[i], 'vote', undefined);
					this.$set(this.users[i], 'voteView', undefined);
					this.$set(this.users[i], 'isVoted', false);
				}
				this.room.task = null;
				this.$refs.stopwatch.clearAll();
			});

			this.socket.listener('room.wait.password', (data) => {
				if (data.wrong) {
					this.enterCountWrongPassword++;
				}

				if (this.enterCountWrongPassword > 2) {
					this.$router.push({name: 'home'});
					return;
				}

				this.$root.setTitle(data.name);

				let password = prompt(`Enter the password for the room "${data.name}"`);
				if (password !== null && password.length !== 0) {
					this.socket.send({
						'action': 'room.enter',
						'room': this.hash,
						'name': this.name,
						'uid': this.$root.getUser(),
						'password': password,
					});
				} else {
					this.$router.push({name: 'home'});
				}
			});

			this.socket.listener('room.user.disconnected', (data) => {
				for (var i = 0; i < this.users.length; i++) {
					if (this.users[i].id === data.id) {
						this.users[i].active = false;
						break;
					}
				}
			});

			this.socket.endGroup();
		},

		methods: {
			onApprove() {
				this.$refs.stopwatch.stopTimer();
			},

			enterRoom() {
				this.socket.send({
					'action': 'room.enter',
					'room': this.hash,
					'name': this.name,
					'uid': this.$root.getUser(),
					'password': '',
				});
			},

			leaveRoom() {
				this.socket.send({
					'action': 'room.leave',
					'room': this.hash,
				});
			},

			startVote() {
				this.socket.send({
					'action': 'room.vote.start',
					'room': this.hash,
				});
			},

			resetVote() {
				this.socket.send({
					'action': 'room.vote.reset',
					'room': this.hash,
				});

				this.$refs.cards.stopApprove();
			},

			skipVote() {
				if (confirm('Skip the current task?')) {
					this.socket.send({
						'action': 'room.task.approve',
						'id': this.room.task.id,
						'point': 0,
						'view': 'skipped',
					});

					this.$refs.cards.stopApprove();
				}
			},

			revote() {
				this.socket.send({
					'action': 'room.vote.revote',
					'room': this.hash,
				});

				this.$refs.cards.stopApprove();
			},

			getAverage() {
				let amount = 0,
					count = 0;
				for (var i = 0; i < this.users.length; i++) {
					if (this.users[i]['hasVote'] && this.users[i]['vote'] != 0) {
						amount += parseFloat(this.users[i]['vote']);
						count++;
					}
				}

				if (count === 0) return 0;

				return Math.round(amount/count*100) / 100;
			},

			setUsers(users) {
				this.users = users;

				for (var i = 0; i < this.users.length; i++) {
					if (this.users[i].id === this.room.clientId) {
						this.users[i].isSelf = true;
					}
				}
			},

			setPassword() {
				if (this.room.isOwner) {
					let password = prompt(`Enter the password for the room. Empty password will unlock the room.`);

					if (password !== null) {
						let promise = this.socket.request('room.update', {
							id: this.room.id,
							owner: this.$root.getUser(),
							password: password,
						});
					}
				}
			},

			editRoomName() {
				if (this.room.isOwner) {
					this.changeRoomNameSwitcher = true;
				}
			},

			saveRoomName() {
				if (this.room.isOwner) {
					if (this.room.name === null || this.room.name.length === 0) {
						this.changeRoomNameErrorText = 'Empty name.';
						return;
					}

					this.changeRoomNameErrorText = null;

					let promise = this.socket.request('room.update', {
						id: this.room.id,
						owner: this.$root.getUser(),
						name: this.room.name,
					}).then((result) => {
						this.changeRoomNameSwitcher = false;
					});
				}
			},

			changedStage() {
				switch(this.stage) {
					case 0:
						this.canVote = false;
						this.$refs.stopwatch.stopTimer();
						break;
					case 1:
						if (this.hasVote) {
							this.canVote = true;
						} else {
							this.canVote = false;
						}
						if (!this.$refs.stopwatch.isRunning) {
							this.$refs.stopwatch.startTimer();
						}
						break;
					case 2:
						this.canVote = false;
						break;
				}
			},

			saveVisitHistory() {
				let visitHistory = [];
				if (localStorage.visitHistory) {
					visitHistory = JSON.parse(localStorage.visitHistory);
				}

				for (var i = 0; i < visitHistory.length; i++) {
					if (visitHistory[i].hash === this.room.hash) {
						visitHistory.splice(i, 1);
						break;
					}
				}

				visitHistory.unshift({name: this.room.name, hash: this.room.hash});
				visitHistory.splice(5);
				localStorage.visitHistory = JSON.stringify(visitHistory);
			},
		},

		computed: {
			canNextButton() {
				return this.stage === 2 && this.$refs.tasksList.haveUnratedTasks && !this.$refs.cards.approve;
			},

			canSkipButton() {
				return !this.canNextButton && this.room.task !== null && (this.stage === 1 || this.stage === 2);
			},

			canReVoteButton() {
				return this.stage === 2 && this.$refs.tasksList.haveUnratedTasks && this.$refs.cards.approve;
			},
			anchorAttributes() {
				return {
					target: 'blank',
					rel: 'nofollow',
				}
			},
			fullPageUrl() {
				return global.location.href;
			}
		},

		watch: {
			stage() {
				this.changedStage();
			},
		},
	}
</script>

<style scoped>
	.fade-enter-active {
		transition: all .5s;
	}
	.fade-leave-active {
		transition: all .5s;
	}
	.fade-enter, .fade-leave-to {
		opacity: 0;
	}
	.wn-button {
		cursor: pointer;
	}
</style>
