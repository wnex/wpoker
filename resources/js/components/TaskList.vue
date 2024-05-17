<template>
	<transition name="fade">
		<div v-if="tasks.length || room.isOwner">
			<h4 class="d-flex justify-content-between align-items-center mb-3">
				<span>Tasks</span>
				<div>
					<span class="badge bg-secondary-subtle text-secondary-emphasis" title="Completed tasks/Number of tasks">{{completedTasks}}/{{tasks.length}}</span>
					<span class="badge bg-primary-subtle text-primary-emphasis" title="Amount story points">{{amount}}</span>
				</div>
			</h4>

			<transition name="fade">
				<task-form 
					v-show="room.isOwner && !edit.enabled"
					@submit="add"
					:text="text"
					placeholder="Enter text new task"
					button="Add task"
				></task-form>
			</transition>

			<task-form 
				v-show="room.isOwner && edit.enabled"
				@submit="editSave"
				:text="edit.text"
				placeholder="Enter text"
				button="Save"
				:isEdit="true"
				ref="editForm"
			></task-form>
				
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
						:class="{'list-group-item-secondary': task.story_point_view, 'list-group-item-primary': task.id === (room.task ? room.task.id : 0)}"
					>
						<div>
							<span class="text-muted">#{{task.order}}</span>
							<vue-markdown :html="false" :anchorAttributes="anchorAttributes" :source="task.text"></vue-markdown>
						</div>
						
						<span class="control text-muted">
							<span v-if="room.isOwner" class="control-owner">
								<i class="fa fa-fw fa-align-justify handle" title="Sort"></i>
								<i class="fa fa-fw fa-pencil button-icon" title="Edit" @click.stop="editInit(task.id)"></i>
								<i class="fa fa-fw fa-trash-o button-icon" title="Delete" @click="remove(task.id)"></i>
							</span>
							<span v-if="task.story_point_view && !room.isOwner" class="story-point" title="Story points">
								<vue-markdown
									class="view"
									:html="false"
									:anchorAttributes="anchorAttributes"
									:source="task.story_point_view"
								></vue-markdown>
							</span>
							<div v-if="task.story_point_view && room.isOwner" class="btn-group" role="group">
								<div class="btn-group" role="group">
									<span class="story-point btn btn-primary dropdown-toggle" title="Story points" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<vue-markdown
											class="view"
											:html="false"
											:anchorAttributes="anchorAttributes"
											:source="task.story_point_view"
										></vue-markdown>
									</span>
									<div class="dropdown-menu">
										<button v-for="(card, index) in cards" :key="index" @click="editStoryPoint(task.id, card.point, card.view)" class="dropdown-item btn-sm" type="button">
											<vue-markdown
												class="view"
												:html="false"
												:anchorAttributes="anchorAttributes"
												:source="card.view"
											></vue-markdown>
										</button>
									</div>
								</div>
							</div>
						</span>
					</li>
				</transition-group>
			</draggable>
		</div>
	</transition>
</template>

<script>
	import VueMarkdown from 'vue-markdown';
	import Draggable from 'vuedraggable';
	import TaskForm from '@/js/components/TaskForm.vue';
	import CardSets from '@/js/modules/CardSets.ts';

	import Prism from 'prismjs';
	import 'prismjs/themes/prism.css';

	import 'prismjs/components/prism-markup-templating.js';
	import 'prismjs/components/prism-php';
	import 'prismjs/components/prism-bash';
	
	export default {
		props: ['socket', 'room'],

		components: {
			VueMarkdown,
			Draggable,
			TaskForm,
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
			textFocused: false,
			cards: [],
			cardsets: {},
		}),

		mounted: function() {
			this.cardsets = CardSets;
			this.socket.group('task');

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
						this.$set(this.tasks[i], 'story_point_view', data.view);
						this.save();
						break;
					}
				}
				this.$emit('approve', data);
			});

			this.socket.endGroup();
		},

		destroyed() {
			this.socket.offGroup('task');
		},

		methods: {
			init() {
				if (localStorage['tasks-'+this.room.hash]) {
					this.tasks = JSON.parse(localStorage['tasks-'+this.room.hash]);
				}

				let promise = this.socket.request('task.get', {
					room: this.room.hash,
				})
					.then((result) => {
						this.tasks = result.data;
						this.tasksSort();
					});
			},

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

			add(data) {
				this.text = data.text;

				if (this.text === '') {
					return false;
				}

				let promise = this.socket.request('task.create', {
					text: this.text,
					user: this.$root.getUser(),
					room: this.room.hash,
				})
					.then((result) => {
						this.text = '';
					});
			},

			remove(id) {
				if (confirm("Remove task?")) {
					let promise = this.socket.request('task.delete', {
						id: id,
						user: this.$root.getUser(),
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
						this.$nextTick(() => {
							this.$refs.editForm.focus();
						});
						break;
					}
				}
			},

			editSave(data) {
				this.edit.text = data.text;

				let promise = this.socket.request('task.update', {
					id: this.edit.id,
					text: this.edit.text,
					user: this.$root.getUser(),
				})
					.then((result) => {
						this.edit.enabled = false;
						this.edit.text = '';
						this.edit.id = '';
					});
			},

			editStoryPoint(id, story_point, story_point_view) {
				let promise = this.socket.request('task.update', {
					id: id,
					story_point: story_point,
					story_point_view: story_point_view,
					user: this.$root.getUser(),
				});
			},

			save() {
				localStorage['tasks-'+this.room.hash] = JSON.stringify(this.tasks);
			},

			saveInServer() {
				this.socket.send({
					action: 'room.task.update.all',
					room: this.room.hash,
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

			completedTasks() {
				let count = 0;
				for (var i = 0; i < this.tasks.length; i++) {
					if (this.tasks[i].story_point !== null && this.tasks[i].story_point !== undefined) {
						count++;
					}
				}
				return count;
			},

			haveUnratedTasks() {
				for (var i = 0; i < this.tasks.length; i++) {
					if (this.tasks[i].story_point === null || this.tasks[i].story_point === undefined) {
						return true;
					}
				}

				return false;
			},

			amount() {
				let amount = 0;
				for (var i = 0; i < this.tasks.length; i++) {
					if (this.tasks[i].story_point !== null && this.tasks[i].story_point !== undefined) {
						amount += parseFloat(this.tasks[i].story_point);
					}
				}
				return amount;
			},
		},

		watch: {
			'room.id'() {
				if (this.room.id !== undefined && this.room.id !== null) {
					this.init();
				}
			},
			'room.cardset'(n, o) {
				if (n.name !== undefined) {
					if (n.name === 'Custom') {
						this.cards = n.cards;
					} else {
						for (let name in this.cardsets) {
							if (name === n.name) {
								this.cards = this.cardsets[name];
								break;
							}
						}
					}
				} else {
					this.cards = this.cardsets.Default;
				}
			},
		},
	}
</script>

<style scoped>
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
	.task {
		padding: 10px;
	}
	.task-complite {
		background-color: #eef6ff;
	}
	.task-active {
		background-color: #bbdbff;
	}
	.story-point {
		padding: 1px 6px;
		background-color: #007bff;
		color: #fff;
		border-radius: 3px;
	}
	.story-point div {
		display: inline-block;
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

	.dropdown-menu {
		min-width: auto;
	}
</style>
