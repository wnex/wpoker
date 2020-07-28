<script>
	import { Doughnut } from 'vue-chartjs'

	export default {
		extends: Doughnut,
		props: ['chartdata', 'options'],

		mounted () {
			this.update();
		},

		methods: {
			update() {
				this.renderChart(this.chartdata, this.options);
			},
		},
	}
</script>

<style>
</style>
