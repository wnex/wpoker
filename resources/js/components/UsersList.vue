<template>
	<transition name="fade">
		<div v-if="users.length">
			<h4 class="d-flex justify-content-between align-items-center mb-2">
				<span class="text-muted">Users</span>
				<span class="badge badge-secondary">{{users.length}}</span>
			</h4>
			<ul class="list-group mb-3">
				<user v-for="user in users" :key="user.id" :socket="socket" :user="user" :room="room"></user>

				<li v-if="average !== null" class="list-group-item d-flex justify-content-between">
					<span>Average</span>
					<strong>{{average}}</strong>
				</li>

				<li v-if="chart" class="list-group-item d-flex justify-content-between">
					<div style="width: 100%;"><chart ref="chart" :chartdata="chartData()" :options="chartOptions" /></div>
				</li>
			</ul>
		</div>
	</transition>
</template>

<script>
	import Chart from '@/js/components/Chart';
	import User from '@/js/components/User';

	export default {
		props: ['socket', 'users', 'room', 'average'],

		components: {
			User,
			Chart,
		},

		data: () => ({
			chart: false,
			colors: ['#6574cd', '#e3342f', '#38c172', '#f6993f', '#9561e2', '#ffed4a', '#6cb2eb'],
			chartOptions: {
				legend: {
					'position': 'bottom',
				},
			},
		}),

		mounted: function() {
			
		},

		methods: {
			chartData() {
				let list = [],
					count = [];

				for (var i = 0; i < this.users.length; i++) {
					if (this.users[i]['hasVote']) {
						let k = list.indexOf(this.users[i].voteView);
						if (k === -1) {
							k = list.length;
							list[k] = this.users[i].voteView;
							count[k] = 1;
						} else {
							count[k]++;
						}
					}
				}

				return {
					labels: list,
					datasets: [
						{
							label: '',
							backgroundColor: this.colors,
							data: count,
						}
					],
				};
			},
		},

		computed: {
			
		},

		watch: {
			average(value) {
				if (value !== null) {
					this.chart = true;
					this.$nextTick(() => {
						this.$refs.chart.update();
					});
				} else {
					this.chart = false;
				}
			},
		},
	}
</script>

<style scoped>
	.list-group-item {
		padding: 10px;
	}

	.fade-enter-active {
		transition: all .5s;
	}
	.fade-enter, .fade-leave-to {
		opacity: 0;
	}
</style>
