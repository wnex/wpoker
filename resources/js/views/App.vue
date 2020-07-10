<template>
	<router-view :socket="socket"></router-view>
</template>

<script>
	import Socket from '@/js/modules/Socket';

	export default {
		data: () => ({
			socket: null,
		}),

		created: function() {
			this.socket = new Socket(document.body.dataset.socket);
		},
	}
</script>
