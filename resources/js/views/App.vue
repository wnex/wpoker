<template>
	<router-view :socket="socket"></router-view>
</template>

<script>
	export default {
		props: {
			socket: {
				required: true,
			},
		},

		data: () => ({
			
		}),
	}
</script>
