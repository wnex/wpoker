<template>
	<div class="row">
		<div class="col-md-4 order-md-2 mb-4" style="position: inherit;">
			<users-list :socket="socket" :users="users" :room="room" :average="average"></users-list>

			<chat :socket="socket" :room="room"></chat>
		</div>
		<div class="col-md-8 order-md-1">
			<h4 class="d-flex justify-content-between align-items-center">
				<span>{{room.name}}</span>
				<span class="badge badge-secondary"><stopwatch ref="stopwatch"></stopwatch></span>
			</h4>

			<cards ref="cards" :socket="socket" :canVote="canVote" :room="room"></cards>

			<transition name="fade">
				<div v-if="room.isOwner" class="row mb-3 ml-0">
					<button v-if="stage === 0" class="btn mr-3 col-md-3 btn-primary" @click="startVote">Start vote</button>
					<button v-if="stage === 1 || stage === 2" class="btn mr-3 col-md-3 btn-primary" @click="resetVote">Reset</button>
					<button v-if="canNextButton" class="btn mr-3 col-md-3 btn-primary" @click="nextVote">Next</button>
					<button v-if="canReVoteButton" class="btn mr-3 col-md-3 btn-primary" @click="nextVote">Revote</button>
				</div>
			</transition>

			<task-list ref="tasksList" :socket="socket" :room="room"></task-list>
		</div>
	</div>
</template>

<script>
	import Stopwatch from '@/js/components/Stopwatch';
	import Chat from '@/js/components/Chat';
	import TaskList from '@/js/components/TaskList';
	import Cards from '@/js/components/Cards';
	import UsersList from '@/js/components/UsersList';

	import Socket from '@/js/modules/Socket';

	export default {
		props: ['socket', 'hash'],

		components: {
			Stopwatch,
			Chat,
			TaskList,
			Cards,
			UsersList,
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
			},
			users: [],
			stage: 0,
			average: null,
			canVote: false,
		}),

		created: function() {
			this.room.hash = this.hash;

			if (localStorage.name) {
				this.name = localStorage.name;
			}

			this.socket.open(() => {
				this.enterRoom();
			});
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
				this.stage = data.stage;

				if (this.room.clientId === this.room.ownerId) {
					this.room.isOwner = true;
				}

				this.setUsers(data.users);
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

			this.socket.listener('room.vote.start', (data) => {
				this.stage = data.stage;
				this.room.task = data.task;
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
				this.canVote = false;
				this.$refs.stopwatch.stopTimer();
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
		},

		methods: {
			enterRoom() {
				this.socket.send({
					'action': 'room.enter',
					'room': this.hash,
					'name': this.name,
					'user': this.$root.getUser(),
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

			nextVote() {
				this.resetVote();
				this.startVote();
			},

			getAverage() {
				let amount = 0,
					count = 0;
				for (var i = 0; i < this.users.length; i++) {
					if (this.users[i]['vote'] != 0) {
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
					if (this.users[i].id === this.room.clientId) {
						this.users[i].isSelf = true;
					}
				}
			},
		},

		computed: {
			canNextButton() {
				return this.stage === 2 && this.$refs.tasksList.haveUnratedTasks && !this.$refs.cards.approve;
			},

			canReVoteButton() {
				return this.stage === 2 && this.$refs.tasksList.haveUnratedTasks && this.$refs.cards.approve;
			},

			
		},

		watch: {
			stage() {
				switch(this.stage) {
					case 0:
						this.canVote = false;
						this.$refs.stopwatch.stopTimer();
						break;
					case 1:
						this.canVote = true;
						this.$refs.stopwatch.startTimer();
						break;
					case 2:
						this.canVote = false;
						break;
				}
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
</style>
