<template>
	<li
		class="list-group-item"
		:class="{'d-flex justify-content-between lh-condensed': !changeNameSwitcher, 'disconnected': !user.active, 'list-group-item-secondary': !user.hasVote}"
	>
		<div v-if="!changeNameSwitcherForSelf()" :class="{'text-success': user.isVoted}">
			<h6 class="my-0">
				{{user.name === '' ? '[No name]' : user.name}}
			</h6>
			<small v-if="user.isOwner" class="text-muted">Owner</small>
			<small v-if="!user.hasVote" class="text-muted">Spectator</small>
		</div>
		<span v-if="!changeNameSwitcher" class="text-muted d-flex panel-buttons">
			<span v-if="user.vote === undefined && user.isVoted" class="empty-card ml-1"></span>
			<vue-markdown
				class="badge user-control badge-primary ml-1"
				:html="false"
				:source="user.voteView"
			></vue-markdown>

			<div v-if="user.isSelf || room.isOwner" class="btn-group dropleft" role="group">
				<button type="button" class="btn badge pr-0 user-control pointer" title="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fa fa-ellipsis-v" aria-hidden="true"></i>
				</button>
				<div class="dropdown-menu">
					<button v-if="user.isSelf" @click.prevent="changeName" class="dropdown-item btn-sm" type="button">
						<i class="fa fa-fw fa-pencil" aria-hidden="true"></i> Change name
					</button>
					<button v-if="room.isOwner && !user.isOwner" @click.prevent="kickOut(user.id)" class="dropdown-item btn-sm" type="button">
						<i class="fa fa-fw fa-sign-out" aria-hidden="true"></i> Kick from room
					</button>
					<button v-if="user.isSelf || room.isOwner && !user.isOwner" @click.prevent="toggleUserHasVote(user.id, !user.hasVote)" class="dropdown-item btn-sm" type="button">
						<i class="fa fa-fw" :class="{'fa-toggle-on': !user.hasVote, 'fa-toggle-off': user.hasVote}" aria-hidden="true"></i> Spectator mode
					</button>
					<button v-if="!user.isSelf && room.isOwner" @click.prevent="transferTheRoom(user.id)" class="dropdown-item btn-sm" type="button">
						<i class="fa fa-fw fa-arrow-circle-o-right" aria-hidden="true"></i> Transfer the room
					</button>
				</div>
			</div>
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
	import VueMarkdown from 'vue-markdown';
	import TextError from '@/js/components/TextError';

	export default {
		props: ['socket', 'user', 'room'],

		components: {
			VueMarkdown,
			TextError,
		},

		data: () => ({
			name: '',
			changeNameSwitcher: false,
			changeNameErrorText: null,
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
					'room': this.room.hash,
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
						'room': this.room.hash,
					});
				}
			},

			toggleUserHasVote(client_id, value) {
				this.socket.send({
					'action': 'room.user.toggle.hasVote',
					'id': client_id,
					'value': value,
					'room': this.room.hash,
				});
			},

			async transferTheRoom(client_id) {
				if (confirm("Transfer the room other user?")) {
					let data = await this.socket.request('room.update', {
						id: this.room.id,
						owner: this.$root.getUser(),
						owner_client_id: client_id,
					});
				}
			}
		},
	}
</script>

<style scoped>
	.panel-buttons {
		align-items: flex-start;
	}

	.pointer {
		cursor: pointer;
	}

	.user-control {
		font-size: 100%;
	}

	.empty-card {
		text-align: center;
		width: 22px;
		height: 24px;
		color: #007bff;
		background-color: #007bff;
		border-radius: .25rem;
		display: inline-block;
		vertical-align: baseline;
		white-space: nowrap;
	}

	.disconnected {
		opacity: 0.5;
	}
</style>
