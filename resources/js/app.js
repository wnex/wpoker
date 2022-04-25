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

		this.socket.close(() => {
			this.disconnect = true;
		});

		this.socket.open(() => {
			this.disconnect = false;

			this.socket.send({
				'action': 'user.connect',
				'name': this.name,
				'uid': this.getUser(),
			});
		});
	},

	mounted: function() {
		if (localStorage.name) {
			this.name = localStorage.name;
		}
	},

	methods: {
		changedName(value) {
			localStorage.name = this.name = value;
		},

		getUser() {
			return this.$cookies.get('uid');
		},
	},
});

export default app;
