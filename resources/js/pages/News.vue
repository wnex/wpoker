<template>
	<div>
		<h4 class="d-flex justify-content-between align-items-center my-4">
			<span class="text-muted">News</span>
			<span class="badge badge-secondary">{{news.length}}</span>
		</h4>
		<div v-for="newItem in news" :key="newItem.id" class="card mb-4">
			<div class="card-body">
				<h5 class="card-title">{{newItem.title}}</h5>
				<vue-markdown
					:html="false"
					:anchorAttributes="anchorAttributes"
					:source="newItem.text"
				></vue-markdown>
				<p class="card-text"><small class="text-muted">Created at <timer :created="newItem.created_at"></timer></small></p>
			</div>
		</div>
	</div>
</template>

<script>
	import Timer from '@/js/components/Timer.vue';

	import VueMarkdown from 'vue-markdown';

	import Prism from 'prismjs';
	import 'prismjs/themes/prism.css';

	import 'prismjs/components/prism-markup-templating.js';
	import 'prismjs/components/prism-php';
	import 'prismjs/components/prism-bash';

	export default {
		props: ['socket'],

		components: {
			VueMarkdown,
			Timer,
		},

		data: () => ({
			news: [],
		}),

		mounted: function() {
			let promise = this.socket.request('new.get', {})
				.then((result) => {
					this.news = result.data;
				});

			this.$root.setTitle();
		},

		methods: {
			
		},

		computed: {
			anchorAttributes() {
				return {
					target: 'blank',
					rel: 'nofollow',
				}
			},
		},
	}
</script>
