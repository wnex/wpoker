import './bootstrap';
import Vue from 'vue';

import Routes from '@/js/routes.js';

import App from '@/js/views/App';
import MenuList from '@/js/components/MenuList';

const app = new Vue({
	el: '#app',
	router: Routes,
	//render: h => h(App),

	components: {
		App: App,
		MenuList: MenuList,
	},

	data: {
		name: '',
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
