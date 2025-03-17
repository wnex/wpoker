import './bootstrap';
import Vue from 'vue';

import Socket from '@/js/modules/Socket';
import Routes from '@/js/routes.js';

import App from '@/js/views/App';
import MenuList from '@/js/components/MenuList';
import Statistics from '@/js/components/Statistics';

import VueCookies from 'vue-cookies';
Vue.use(VueCookies);

const app = new Vue({
	el: '#app',
	router: Routes,

	components: {
		App,
		MenuList,
		Statistics,
	},

	data: {
		name: '',
		socket: null,
		disconnect: false,
	},

	created: function() {
		this.socket = new Socket(document.body.dataset.socket);

		this.socket.onOpen(this.onConnected);
		this.socket.onClose(this.onDisconnected);
	},

	destroyed() {
		this.socket.offOpen(this.onConnected);
		this.socket.offClose(this.onDisconnected);
	},

	mounted: function() {
		this.name = this.$cookies.get('username') ?? '';
	},

	methods: {
		onConnected() {
			this.disconnect = false;

			this.socket.send({
				'action': 'user.connect',
				'name': this.name,
				'uid': this.getUser(),
			});
		},

		onDisconnected() {
			this.disconnect = true;
		},

		changedName(value) {
			this.$cookies.set('username', this.name = value);
		},

		getUser() {
			return this.$cookies.get('uid');
		},

		setTitle(name = '') {
			if (name !== '') {
				document.title = name+' - '+global.meta.raw.name;
			} else {
				document.title = global.meta.raw.name+' - '+global.meta.raw.tagline;
			}
		},
	},
});

export default app;
