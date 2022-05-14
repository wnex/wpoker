<template>
	<div class="row">
		<div class="col-md-8 order-md-1">
			<h4 class="mb-3">Your rooms</h4>
			<form @submit.prevent="createRoom" class="mb-4">
				<text-error :text="roomErrorText"></text-error>
				<div class="input-group">
					<input type="text" v-model="nameNewRoom" class="form-control" placeholder="Enter the name of the new room">
					<div class="input-group-append">
						<button type="submit" class="btn btn-primary">Create room</button>
					</div>
				</div>
				<small class="text-muted">
					This site is protected by reCAPTCHA and the Google
						<a href="https://policies.google.com/privacy" rel="nofollow" target="_blank">Privacy Policy</a> and
						<a href="https://policies.google.com/terms" rel="nofollow" target="_blank">Terms of Service</a> apply.
				</small>
			</form>

			<ul v-if="rooms.length > 0" class="list-group mb-3">
				<li v-for="room in rooms" class="list-group-item d-flex justify-content-between lh-condensed">
					<span>
						<router-link link :to="{name: 'room', params: {hash: room.hash}}" style="cursor: pointer;">
							<h6 class="my-0">{{room.name}}</h6>
						</router-link>
						<small class="text-muted">
							Set: {{ room.cardset ? room.cardset.name : 'Default' }} &bull;
							Updated at <timer :created="room.updated_at"></timer></small>
					</span>
					<span>
						<span class="badge badge-secondary wn-button" @click="cloneRoom(room.id, room.name)" title="Clone the room">
							<i class="fa fa-fw fa fa-clone" aria-hidden="true"></i>
						</span>
						<span class="badge badge-secondary wn-button" @click="setPassword(room.id)" title="Set password">
							<i class="fa fa-fw" :class="{'fa-unlock': !room.hasPassword, 'fa-lock': room.hasPassword}" aria-hidden="true"></i>
						</span>
						<span class="badge badge-secondary wn-button" @click="deleteRoom(room.id)" title="Delete the room">
							<i class="fa fa-times" aria-hidden="true"></i>
						</span>
					</span>
				</li>
			</ul>
		</div>
		<div class="col-md-4 order-md-2 mb-4">
			<h4 class="d-flex justify-content-between align-items-center mb-3">Your name</h4>

			<form @submit.prevent="saveName" class="mb-4">
				<text-error :text="changeNameErrorText"></text-error>
				<div class="input-group">
					<input type="text" v-model="name" class="form-control" placeholder="Enter your name">
					<div class="input-group-append">
						<button type="submit" class="btn btn-secondary">Save</button>
					</div>
				</div>
			</form>

			<h4 class="d-flex justify-content-between align-items-center mb-3">Your secret UUID</h4>
			<div class="input-group mb-3">
				<input :type="uidVisibility ? 'text' : 'password'" @focus="$event.target.select()" :value="$root.getUser()" readonly class="form-control">
				<div class="input-group-append">
					<button @click="uidVisibility = !uidVisibility" type="button" class="btn btn-outline-secondary">
						<i class="fa" :class="{'fa-eye': !uidVisibility, 'fa-eye-slash': uidVisibility}" aria-hidden="true"></i>
					</button>
					<button @click="changeUid" type="button" class="btn btn-outline-secondary">
						<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
					</button>
					<button @click="copyUid" type="button" class="btn btn-outline-secondary">Copy</button>
				</div>
			</div>
			<div class="mb-4">
				<p>This is your unique user ID. Your rooms are tied to it and your owner rights are determined by it.</p>
				<p>You can copy and paste it on your other devices so you can access your rooms from all your devices.</p>
				<p>Do not share this code with other people.</p>
			</div>

			<h4 v-if="visitHistory.length > 0" class="d-flex justify-content-between align-items-center mb-3">Visit history</h4>
			<div v-if="visitHistory.length > 0">
				<div v-for="room in visitHistory">
					<router-link link :to="{name: 'room', params: {hash: room.hash}}" style="cursor: pointer;">
						<h6 class="my-0">{{room.name}}</h6>
					</router-link>
					<hr>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import Timer from '@/js/components/Timer';
	import Socket from '@/js/modules/Socket';
	import TextError from '@/js/components/TextError';
	import { validate as uuidValidate } from 'uuid';
	import { load as recaptchaLoad } from 'recaptcha-v3';

	export default {
		props: ['socket'],

		components: {
			Timer,
			TextError,
		},

		data: () => ({
			rooms: [],
			visitHistory: [],
			name: '',
			roomErrorText: null,
			changeNameErrorText: null,
			nameNewRoom: '',
			loading: false,
			uidVisibility: false,
		}),

		created: function() {
			if (localStorage.name) {
				this.name = localStorage.name;
			}

			if (localStorage.rooms) {
				this.rooms = JSON.parse(localStorage.rooms);
			}

			if (localStorage.visitHistory) {
				this.visitHistory = JSON.parse(localStorage.visitHistory);
			}

			this.roomsUpdate();
		},

		mounted: function() {
			if(this.$route.query.kicked) {
				this.$nextTick(() => {
					console.log('Kicked you out.');
				});
			}

			this.$root.setTitle();
		},

		methods: {
			roomsUpdate() {
				let promise = this.socket.request('room.get', {
					owner: this.$root.getUser(),
				}).then((result) => {
					this.rooms = result.data;
					localStorage.rooms = JSON.stringify(this.rooms);
				});
			},

			saveName() {
				if (this.name === '') {
					this.changeNameErrorText = 'Empty name.';
					return false;
				}

				localStorage.name = this.name;
				this.changeNameErrorText = null;
			},

			async captchaVerify(action) {
				const recaptcha = await recaptchaLoad('6LeVSDIfAAAAABxjfI8yEzoalSQ5iNa1TTobtx8j');
				const token = await recaptcha.execute('room/create');
				
				let verify = await this.socket.request('captcha.verify', {
					action: action,
					token: token,
				});

				return verify.data.success;
			},

			async createRoom() {
				if (this.nameNewRoom === '') {
					this.roomErrorText = 'Empty name.';
					return false;
				}

				if (!this.captchaVerify('room/create')) {
					return false;
				}

				let promise = this.socket.request('room.create', {
					name: this.nameNewRoom,
					owner: this.$root.getUser(),
				}).then((result) => {
					this.rooms.unshift(result.data);
					localStorage.rooms = JSON.stringify(this.rooms);
					this.nameNewRoom = '';
				});

				this.roomErrorText = null;
			},

			async cloneRoom(id, cloneName) {
				let name = prompt(`Enter the new room name`, cloneName);

				if (name === '') {
					return false;
				}

				if (!this.captchaVerify('room/create')) {
					return false;
				}

				let promise = this.socket.request('room.create', {
					name: name,
					owner: this.$root.getUser(),
					clone: id,
				}).then((result) => {
					this.rooms.unshift(result.data);
					localStorage.rooms = JSON.stringify(this.rooms);
				});
			},

			deleteRoom(id) {
				if (confirm("Delete room?")) {
					let promise = this.socket.request('room.delete', {
						id: id,
						owner: this.$root.getUser(),
					}).then((result) => {
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
			
			setPassword(id) {
				let password = prompt(`Enter the password for the room. Empty password will unlock the room.`);

				if (password !== null) {
					let promise = this.socket.request('room.update', {
						id: id,
						owner: this.$root.getUser(),
						password: password,
					})
						.then((result) => {
							for (var i = 0; i < this.rooms.length; i++) {
								if (this.rooms[i].id == result.data.id) {
									this.rooms.splice(i, 1, result.data);
									break;
								}
							}
							localStorage.rooms = JSON.stringify(this.rooms);
						});
				}
			},

			copyUid() {
				global.navigator.clipboard.writeText(this.$root.getUser());
			},

			changeUid() {
				let uid = prompt(`Enter UID`);

				if (uid === null || uid === '') {
					return alert('Empty UID.');
				}

				if (!uuidValidate(uid)) {
					return alert('Not a valid UID.');
				}

				if (confirm('Are you confident? All your current data will be rebound to the new UID.')) {
					let promise = this.socket.request('room.rebind', {
						owner: this.$root.getUser(),
						new_uid: uid,
					}).then((result) => {
						if (result.data) {
							this.$cookies.set('uid', uid, 10000000*60);
							this.roomsUpdate();
						}
					});
				}
			},
		},
	}
</script>

<style scoped>
	.wn-button {
		cursor: pointer;
	}
</style>
