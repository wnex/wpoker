<template>
	<div class="row">
		<div class="col-md-4 order-md-2 mb-4">
			<h4 class="d-flex justify-content-between align-items-center mb-3">
				<span class="text-muted">Users</span>
				<span class="badge badge-secondary">{{users.length}}</span>
			</h4>
			<ul v-if="users.length > 0" class="list-group mb-3">
				<li v-for="user in users" :key="user.id" class="list-group-item d-flex justify-content-between lh-condensed">
					<div :class="{'text-success': user.isVoted}">
						<h6 class="my-0">
							{{user.name === '' ? 'User #'+user.id : user.name}}
						</h6>
						<small class="text-muted">{{owner === user.id ? 'Owner' : 'Guest'}}</small>
					</div>
					<span class="text-muted">
						<span class="badge badge-success">{{user.vote}}</span>
					</span>
				</li>

				<li v-if="average !== null" class="list-group-item d-flex justify-content-between">
					<span>Average</span>
					<strong>{{average}}</strong>
				</li>
			</ul>
			<div class="card p-2">
				<button v-if="!changeNameSwitcher" @click.prevent="changeName" class="btn btn-secondary">Change name</button>
				<form v-if="changeNameSwitcher" @submit.prevent="saveName">
					<div class="input-group">
						<input type="text" v-model="name" class="form-control" placeholder="Enter your name">
						<div class="input-group-append">
							<button type="submit" class="btn btn-secondary">Save</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="col-md-8 order-md-1">
			<h4 class="d-flex justify-content-between align-items-center mb-3">
				Cards
				<span class="badge badge-secondary"><stopwatch ref="stopwatch"></stopwatch></span>
			</h4>
			<div class="row mb-3">
				<div v-for="card in cards" :key="card.point" class="poker-card mb-3 d-flex col-md-2 justify-content-between">
					<div class="flip-container" :class="{'hover': canVote}">
						<div class="flipper">
							<div class="front">
								<img :src="cover" @click="shake(card.point)" :class="{'shake': card.shake}" width="100%">
							</div>
							<div class="back">
								<img :src="card.src" @click="vote(card.point)" width="100%">
							</div>
						</div>
					</div>
					
				</div>
			</div>
			<div v-if="isOwner()" class="row mb-3 ml-0 text-right">
				<button v-if="stage === 0" class="btn mr-3 col-md-3 btn-primary" @click="startVote">Start vote</button>
				<button v-if="stage === 1" class="btn mr-3 col-md-3 btn-primary" @click="resetVote">Reset</button>
			</div>
		</div>
	</div>
</template>

<script>
	import Stopwatch from '@/js/components/Stopwatch';
	import Socket from '@/js/modules/Socket';

	export default {
		props: ['hash'],

		components: {
			Stopwatch,
		},

		data: () => ({
			name: '',
			socket: null,
			users: [],
			owner: null,
			stage: 0,
			selectPoint: 0,
			average: null,
			canVote: false,
			cover: '/images/cards/cover.png',
			cards: [
				{
					src: '/images/cards/0.25.png',
					point: 0.25,
				},
				{
					src: '/images/cards/0.5.png',
					point: 0.5,
				},
				{
					src: '/images/cards/1.png',
					point: 1,
				},
				{
					src: '/images/cards/2.png',
					point: 2,
				},
				{
					src: '/images/cards/3.png',
					point: 3,
				},
				{
					src: '/images/cards/5.png',
					point: 5,
				},
				{
					src: '/images/cards/8.png',
					point: 8,
				},
				{
					src: '/images/cards/13.png',
					point: 13,
				},
				{
					src: '/images/cards/dragon.png',
					point: 0,
				},
			],
			loading: false,

			changeNameSwitcher: false,
		}),

		created: function() {
			this.loading = true;

			if (localStorage.name) {
				this.name = localStorage.name;
			}

			this.socket = new Socket(document.body.dataset.socket);

			this.socket.open(() => {
				this.socket.send({
					'action': 'room.open',
					'room': this.hash,
					'name': this.name,
					'user': this.$root.getUser(),
				});
			});
		},

		mounted: function() {
			this.socket.listener('room.entered.user', (data) => {
				this.users.push({
					id: data.id,
					name: data.name,
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
				this.selfId = data.id;
				this.users = data.users;
				this.owner = data.owner;
				this.stage = data.stage;
			});

			this.socket.listener('room.user.changeName', (data) => {
				for (var i = 0; i < this.users.length; i++) {
					if (this.users[i].id === data.id) {
						this.users[i].name = data.name;
						break;
					}
				}
			});

			this.socket.listener('room.vote.start', (data) => {
				this.stage = 1;
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

			this.socket.listener('room.vote.final', (data) => {
				this.users = data.users;
				this.canVote = false;
				this.average = this.getAverage();
				this.$refs.stopwatch.stopTimer();
			});

			this.socket.listener('room.vote.reset', (data) => {
				this.stage = 0;
				this.selectPoint = 0;
				this.average = null;
				for (var i = 0; i < this.users.length; i++) {
					this.$set(this.users[i], 'vote', undefined);
					this.$set(this.users[i], 'isVoted', false);
				}
				this.$refs.stopwatch.clearAll();
			});

			this.socket.listener('room.eggs.shake', (data) => {
				for (var i = 0; i < this.cards.length; i++) {
					if (this.cards[i].point === data.point) {
						this.$set(this.cards[i], 'shake', true);

						setTimeout(() => {
							this.$set(this.cards[i], 'shake', false);
						}, 500);
						break;
					}
				}
			});
		},

		methods: {
			changeName() {
				this.changeNameSwitcher = true;
			},

			saveName() {
				if (this.name === '') {
					alert('Error! Empty name.');
					return false;
				}

				localStorage.name = this.name;
				this.changeNameSwitcher = false;

				this.socket.send({
					'action': 'room.user.changeName',
					'name': this.name,
					'room': this.hash,
				});
			},

			isOwner() {
				return this.selfId === this.owner;
			},

			vote(point) {
				if (this.canVote) {
					this.selectPoint = point;
					this.socket.send({
						'action': 'room.vote',
						'room': this.hash,
						'vote': point,
					});
				}
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


			// Easter eggs
			shake(point) {
				this.socket.send({
					'action': 'room.eggs.shake',
					'point': point,
					'room': this.hash,
				});
			},
		},

		watch: {
			stage() {
				if (this.stage === 0) {
					this.canVote = false;
					this.$refs.stopwatch.stopTimer();
				} else
				if (this.stage === 1) {
					this.canVote = true;
					this.$refs.stopwatch.startTimer();
				}
			},
		},
	}
</script>

<style scoped>
	.poker-card img {
		cursor: pointer;
		border-radius: 5px;
	}

	/* entire container, keeps perspective */
	.flip-container {
		perspective: 1000px;
	}
		/* flip the pane when hovered */
		.flip-container.hover .flipper {
			transform: rotateY(180deg);
		}

	.flip-container, .front, .back {
		/* width: 96px;
		height: 144px; */
		width: 100%;
		/* height: 100vh; */
	}

	/* flip speed goes here */
	.flipper {
		transition: ease-in-out 0.6s;
		transform-style: preserve-3d;

		position: relative;

		width: 100%;
		padding-top: 150%; /* 3:4 Aspect Ratio */
	}

	/* hide back of pane during swap */
	.front, .back {
		backface-visibility: hidden;

		position: absolute;
		top: 0;
		left: 0;
		bottom: 0;
		right: 0;
	}

	/* front pane, placed above back */
	.front {
		z-index: 2;
		/* for firefox 31 */
		transform: rotateY(0deg);
	}

	/* back, initially hidden pane */
	.back {
		transform: rotateY(180deg);
	}

	img.shake {
		animation: shake 0.5s;
		animation-iteration-count: 1;
	}

	/* @keyframes shake {
		0% { transform: translate(1px, 1px) rotate(0deg); }
		10% { transform: translate(-1px, -2px) rotate(-1deg); }
		20% { transform: translate(-3px, 0px) rotate(1deg); }
		30% { transform: translate(3px, 2px) rotate(0deg); }
		40% { transform: translate(1px, -1px) rotate(1deg); }
		50% { transform: translate(-1px, 2px) rotate(-1deg); }
		60% { transform: translate(-3px, 1px) rotate(0deg); }
		70% { transform: translate(3px, 1px) rotate(-1deg); }
		80% { transform: translate(-1px, -1px) rotate(1deg); }
		90% { transform: translate(1px, 2px) rotate(0deg); }
		100% { transform: translate(1px, -2px) rotate(-1deg); }
	} */
	@keyframes shake {
		0% { transform: translate(1px, 1px) rotate(0deg); }
		10% { transform: translate(-1px, -2px) rotate(0deg); }
		20% { transform: translate(-2px, 0px) rotate(0deg); }
		30% { transform: translate(2px, 1px) rotate(0deg); }
		40% { transform: translate(1px, -1px) rotate(0deg); }
		50% { transform: translate(-1px, 2px) rotate(0deg); }
		60% { transform: translate(-2px, 1px) rotate(0deg); }
		70% { transform: translate(2px, 1px) rotate(0deg); }
		80% { transform: translate(-1px, -1px) rotate(0deg); }
		90% { transform: translate(1px, 1px) rotate(0deg); }
		100% { transform: translate(1px, -1px) rotate(0deg); }
	}
</style>
