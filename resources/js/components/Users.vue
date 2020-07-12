<template>
	<transition name="fade">
		<div v-if="users.length">
			<h4 class="d-flex justify-content-between align-items-center mb-3">
				<span class="text-muted">Users</span>
				<span class="badge badge-secondary">{{users.length}}</span>
			</h4>
			<ul class="list-group mb-3">
				<li v-for="user in users" :key="user.id" class="list-group-item d-flex justify-content-between lh-condensed">
					<div :class="{'text-success': user.isVoted}">
						<h6 class="my-0">
							{{user.name === '' ? 'User #'+user.id : user.name}}
						</h6>
						<small class="text-muted">{{user.isOwner ? 'Owner' : 'Guest'}}</small>
					</div>
					<span class="text-muted">
						<span v-if="isOwner && !user.isOwner" class="badge user-control kick-out" @click="kickOut(user.id)">
							<i class="fa fa-fw fa-sign-out" aria-hidden="true"></i>
						</span>
						<span class="badge user-control badge-primary">{{user.vote}}</span>
					</span>
				</li>

				<li v-if="average !== null" class="list-group-item d-flex justify-content-between">
					<span>Average</span>
					<strong>{{average}}</strong>
				</li>

				<li v-if="chart" class="list-group-item d-flex justify-content-between">
					<div style="width: 100%;"><chart ref="chart" :chartdata="chartData()" :options="chartOptions" /></div>
				</li>
<!-- :width="308" :height="200" -->
				<li class="list-group-item d-flex lh-condensed" style="display: flex; flex-direction: column;">
					<button v-if="!changeNameSwitcher" @click.prevent="changeName" class="btn btn-outline-secondary">Change name</button>
					<form v-if="changeNameSwitcher" @submit.prevent="saveName">
						<div class="input-group">
							<input type="text" v-model="name" class="form-control" placeholder="Enter your name">
							<div class="input-group-append">
								<button type="submit" class="btn btn-secondary">Save</button>
							</div>
						</div>
					</form>
				</li>
			</ul>
		</div>
	</transition>
</template>

<script>
	import Chart from '@/js/components/Chart'

	export default {
		props: ['socket', 'users', 'room', 'isOwner', 'average'],

		components: {
			Chart,
		},

		data: () => ({
			name: '',
			chart: false,
			changeNameSwitcher: false,
			points: ['0.25 sp', '0.5 sp', '1 sp', '2 sp', '3 sp', '5 sp', '8 sp', '13 sp', '0 sp'],
			colors: ['#9561e2', '#e3342f', '#6cb2eb', '#f6993f', '#38c172', '#ffed4a', '#6574cd'],
			chartOptions: {
				legend: {
					'position': 'bottom',
				},
				/*responsive: true,
				maintainAspectRatio: false,*/
			},
		}),

		mounted: function() {
			if (localStorage.name) {
				this.name = localStorage.name;
			}
		},

		methods: {
			changeName() {
				this.changeNameSwitcher = true;
			},

			saveName() {
				if (this.name === '') {
					alert('Error! Empty name.');
					return false;
				}

				localStorage.name = this.name;
				this.changeNameSwitcher = false;

				this.socket.send({
					'action': 'room.user.changeName',
					'name': this.name,
					'room': this.room,
				});
			},

			kickOut(client_id) {
				if (confirm("Kick out this user?")) {
					this.socket.send({
						'action': 'room.user.kick',
						'id': client_id,
						'room': this.room,
					});
				}
			},

			chartData() {
				let list = [],
					count = [];

				for (var i = 0; i < this.users.length; i++) {
					let k = list.indexOf(this.users[i].vote + ' sp');
					if (k === -1) {
						k = list.length;
						list[k] = this.users[i].vote + ' sp';
						count[k] = 1;
					} else {
						count[k]++;
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
	.kick-out {
		cursor: pointer;
	}

	.user-control {
		font-size: 100%;
	}

	.fade-enter-active {
		transition: all .5s;
	}
	.fade-enter, .fade-leave-to {
		opacity: 0;
	}
</style>
