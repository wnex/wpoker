<template>
	<div>
		<nav class="navbar navbar-expand-lg navbar-dark mb-3">
			<div class="container">
				<router-link link :to="{name: 'home'}" class="navbar-brand">
					<img class="logo" :src="'/images/logo_header.png'">
				</router-link>
				<h1 style="display: none;">Planning Poker</h1>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				
				<menu-list></menu-list>
			</div>
		</nav>
		
		<div class="container">
			<router-view :socket="socket"></router-view>
			
			<footer class="my-5 pt-5 text-muted text-center text-small">
				<statistics :socket="socket" :refresh-every="300"></statistics>
				<p class="mb-1">© {{new Date().getFullYear() > 2020 ? '2020-'+new Date().getFullYear() : new Date().getFullYear()}} wNex</p>
				<ul class="list-inline">
					<li class="list-inline-item"><a href="#"></a></li>
				</ul>
			</footer>
		</div>
		
		<div v-if="disconnect" class="disconnect-alert alert alert-danger" role="alert">
			Connecting...
		</div>
		
		<div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">
			<button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (light)">
				<svg class="bi my-1 theme-icon-active" width="1em" height="1em"><use href="#sun-fill"></use></svg>
				<span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
			</button>
			<ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text" style="">
				<li>
					<button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="light" aria-pressed="true">
						<svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#sun-fill"></use></svg>
						Light
						<svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
					</button>
				</li>
				<li>
					<button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
						<svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
						Dark
						<svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
					</button>
				</li>
				<li>
					<button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto" aria-pressed="false">
						<svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#circle-half"></use></svg>
						Auto
						<svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
					</button>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
	import Socket from '@/js/modules/Socket.ts';
	import MenuList from '@/js/components/MenuList.vue';
	import Statistics from '@/js/components/Statistics.vue';

	export default {
		data: () => ({
			name: '',
			socket: null,
			disconnect: false,
		}),

		components: {
			MenuList,
			Statistics,
		},

		created() {
			this.socket = new Socket(document.body.dataset.socket);
			
			this.socket.onOpen(this.onConnected);
			this.socket.onClose(this.onDisconnected);
		},

		destroyed() {
			this.socket.offOpen(this.onConnected);
			this.socket.offClose(this.onDisconnected);
		},

		mounted() {
			if (localStorage.name) {
				this.name = localStorage.name;
			}
		},

		methods: {
			onConnected() {
				this.disconnect = false;
				
				this.socket.send({
					'action': 'user.connect',
					'name': this.name,
					'uid': this.$root.getUser(),
				});
			},
			
			onDisconnected() {
				this.disconnect = true;
			},
			
			changedName(value) {
				localStorage.name = this.name = value;
			},
			
			
		},
	}
</script>
