<template>
	<div class="collapse navbar-collapse" id="navbarNav">
		<ul class="navbar-nav">
			<li v-for="item in list" :key="item.route" class="nav-item" :class="{active: isCurrent(item.route)}">
				<router-link class="nav-link" :to="{name: item.route, params: item.params}">
					{{item.title}}<span v-if="isCurrent(item.route)" class="sr-only">(current)</span>
				</router-link>
			</li>
		</ul>
	</div>
</template>

<script>
	export default {
		props: [],

		data () {
			return {
				list: [
					{
						title: 'Home',
						route: 'home',
						params: {},
					},
					{
						title: 'News',
						route: 'news',
						params: {},
					},
					{
						title: 'About',
						route: 'about',
						params: {},
					},
				],
			}
		},

		created () {
			
		},

		methods: {
			isCurrent(route) {
				return this.$route.name === route;
			},
		},
	}
</script>

<style>
	
</style>
