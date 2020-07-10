<template>
	<span>{{ timeFromNow }}</span>
</template>

<script>
	import moment from 'moment';

	export default {
		props: ['created'],

		data () {
			return {
				timeFromNow: null,
			}
		},
		created () {
			this.getTimeFromNow();
			setInterval(this.getTimeFromNow, 1000);
		},
		destroyed () {
			clearInterval(this.getTimeFromNow);
		},
		methods: {
			getTimeFromNow () {
				this.timeFromNow = moment(this.created).fromNow();
			}
		}
	}
</script>
