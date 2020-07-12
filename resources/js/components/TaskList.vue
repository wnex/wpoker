<template>
	<transition name="fade">
		<div v-if="tasks.length || isOwner">
			<h4 class="d-flex justify-content-between align-items-center mb-3">
				Tasks
			</h4>
			<transition name="fade">
				<form v-if="isOwner && !edit.enabled" @submit.prevent="add" class="mb-3">
					<div class="input-group">
						<textarea
							rows="2"
							@keydown.enter.exact.prevent="add"
							class="form-control"
							v-model="text"
							placeholder="Enter text"
						></textarea>
						<div class="input-group-append">
							<button type="submit" class="btn btn-secondary">Add task</button>
						</div>
					</div>
				</form>
			</transition>
			<form v-if="isOwner && edit.enabled" @submit.prevent="editSave" class="mb-3">
				<div class="input-group">
					<textarea
						rows="2"
						@keydown.enter.exact.prevent="editSave"
						class="form-control text-success"
						v-model="edit.text"
						placeholder="Enter text"
					></textarea>
					<div class="input-group-append">
						<button type="submit" class="btn btn-success">Save</button>
					</div>
				</div>
			</form>
			<draggable
				v-model="tasks"
				v-bind="dragOptions"
				@start="drag = true"
				@end="endDrag"
				handle=".handle"
			>
				<transition-group
					tag="ul"
					class="list-group mb-3"
					type="transition"
					:name="!drag ? 'flip-list' : null"
				>
					<li
						v-for="task in tasks"
						:key="task.id"
						class="list-group-item d-flex justify-content-between lh-condensed task"
						:class="{'text-success': task.story_point}"
					>
						<div>
							<span class="text-muted">#{{task.order}}</span>
							<vue-markdown :html="false" :anchorAttributes="anchorAttributes" :source="task.text"></vue-markdown>
						</div>
						
						<span class="control text-muted">
							<span v-if="isOwner" class="control-owner">
								<i class="fa fa-fw fa-align-justify handle" title="Sort"></i>
								<i class="fa fa-fw fa-pencil button-icon" title="Edit" @click="editInit(task.id)"></i>
								<i class="fa fa-fw fa-trash-o button-icon" title="Delete" @click="remove(task.id)"></i>
							</span>
							<span v-if="task.story_point" class="story-point" title="Story points">{{task.story_point}}</span>
						</span>
					</li>
				</transition-group>
			</draggable>
		</div>
	</transition>
</template>

<script>
	import VueMarkdown from 'vue-markdown';
	import Draggable from 'vuedraggable'

	import Prism from 'prismjs';
	import 'prismjs/themes/prism.css';

	import 'prismjs/components/prism-markup-templating.js';
	import 'prismjs/components/prism-php';
	import 'prismjs/components/prism-bash';
	
	export default {
		props: ['socket', 'room', 'id', 'isOwner'],

		components: {
			VueMarkdown,
			Draggable,
		},

		data: () => ({
			drag: false,
			text: '',
			tasks: [],
			edit: {
				enabled: false,
				text: '',
				id: '',
			},
		}),

		mounted: function() {
			if (localStorage['tasks-'+this.room]) {
				this.tasks = JSON.parse(localStorage['tasks-'+this.room]);
			}

			let promise = this.socket.request('task.get', {
				room: this.room,
			})
				.then((result) => {
					this.tasks = result.data;
					this.tasksSort();
				});

			this.socket.listener('room.task.add', (data) => {
				this.tasks.push(data.task);
				this.tasksSort();

				this.$nextTick(() => {
					Prism.highlightAll();
				});
			});

			this.socket.listener('room.task.remove', (data) => {
				for (var i = 0; i < this.tasks.length; i++) {
					if (this.tasks[i].id === data.id) {
						this.tasks.splice(i, 1);
						this.save();
						break;
					}
				}
			});

			this.socket.listener('room.task.update', (data) => {
				for (var i = 0; i < this.tasks.length; i++) {
					if (this.tasks[i].id === data.task.id) {
						this.$set(this.tasks, i, data.task);
						this.save();
						break;
					}
				}
			});

			this.socket.listener('room.task.update.all', (data) => {
				this.tasks = data.tasks;
				this.tasksSort();
			});

			this.socket.listener('room.task.approve', (data) => {
				for (var i = 0; i < this.tasks.length; i++) {
					if (this.tasks[i].id === data.id) {
						this.$set(this.tasks[i], 'story_point', data.point);
						this.save();
						break;
					}
				}
			});
		},

		methods: {
			endDrag() {
				this.drag = false;

				for (var i = 0; i < this.tasks.length; i++) {
					let item = this.tasks[i];
					item.order = i + 1;
					this.$set(this.tasks, i, item);
				}

				this.saveInServer();
				this.save();
			},

			add() {
				if (this.text === '') {
					alert('Error! Empty text task.');
					return false;
				}

				let promise = this.socket.request('task.create', {
					text: this.text,
					owner: this.$root.getUser(),
					room: this.room,
					order: this.tasks.length + 1,
				})
					.then((result) => {
						
					});
				this.text = '';
			},

			remove(id) {
				if (confirm("Remove task?")) {
					let promise = this.socket.request('task.delete', {
						id: id,
						owner: this.$root.getUser(),
					})
						.then((result) => {
							for (var i = 0; i < this.tasks.length; i++) {
								if (this.tasks[i].id === id) {
									this.tasks.splice(i, 1);
									this.save();
									break;
								}
							}
						});
				}
			},

			editInit(id) {
				for (var i = 0; i < this.tasks.length; i++) {
					if (this.tasks[i].id === id) {
						this.edit.enabled = true;
						this.edit.id = this.tasks[i].id;
						this.edit.text = this.tasks[i].text;
						break;
					}
				}
			},

			editSave() {
				let promise = this.socket.request('task.update', {
					id: this.edit.id,
					text: this.edit.text,
					owner: this.$root.getUser(),
				})
					.then((result) => {
						this.edit.enabled = false;
						this.edit.text = '';
						this.edit.id = '';
					});
			},

			save() {
				localStorage['tasks-'+this.room] = JSON.stringify(this.tasks);
			},

			saveInServer() {
				this.socket.send({
					action: 'room.task.update.all',
					room: this.room,
					tasks: this.tasks,
					owner: this.$root.getUser(),
				});
			},

			tasksSort() {
				this.tasks.sort(function(a, b) { 
					return a.order - b.order;
				});
				this.save();
			},
		},

		computed: {
			dragOptions() {
				return {
					animation: 200,
					group: "description",
					disabled: false,
					ghostClass: "ghost"
				};
			},

			anchorAttributes() {
				return {
					target: 'blank',
					rel: 'nofollow',
				}
			},

			haveUnratedTasks() {
				for (var i = 0; i < this.tasks.length; i++) {
					if (this.tasks[i].story_point === null || this.tasks[i].story_point === undefined) {
						return true;
					}
				}

				return false;
			},
		},
	}
</script>

<style scoped>
	textarea {
		min-height: 38px;
	}
	.button {
		margin-top: 35px;
	}
	.flip-list-move {
		transition: transform 0.5s;
	}
	.no-move {
		transition: transform 0s;
	}
	.ghost {
		opacity: 0.5;
		background: #c8ebfb;
	}
	.handle {
		cursor: move;
	}
	.button-icon {
		cursor: pointer;
	}
	.control {
		position: absolute;
		top: 10px;
		right: 10px;
	}
	.text-success {
		background-color: #e1fde8;
	}
	.story-point {
		padding: 1px 6px;
		background-color: #28a745;
		color: #fff;
		border-radius: 3px;
	}
	.task .control-owner {
		opacity: 0;
		transition: opacity .5s;
	}
	.task:hover .control-owner {
		opacity: 1;
	}

	.fade-enter-active {
		transition: all .5s;
	}
	.fade-enter, .fade-leave-to {
		opacity: 0;
	}
</style>
