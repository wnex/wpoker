<template>
	<vue-markdown
		:html="false"
		:anchorAttributes="anchorAttributes"
		:source="'##### About'"
	></vue-markdown>
</template>

<script>
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
