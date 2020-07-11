<template>
	<div>
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
					<small class="text-muted">{{user.isOwner ? 'Owner' : 'Guest'}}</small>
				</div>
				<span class="text-muted">
					<span v-if="isOwner && !user.isOwner" class="badge user-control kick-out" @click="kickOut(user.id)">
						<i class="fa fa-fw fa-sign-out" aria-hidden="true"></i>
					</span>
					<span class="badge user-control badge-primary">{{user.vote}}</span>
				</span>
			</li>

			<li v-if="average !== null" class="list-group-item d-flex justify-content-between">
				<span>Average</span>
				<strong>{{average}}</strong>
			</li>

			<li class="list-group-item d-flex lh-condensed" style="display: flex; flex-direction: column;">
				<button v-if="!changeNameSwitcher" @click.prevent="changeName" class="btn btn-outline-secondary">Change name</button>
				<form v-if="changeNameSwitcher" @submit.prevent="saveName">
					<div class="input-group">
						<input type="text" v-model="name" class="form-control" placeholder="Enter your name">
						<div class="input-group-append">
							<button type="submit" class="btn btn-secondary">Save</button>
						</div>
					</div>
				</form>
			</li>
		</ul>
	</div>
</template>

<script>
	export default {
		props: ['socket', 'users', 'room', 'name', 'isOwner', 'average'],

		components: {
			
		},

		data: () => ({
			changeNameSwitcher: false,
		}),

		mounted: function() {
			
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
					'room': this.room,
				});
			},

			kickOut(client_id) {
				if (confirm("Kick out this user?")) {
					this.socket.send({
						'action': 'room.user.kick',
						'id': client_id,
						'room': this.room,
					});
				}
			},
		},
	}
</script>

<style scoped>
	.kick-out {
		cursor: pointer;
	}
</style>
