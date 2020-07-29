import './bootstrap';
import Vue from 'vue';

import Socket from '@/js/modules/Socket';
import Routes from '@/js/routes.js';

import App from '@/js/views/App';
import MenuList from '@/js/components/MenuList';
import Statistics from '@/js/components/Statistics';

const app = new Vue({
	el: '#app',
	router: Routes,
	//render: h => h(App),

	components: {
		App,
		MenuList,
		Statistics,
	},

	data: {
		name: '',
		socket: null,
	},

	created: function() {
		this.socket = new Socket(document.body.dataset.socket);
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
			return this.getCookie('user');
		},

		getCookie(name) {
			let matches = document.cookie.match(new RegExp(
				"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
			));
			return matches ? decodeURIComponent(matches[1]) : undefined;
		},
	},
});

export default app;
