<template>
	<focusable class="pt-2 pl-2 pr-2 mb-4 p-2" ref="focusable" @blurred="onBlurred">
		<form v-show="!isPreview" @submit.prevent="submit">
			<div class="input-group">
				<textarea
					@keydown.enter.exact.prevent="submit"
					@paste="onPasteImage"
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
			<button class="btn btn-light btn-sm" v-show="!isPreview && textLocal.length !== 0" @click.prevent="isPreview = true">Preview</button>
			<button class="btn btn-light btn-sm" v-show="isPreview" @click.prevent="isPreview = false">Edit</button>

			<div v-show="!isEdit" class="btn-group" role="group">
				<input type="radio" class="btn-check" name="mode" id="modeOnce" value="once" v-model="mode">
				<label class="btn btn-light btn-sm" for="modeOnce">Once</label>

				<input type="radio" class="btn-check" name="mode" id="modeMulti" value="multi" v-model="mode">
				<label class="btn btn-light btn-sm" for="modeMulti">Multi</label>
			</div>

			<button type="button" class="btn btn-light btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Markdown supported">
				<i class="fa fa-question-circle-o" aria-hidden="true"></i>
			</button>

			<button
				@click.prevent="submit"
				type="submit"
				class="btn btn-sm float-end"
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
			onPasteImage(pasteEvent) {
				if(pasteEvent.clipboardData == false){
				    if(typeof(callback) == "function"){
				        console.log(undefined);
				    }
				};

				var items = pasteEvent.clipboardData.items;

				if(items == undefined){
				    if(typeof(callback) == "function"){
				        console.log(undefined);
				    }
				};

				for (var i = 0; i < items.length; i++) {
				    // Skip content if not image
				    if (items[i].type.indexOf("image") == -1) continue;
				    // Retrieve image on clipboard as blob
				    var blob = items[i].getAsFile();
				    console.log(blob);
				}
			},

			submit() {
				if (this.mode === 'multi') {
					let texts = this.textLocal.split('\n');

					for (var i = 0; i < texts.length; i++) {
						this.$emit('submit', {text: texts[i]});
					}
				} else {
					this.$emit('submit', {text: this.textLocal});
				}
			},

			focus() {
				this.$refs.text.focus();
				this.$refs.focusable.focus();
			},

			onBlurred() {
				this.isPreview = false;
				this.$refs.text.blur();
			}
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

			isPreview() {
				if (this.isPreview) {
					this.$nextTick(() => {
						Prism.highlightAll();
					});
				}
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
		background-color: var(--bs-card-bg);
	}
</style>
