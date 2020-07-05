<template>
	<div class="row">
		<div class="col-md-4 order-md-2 mb-4">
			<h4 class="d-flex justify-content-between align-items-center mb-3">Your name</h4>
			<div class="input-group">
				<input type="text" v-model="name" class="form-control" placeholder="Enter your name">
				<div class="input-group-append">
					<button type="submit" @click.prevent="saveName" class="btn btn-secondary">Save</button>
				</div>
			</div>
		</div>
		<div class="col-md-8 order-md-1">
			<h4 class="mb-3">Room list</h4>
			<ul v-if="rooms.length > 0" class="list-group mb-3">
				<li v-for="room in rooms" class="list-group-item d-flex justify-content-between lh-condensed">
					<router-link link :to="{name: 'room', params: {hash: room.hash}}" style="cursor: pointer;">
						<h6 class="my-0">{{room.name}}</h6>
					</router-link>
					<span class="badge badge-secondary delete" @click="deleteRoom(room.id)">x</span>
				</li>
			</ul>
			<form @submit.prevent="createRoom">
				<div class="input-group mb-4">
					<input type="text" v-model="nameNewRoom" class="form-control" placeholder="Room name">
					<div class="input-group-append">
						<button type="submit" class="btn btn-primary">New room</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script>
	import Socket from '@/js/modules/Socket';

	export default {
		components: {

		},

		data: () => ({
			rooms: [],
			name: '',
			nameNewRoom: '',
			socket: null,
			loading: false,
		}),

		created: function() {
			if (localStorage.name) {
				this.name = localStorage.name;
			}

			if (localStorage.rooms) {
				this.rooms = JSON.parse(localStorage.rooms);
			}

			this.socket = new Socket(document.body.dataset.socket);

			let promise = this.socket.request('room.get', {
				owner: this.$root.getUser(),
			})
				.then((result) => {
					this.rooms = result.data;
					localStorage.rooms = JSON.stringify(this.rooms);
				});
		},

		mounted: function() {
			
		},

		methods: {
			saveName() {
				if (this.name === '') {
					alert('Error! Empty name.');
					return false;
				}

				localStorage.name = this.name;
			},

			createRoom() {
				if (this.nameNewRoom === '') {
					alert('Error! Empty name.');
					return false;
				}

				let promise = this.socket.request('room.create', {
					name: this.nameNewRoom,
					owner: this.$root.getUser(),
				})
					.then((result) => {
						this.rooms.push(result.data);
						localStorage.rooms = JSON.stringify(this.rooms);
						this.nameNewRoom = '';
					});
			},

			deleteRoom(id) {
				if (confirm("Delete room?")) {
					let promise = this.socket.request('room.delete', {
						id: id,
						owner: this.$root.getUser(),
					})
						.then((result) => {
							for (var i = 0; i < this.rooms.length; i++) {
								if (this.rooms[i].id == id) {
									this.rooms.splice(i, 1);
									break;
								}
							}
							localStorage.rooms = JSON.stringify(this.rooms);
						});
				}
			},
			
		},

		computed: {
			
		},

		watch: {

		},
	}
</script>

<style scoped>
	.delete {
		cursor: pointer;
	}
</style>
