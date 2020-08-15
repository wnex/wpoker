<template>
	<focusable class="pt-2 pl-2 pr-2 mb-4" ref="focusable">
		<form v-show="!isPreview" @submit.prevent="submit">
			<div class="input-group">
				<textarea
					@keydown.enter.exact.prevent="submit"
					@focus="$refs.focusable.focus()"
					ref="text"
					class="text-box"
					:class="{'text-success': isEdit}"
					v-model="textLocal"
					:placeholder="placeholder"
					v-autosize="textLocal" rows="1"
				></textarea>
			</div>
		</form>

		<vue-markdown v-if="isPreview" :html="false" :anchorAttributes="anchorAttributes" :source="textLocal"></vue-markdown>

		<template v-slot:footer>
			<button class="btn btn-light btn-sm" v-show="!isPreview" @click.prevent="isPreview = true">Preview</button>
			<button class="btn btn-light btn-sm" v-show="isPreview" @click.prevent="isPreview = false">Edit</button>

			<div v-show="!isEdit" class="btn-group btn-group-toggle" data-toggle="buttons">
				<label class="btn btn-light btn-sm" :class="{'active': mode === 'once'}">
					<input type="radio" @click="mode = 'once'" v-model="mode">Once
				</label>
				<label class="btn btn-light btn-sm" :class="{'active': mode === 'multi'}">
					<input type="radio" @click="mode = 'multi'" v-model="mode">Multi
				</label>
			</div>

			<button class="btn btn-light btn-sm" disabled>
				<i class="fa fa-question-circle-o" aria-hidden="true"></i>
				Markdown supported
			</button>

			<button
				@click.prevent="submit"
				type="submit"
				class="btn btn-sm float-right"
				:class="{'btn-success': isEdit, 'btn-primary': !isEdit}"
				:disabled="textLocal.length === 0"
			>{{button}}</button>
		</template>
	</focusable>
</template>

<script>
	import VueMarkdown from 'vue-markdown';
	import Focusable from '@/js/components/Focusable';

	import Vue from 'vue';
	import VueAutosize from 'autosize-vue';
	Vue.use(VueAutosize);

	import Prism from 'prismjs';
	import 'prismjs/themes/prism.css';

	import 'prismjs/components/prism-markup-templating.js';
	import 'prismjs/components/prism-php';
	import 'prismjs/components/prism-bash';

	export default {
		props: {
			text: {
				type: String,
				required: true,
			},
			placeholder: {
				type: String,
				required: true,
			},
			button: {
				type: String,
				required: true,
			},
			isEdit: {
				type: Boolean,
				default: false,
			},
		},

		components: {
			VueMarkdown,
			Focusable,
		},

		data: () => ({
			textLocal: '',
			isPreview: false,
			mode: 'once',
		}),

		methods: {
			submit() {
				if (this.mode === 'multi') {
					let texts = this.textLocal.split('\n');

					for (var i = 0; i < texts.length; i++) {
						this.$emit('submit', {text: texts[i]});
					}

					return;
				}

				this.$emit('submit', {text: this.textLocal});
			},

			preview() {
				this.isPreview = true;
			},

			focus() {
				this.$refs.text.focus();
				this.$refs.focusable.focus();
			},
		},

		computed: {
			anchorAttributes() {
				return {
					target: 'blank',
					rel: 'nofollow',
				}
			},
		},

		watch: {
			text() {
				this.textLocal = this.text;
			},
		},
	}
</script>

<style scoped>
	textarea.text-box {
		position: relative;
		flex: 1 1 auto;
		width: 1%;
		min-width: 0;
		overflow: hidden;
		overflow-wrap: break-word;
		min-height: 20px;
		line-height: 20px;
		font-size: 14px;
		border: none;
		outline: 0;
		resize: none;
	}
</style>
