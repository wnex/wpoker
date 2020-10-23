<template>
	<div class="row">
		<div class="col-md-4 order-md-2 mb-4">
			<h4 class="d-flex justify-content-between align-items-center mb-3">Your name</h4>

			<form @submit.prevent="saveName">
				<text-error :text="changeNameErrorText"></text-error>
				<div class="input-group">
					<input type="text" v-model="name" class="form-control" placeholder="Enter your name">
					<div class="input-group-append">
						<button type="submit" class="btn btn-secondary">Save</button>
					</div>
				</div>
			</form>
		</div>
		<div class="col-md-8 order-md-1">
			<h4 class="mb-3">Room list</h4>
			<form @submit.prevent="createRoom">
				<text-error :text="roomErrorText"></text-error>
				<div class="input-group mb-4">
					<input type="text" v-model="nameNewRoom" class="form-control" placeholder="Room name">
					<div class="input-group-append">
						<button type="submit" class="btn btn-primary">New room</button>
					</div>
				</div>
			</form>

			<ul v-if="rooms.length > 0" class="list-group mb-3">
				<li v-for="room in rooms" class="list-group-item d-flex justify-content-between lh-condensed">
					<router-link link :to="{name: 'room', params: {hash: room.hash}}" style="cursor: pointer;">
						<h6 class="my-0">{{room.name}}</h6>
					</router-link>
					<span>
						<span class="badge badge-secondary delete" @click="">
							<i class="fa fa-unlock-alt" aria-hidden="true"></i>
						</span>
						<span class="badge badge-secondary delete" @click="deleteRoom(room.id)">
							<i class="fa fa-times" aria-hidden="true"></i>
						</span>
					</span>
				</li>
			</ul>
			
		</div>
	</div>
</template>

<script>
	import Socket from '@/js/modules/Socket';
	import TextError from '@/js/components/TextError';

	export default {
		props: ['socket'],

		components: {
			TextError,
		},

		data: () => ({
			rooms: [],
			name: '',
			roomErrorText: null,
			changeNameErrorText: null,
			nameNewRoom: '',
			loading: false,
		}),

		created: function() {
			if (localStorage.name) {
				this.name = localStorage.name;
			}

			if (localStorage.rooms) {
				this.rooms = JSON.parse(localStorage.rooms);
			}

			let promise = this.socket.request('room.get', {
				owner: this.$root.getUser(),
			})
				.then((result) => {
					this.rooms = result.data;
					localStorage.rooms = JSON.stringify(this.rooms);
				});
		},

		mounted: function() {
			if(this.$route.query.kicked) {
				alert('Kicked you out.');
			}
		},

		methods: {
			saveName() {
				if (this.name === '') {
					this.changeNameErrorText = 'Empty name.';
					return false;
				}

				localStorage.name = this.name;
				this.changeNameErrorText = null;
			},

			createRoom() {
				if (this.nameNewRoom === '') {
					this.roomErrorText = 'Empty name.';
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

				this.roomErrorText = null;
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
	}
</script>

<style scoped>
	.delete {
		cursor: pointer;
	}
</style>
