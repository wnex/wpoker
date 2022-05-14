<template>
	<div class="row">
		<div class="col-md-4 order-md-2 mb-4" style="position: inherit;">
			<users-list :socket="socket" :users="users" :room="room" :average="average"></users-list>

			<chat :socket="socket" :room="room"></chat>
		</div>
		<div class="col-md-8 order-md-1">
			<h4 class="d-flex justify-content-between align-items-center mb-3">
				<div>
					<span v-if="!changeRoomNameSwitcher">{{room.name}}</span>
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
						class="badge wn-button"
						@click="editRoomName"
						title="Edit room's name"
					>
						<i class="fa fa-pencil" aria-hidden="true"></i>
					</span>
					<span v-if="room.name !== '' && room.isOwner && !changeRoomNameSwitcher" class="badge wn-button" @click="setPassword" title="Set password">
						<i class="fa" :class="{'fa-unlock': !room.hasPassword, 'fa-lock': room.hasPassword}" aria-hidden="true"></i>
					</span>
					<span v-if="room.name !== '' && !room.isOwner" class="badge">
						<i class="fa" :class="{'fa-unlock': !room.hasPassword, 'fa-lock': room.hasPassword}" aria-hidden="true"></i>
					</span>
				</div>
				<span class="badge badge-secondary"><stopwatch ref="stopwatch"></stopwatch></span>
			</h4>

			<cards ref="cards" :socket="socket" :canVote="canVote" :room="room"></cards>

			<transition name="fade">
				<div v-if="room.isOwner" class="row ml-0">
					<button v-if="stage === 0" class="btn mr-3 col-md-3 mb-3 btn-primary" @click="startVote">Start vote</button>
					<button v-if="stage === 1 || stage === 2" class="btn mr-3 mb-3 col-md-3 btn-primary" @click="resetVote">Reset</button>
					<button v-if="canSkipButton" class="btn mr-3 mb-3 col-md-3 btn-primary" @click="skipVote">Skip</button>
					<button v-if="canNextButton" class="btn mr-3 col-md-3 mb-3 btn-primary" @click="nextVote">Next</button>
					<button v-if="canReVoteButton" class="btn mr-3 col-md-3 mb-3 btn-primary" @click="revote">Revote</button>
				</div>
			</transition>

			<task-list ref="tasksList" :socket="socket" :room="room" @approve="onApprove"></task-list>
		</div>
	</div>
</template>

<script>
	import Stopwatch from '@/js/components/Stopwatch';
	import Chat from '@/js/components/Chat';
	import TaskList from '@/js/components/TaskList';
	import Cards from '@/js/components/Cards';
	import UsersList from '@/js/components/UsersList';
	import TextError from '@/js/components/TextError';

	export default {
		props: ['socket', 'hash'],

		components: {
			Stopwatch,
			Chat,
			TaskList,
			Cards,
			UsersList,
			TextError,
		},

		data: () => ({
			name: '',
			room: {
				id: null,
				name: '',
				hash: null,
				task: null,
				ownerId: null,
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

		created: function() {
			this.room.hash = this.hash;

			if (localStorage.name) {
				this.name = localStorage.name;
			}

			this.socket.open(() => {
				this.enterRoom();
			});

			this.soundStart = new Audio('../sounds/all-eyes-on-me.mp3');
			this.soundStart.volume = 0.1;
			this.soundFinal = new Audio('../sounds/rhodesmas.mp3');
			this.soundFinal.volume = 0.1;
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
			this.socket.listener('room.entered.user', (data) => {
				this.users.push({
					id: data.id,
					name: data.name,
					isOwner: data.isOwner,
					hasVote: data.hasVote,
					active: true,
				});

				if (data.isOwner) {
					this.room.ownerId = data.id;
				}
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
				this.room.ownerId = data.owner;
				this.room.clientId = data.client_id;
				this.room.task = data.task;
				this.room.hasPassword = data.hasPassword;
				this.room.cardset = data.cardset || {name: 'Default'};
				this.hasVote = data.hasVote;
				this.stage = data.stage;

				if (this.room.clientId === this.room.ownerId) {
					this.room.isOwner = true;
				}

				this.setTitle(this.room.name);
				this.setUsers(data.users);
				this.saveVisitHistory();
			});

			this.socket.listener('room.update', (data) => {
				this.room.name = data.name;
				this.room.hasPassword = data.hasPassword;
				this.room.cardset = data.cardset || {name: 'Default'};
				this.setTitle(this.room.name);
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

				this.setTitle(data.name);

				let password = prompt(`Enter the password for the room "${data.name}"`);
				if (password !== null && password.length !== 0) {
					this.socket.send({
						'action': 'room.enter',
						'room': this.hash,
						'name': this.name,
						'user': this.$root.getUser(),
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
						this.users[i].hasVote = false;
						break;
					}
				}
			});
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
					this.nextVote();
				}
			},

			nextVote() {
				this.resetVote();
				this.startVote();
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
						amount += this.users[i]['vote'];
						count++;
					}
				}

				if (count === 0) return 0;

				return Math.round(amount/count*100) / 100;
			},

			setUsers(users) {
				this.users = users;

				for (var i = 0; i < this.users.length; i++) {
					if (!this.users[i].active) {
						this.users[i].hasVote = false;
					}
					if (this.users[i].id === this.room.clientId) {
						this.users[i].isSelf = true;
					}
				}
			},

			setTitle(name) {
				let part = document.title.split(' - ');
				let base = part[1] !== undefined ? part[1] : part[0];
				document.title = name + ' - ' + base;
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
