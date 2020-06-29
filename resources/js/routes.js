import Vue from 'vue';
import VueRouter from 'vue-router';

import Home from '@/js/pages/Home';
import Room from '@/js/pages/Room';

Vue.use(VueRouter)

const router = new VueRouter({
	mode: 'history',
	routes: [
		{
			path: '/',
			name: 'home',
			component: Home,
		},
		{
			path: '/room/:hash',
			name: 'room',
			component: Room,
			props: true,
		},
	],
});

export default router;
