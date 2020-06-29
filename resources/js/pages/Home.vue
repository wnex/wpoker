<template>
	<div class="row">
		<div class="col-md-4 order-md-2 mb-4">
			<h4 class="mb-3">Your name</h4>
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
				</li>
			</ul>
			<div class="input-group mb-4">
				<input type="text" v-model="nameNewRoom" class="form-control" placeholder="Room name">
				<div class="input-group-append">
					<button type="submit" @click.prevent="createRoom" class="btn btn-primary">New room</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		components: {

		},

		data: () => ({
			rooms: [],
			name: '',
			nameNewRoom: '',
			loading: false,
		}),

		mounted: function() {
			if (localStorage.name) {
				this.name = localStorage.name;
			}

			if (localStorage.rooms) {
				this.rooms = JSON.parse(localStorage.rooms);
			}

			let promise = fetch('/api/rooms?owner='+this.$root.getUser())
				.then(response => response.json())
				.then(result => {
					this.rooms = result;
					localStorage.rooms = JSON.stringify(this.rooms);
				});
		},

		methods: {
			saveName() {
				localStorage.name = this.name;
			},

			createRoom() {
				let promise = fetch('/api/rooms/create', {
					method: 'POST',
					body: JSON.stringify({
						name: this.nameNewRoom,
						owner: this.$root.getUser(),
					}),
				})
					.then(response => response.json())
					.then(result => {
						this.rooms.push(result);
						localStorage.rooms = JSON.stringify(this.rooms);
					});
			},

			
		},

		computed: {
			
		},

		watch: {

		},
	}
</script>

<style scoped>
	
</style>
