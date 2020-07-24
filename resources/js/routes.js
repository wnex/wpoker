import Vue from 'vue';
import VueRouter from 'vue-router';

import Home from '@/js/pages/Home';
import Room from '@/js/pages/Room';
import News from '@/js/pages/News';
import About from '@/js/pages/About';

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
		{
			path: '/news',
			name: 'news',
			component: News,
		},
		{
			path: '/about',
			name: 'about',
			component: About,
		},
	],
});

export default router;
