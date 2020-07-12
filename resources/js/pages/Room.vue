<template>
	<div class="row">
		<div class="col-md-4 order-md-2 mb-4" style="position: inherit;">
			<users :socket="socket" :users="users" :room="hash" :isOwner="isOwner" :average="average"></users>

			<chat :socket="socket" :room="hash"></chat>
		</div>
		<div class="col-md-8 order-md-1">
			<h4 class="d-flex justify-content-between align-items-center">
				<span>{{room.name}}</span>
				<span class="badge badge-secondary"><stopwatch ref="stopwatch"></stopwatch></span>
			</h4>

			<cards ref="cards" :socket="socket" :canVote="canVote" :room="hash" :task="task"></cards>

			<transition name="fade">
				<div v-if="isOwner" class="row mb-3 ml-0">
					<button v-if="stage === 0" class="btn mr-3 col-md-3 btn-primary" @click="startVote">Start vote</button>
					<button v-if="stage === 1 || stage === 2" class="btn mr-3 col-md-3 btn-primary" @click="resetVote">Reset</button>
					<transition name="fade">
						<button v-if="canNextButton" class="btn mr-3 col-md-3 btn-primary" @click="nextVote">Next</button>
					</transition>
					<transition name="fade">
						<button v-if="canReVoteButton" class="btn mr-3 col-md-3 btn-primary" @click="nextVote">Revote</button>
					</transition>
				</div>
			</transition>

			<task-list ref="tasksList" :socket="socket" :room="hash" :id="room.id" :isOwner="isOwner"></task-list>
		</div>
	</div>
</template>

<script>
	import Stopwatch from '@/js/components/Stopwatch';
	import Chat from '@/js/components/Chat';
	import TaskList from '@/js/components/TaskList';
	import Cards from '@/js/components/Cards';
	import Users from '@/js/components/Users';

	import Socket from '@/js/modules/Socket';

	export default {
		props: ['socket', 'hash'],

		components: {
			Stopwatch,
			Chat,
			TaskList,
			Cards,
			Users,
		},

		data: () => ({
			selfId: null,
			name: '',
			room: {
				id: '',
				name: '',
			},
			users: [],
			owner: null,
			stage: 0,
			selectPoint: 0,
			average: null,
			canVote: false,
			task: null,
			loading: false,
		}),

		created: function() {
			this.loading = true;

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
					this.owner = data.id;
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
				this.selfId = data.client_id;
				this.users = data.users;
				this.owner = data.owner;
				this.room.id = data.id;
				this.room.name = data.name;
				this.stage = data.stage;
				this.task = data.task;
			});

			this.socket.listener('room.user.changeName', (data) => {
				for (var i = 0; i < this.users.length; i++) {
					if (this.users[i].id === data.id) {
						this.users[i].name = data.name;
						break;
					}
				}

				if (data.id === this.selfId) {
					this.name = data.name;
				}
			});

			this.socket.listener('room.vote.start', (data) => {
				this.stage = data.stage;
				this.task = data.task;
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
						this.$set(this.users[i], 'isVoted', true);
						break;
					}
				}
			});

			this.socket.listener('room.kicked.you', (data) => {
				this.$router.push({name: 'home', query: {kicked: true}});
			});

			this.socket.listener('room.vote.final', (data) => {
				this.users = data.users;
				this.canVote = false;
				this.average = this.getAverage();
				this.$refs.stopwatch.stopTimer();
				this.stage = 2;

				if (this.isOwner) {
					this.$refs.cards.startApprove();
				}
			});

			this.socket.listener('room.vote.reset', (data) => {
				this.stage = 0;
				this.selectPoint = 0;
				this.average = null;
				for (var i = 0; i < this.users.length; i++) {
					this.$set(this.users[i], 'vote', undefined);
					this.$set(this.users[i], 'isVoted', false);
				}
				this.task = null;
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
					amount += this.users[i]['vote'];
					count++;
				}

				return Math.round(amount/count*100) / 100;
			},

		},

		computed: {
			isOwner() {
				if (this.owner === null) {
					return false;
				}

				return this.selfId === this.owner;
			},

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
