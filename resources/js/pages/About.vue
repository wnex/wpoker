<template>
	<vue-markdown
		:html="true"
		:anchorAttributes="anchorAttributes"
		:source="`##### About

This site has been designed to carry out poker planning within the team. The goal was to develop a user-friendly online scheduling tool so that participants can vote from both a PC and a mobile device. As a result, this project can be used for planning poker online and offline, via mobile devices.

> Planning poker, also called Scrum poker, is a consensus-based, gamified technique for estimating, mostly used for timeboxing in Agile principles. In planning poker, members of the group make estimates by playing numbered cards face-down to the table, instead of speaking them aloud. The cards are revealed, and the estimates are then discussed. By hiding the figures in this way, the group can avoid the cognitive bias of anchoring, where the first number spoken aloud sets a precedent for subsequent estimates.
>
> Planning poker is a variation of the Wideband delphi method. It is most commonly used in agile software development, in particular in Scrum and Extreme Programming.
>
> The method was first defined and named by James Grenning in 2002 and later popularized by Mike Cohn in the book Agile Estimating and Planning, whose company trade marked the term and a digital online tool.
>
>@wikipedia.org`"
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

<style scope>
	blockquote {
		background: #f9f9f9;
		border-left: 10px solid #ccc;
		margin: 1.5em 10px;
		padding: 1em 10px 0.1em 10px;
		quotes: "\201C""\201D""\2018""\2019";
	}
</style>
