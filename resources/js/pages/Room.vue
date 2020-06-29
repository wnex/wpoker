<template>
	<div class="row">
		<div class="col-md-4 order-md-2 mb-4">
			<h4 class="d-flex justify-content-between align-items-center mb-3">
				<span class="text-muted">Users</span>
				<span class="badge badge-secondary badge-pill">{{users.length}}</span>
			</h4>
			<ul v-if="users.length > 0" class="list-group mb-3">
				<li v-for="user in users" :key="user.id" class="list-group-item d-flex justify-content-between lh-condensed">
					<div v-bind:class="{'text-success': user.isVoted}">
						<h6 class="my-0">{{user.name}}</h6>
						<small class="text-muted"></small>
					</div>
					<span class="text-muted">{{user.vote}}</span>
				</li>

				<li v-if="average !== null" class="list-group-item d-flex justify-content-between">
					<span>Average</span>
					<strong>{{average}}</strong>
				</li>
			</ul>
			<div class="card p-2">
				<div class="input-group">
					<input type="text" v-model="name" class="form-control" placeholder="Enter your name">
					<div class="input-group-append">
						<button type="submit" @click.prevent="saveName" class="btn btn-secondary">Save</button>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-8 order-md-1">
			<h4 class="mb-3">Cards</h4>
			<div class="row mb-3">
				<div v-for="card in cards" :key="card.point" class="poker-card mb-3 d-flex col-md-2 justify-content-between">
					<img :src="canVote ? card.src : cover" @click="vote(card.point)" width="100%">
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
	export default {
		props: ['hash'],

		components: {

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
		}),

		created: function() {
			
		},

		mounted: function() {
			this.loading = true;

			if (localStorage.name) {
				this.name = localStorage.name;
			}

			this.socket = new WebSocket('ws://localhost:3000');

			this.socket.addEventListener('open', () => {
				this.socket.send(JSON.stringify({
					'action': 'room.open',
					'room': this.hash,
					'name': this.name,
				}));
			});

			this.socket.addEventListener('message', (etv) => {
				let data = JSON.parse(etv.data);

				if (data['action'] === 'room.entered.user') {
					this.users.push({
						id: data.id,
						name: data.name,
					});
				}

				if (data['action'] === 'room.left.user') {
					for (var i = 0; i < this.users.length; i++) {
						if (this.users[i].id === data.id) {
							break;
						}
					}

					this.users.splice(i, 1);
				}

				if (data['action'] === 'room.parameters') {
					this.users = data.users;
					this.owner = data.owner;
					this.stage = data.stage;
				}

				if (data['action'] === 'room.user.changeName') {
					for (var i = 0; i < this.users.length; i++) {
						if (this.users[i].id === data.id) {
							this.users[i].name = data.name;
							break;
						}
					}
				}

				if (data['action'] === 'room.vote.start') {
					this.stage = 1;
				}

				if (data['action'] === 'room.vote') {
					for (var i = 0; i < this.users.length; i++) {
						if (this.users[i].id === data.id) {
							this.$set(this.users[i], 'isVoted', true);
							break;
						}
					}
				}

				if (data['action'] === 'room.vote.you') {
					for (var i = 0; i < this.users.length; i++) {
						if (this.users[i].id === data.id) {
							this.$set(this.users[i], 'vote', data.vote);
							this.$set(this.users[i], 'isVoted', true);
							break;
						}
					}
				}

				if (data['action'] === 'room.vote.final') {
					this.users = data.users;
					this.canVote = false;
					this.average = this.getAverage();
				}

				if (data['action'] === 'room.vote.reset') {
					this.stage = 0;
					this.selectPoint = 0;
					this.average = null;
					for (var i = 0; i < this.users.length; i++) {
						this.$set(this.users[i], 'vote', undefined);
						this.$set(this.users[i], 'isVoted', false);
					}
				}
			});

		},

		methods: {
			saveName() {
				localStorage.name = this.name;

				this.socket.send(JSON.stringify({
					'action': 'room.user.changeName',
					'name': this.name,
					'room': this.hash,
				}));
			},

			isOwner() {
				return this.$root.getUser() === this.owner;
			},

			vote(point) {
				if (this.canVote) {
					this.selectPoint = point;
					this.socket.send(JSON.stringify({
						'action': 'room.vote',
						'room': this.hash,
						'vote': point,
					}));
				}
			},

			startVote() {
				this.socket.send(JSON.stringify({
					'action': 'room.vote.start',
					'room': this.hash,
				}));
			},

			resetVote() {
				this.socket.send(JSON.stringify({
					'action': 'room.vote.reset',
					'room': this.hash,
				}));
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

		watch: {
			stage() {
				if (this.stage === 0) {
					this.canVote = false;
				} else
				if (this.stage === 1) {
					this.canVote = true;
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
</style>
