<template>
	<span>Online {{quantity}} users</span>
</template>

<script>
	export default {
		props: {
			socket: {
				required: true,
			},
			refreshEvery: {
				type: Number,
				default: 5,
			},
		},

		data() {
			return {
				quantity: 0,
			}
		},

		created () {
			this.socket.listener('users.online.update', (data) => {
				this.quantity = data.quantity;
			});
		},
	}
</script>
