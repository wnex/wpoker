import './bootstrap';
import '../sass/app.scss'; 
import Vue from 'vue';

import Routes from '@/js/routes.js';

import App from '@/js/views/App.vue';

import VueCookies from 'vue-cookies';
Vue.use(VueCookies);

const app = new Vue({
	render: (h) => h(App),
	router: Routes,

	methods: {
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
	}
}).$mount('#app');

export default app;
