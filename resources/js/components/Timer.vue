<template>
	<span :title="getFillTime">{{ timeFromNow }}</span>
</template>

<script>
	import moment from 'moment';

	export default {
		props: {
			created: {
				type: String,
				required: true,
			},
			refreshEvery : {
				type: Number,
				default: 5,
			},
		},

		data () {
			return {
				timeFromNow: null,
			}
		},
		created () {
			this.getTimeFromNow();
			setInterval(this.getTimeFromNow, this.refreshEvery * 1000);
		},
		destroyed () {
			clearInterval(this.getTimeFromNow);
		},
		methods: {
			getTimeFromNow() {
				this.timeFromNow = moment(this.created).fromNow();
			},
		},
		computed: {
			getFillTime() {
				return moment(this.created).format('LLL');
			},
		},
	}
</script>
