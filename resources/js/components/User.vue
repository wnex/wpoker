<template>
	<li
		class="list-group-item"
		:class="{'d-flex justify-content-between lh-condensed px-4 py-2': !changeNameSwitcher}"
	>
		<div v-if="!changeNameSwitcherForSelf()" :class="{'text-success': user.isVoted}">
			<h6 class="my-0">
				{{user.name === '' ? 'User #'+user.id : user.name}}
			</h6>
			<small class="text-muted">{{user.isOwner ? 'Owner' : 'Guest'}}</small>
		</div>
		<span v-if="!changeNameSwitcher" class="text-muted">
			<span
				v-if="user.isSelf"
				title="Change name"
				class="badge user-control pointer"
				@click.prevent="changeName"
			>
				<i class="fa fa-fw fa-pencil" aria-hidden="true"></i>
			</span>
			<span
				v-if="isOwner && !user.isOwner"
				title="Kick this user"
				class="badge user-control pointer"
				@click="kickOut(user.id)"
			>
				<i class="fa fa-fw fa-sign-out" aria-hidden="true"></i>
			</span>
			<span v-if="user.vote === undefined && user.isVoted" class="empty-card">?</span>
			<span class="badge user-control badge-primary">{{user.vote}}</span>
		</span>

		<form v-if="user.isSelf && changeNameSwitcher" @submit.prevent="saveName">
			<text-error :text="changeNameErrorText"></text-error>
			<div class="input-group">
				<input type="text" v-model="name" class="form-control" placeholder="Enter your name">
				<div class="input-group-append">
					<button type="submit" class="btn btn-secondary">Save</button>
				</div>
			</div>
		</form>
	</li>
</template>

<script>
	import TextError from '@/js/components/TextError';

	export default {
		props: ['socket', 'user', 'isOwner', 'room'],

		components: {
			TextError,
		},

		data: () => ({
			name: '',
			chart: false,
			changeNameSwitcher: false,
			changeNameErrorText: null,
			points: ['0.25 sp', '0.5 sp', '1 sp', '2 sp', '3 sp', '5 sp', '8 sp', '13 sp', '0 sp'],
			colors: ['#6574cd', '#e3342f', '#38c172', '#f6993f', '#9561e2', '#ffed4a', '#6cb2eb'],
			chartOptions: {
				legend: {
					'position': 'bottom',
				},
				/*responsive: true,
				maintainAspectRatio: false,*/
			},
		}),

		mounted: function() {
			if (localStorage.name) {
				this.name = localStorage.name;
			}
		},

		methods: {
			changeName() {
				this.changeNameSwitcher = true;
			},

			saveName() {
				if (this.name === '') {
					this.changeNameErrorText = 'Empty name.';
					return false;
				}

				localStorage.name = this.name;
				this.changeNameSwitcher = false;

				this.socket.send({
					'action': 'room.user.changeName',
					'name': this.name,
					'room': this.room,
				});

				this.changeNameErrorText = null;
			},

			changeNameSwitcherForSelf(id) {
				return this.user.isSelf && this.changeNameSwitcher;
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
	.pointer {
		cursor: pointer;
	}

	.user-control {
		font-size: 100%;
	}

	.empty-card {
		width: 22px;
		height: 24px;
		color: #007bff;
		background-color: #007bff;
		border-radius: .25rem;
		display: inline-block;
		vertical-align: baseline;
		white-space: nowrap;
	}
</style>
