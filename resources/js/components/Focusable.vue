<template>
	<div
		class="card focusable"
		@click="focus"
		v-click-outside="blur"
		:class="{'is-focused': focused}"
	>
		<slot></slot>
		<slot name="main"></slot>

		<transition name="fade">
			<div v-if="focused" class="row input-group-append mt-2 focusable-hidden">
				<div class="col p-0">
					<slot name="footer"></slot>
				</div>
			</div>
		</transition>
	</div>
</template>

<script>
	import Vue from 'vue';
	import ClickOutside from '@/js/directives/click-outside';
	Vue.use(ClickOutside);

	export default {
		props: {
			
		},

		data() {
			return {
				focused: false,
			}
		},

		methods: {
			focus() {
				this.focused = true;
				this.$emit('focused');
			},

			blur() {
				this.focused = false;
				this.$emit('blurred');
			},
		},
	}
</script>

<style scoped>
	.focusable {
		position: relative;
		overflow: hidden;
		padding-bottom: 10px;
		transition: padding-bottom 0.3s;
	}

	.focusable-hidden {
		position: absolute;
		bottom: 10px; left: 10px;
		width: calc(100% - 20px);
	} 

	.is-focused {
		padding-bottom: 50px;
	}

	.fade-enter-active {
		transition: opacity .5s;
	}
	.fade-leave-active {
		transition: opacity 0s;
	}
	.fade-enter, .fade-leave-to {
		opacity: 0;
	}
</style>
