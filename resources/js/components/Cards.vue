<template>
	<div class="row mb-3 pt-3" :class="{'approve': approve}">
		<transition name="fade">
			<div v-if="task" class="mb-3 col-12">
				<vue-markdown
					:html="false"
					:anchorAttributes="anchorAttributes"
					:source="task.text"
					class="alert alert-primary mb-0 p-2"
				></vue-markdown>
			</div>
		</transition>

		<div class="col-12">
			<div v-if="approve" class="font-weight-bolder" role="alert">
				You must confirm the grade for the current task in order to proceed.
			</div>
		</div>

		<div
			v-for="card in cards"
			:key="card.view"
			class="poker-card mb-3 d-flex col-3 col-md-2 pt-3 justify-content-between"
		>
			<div class="flip-container" :class="{'hover': canVote || approve}">
				<div class="flipper">
					<div class="front">
						<img :src="cover" @click="cardShake(card.view)" :class="{'shake': card.shake}" width="100%">
					</div>
					<div class="back">
						<img :src="card.src" @click="vote(card.point, card.view)" width="100%">
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import VueMarkdown from 'vue-markdown';

	import Prism from 'prismjs';
	import 'prismjs/themes/prism.css';

	import 'prismjs/components/prism-markup-templating.js';
	import 'prismjs/components/prism-php';
	import 'prismjs/components/prism-bash';

	export default {
		props: ['socket', 'canVote', 'room', 'task'],

		components: {
			VueMarkdown,
		},

		data: () => ({
			approve: false,
			cover: '/images/cards/cover.png',
			cards: [
				{
					src: '/images/cards/0.25.png',
					point: 0.25,
					view: '1/4',
				},
				{
					src: '/images/cards/0.5.png',
					point: 0.5,
					view: '1/2',
				},
				{
					src: '/images/cards/1.png',
					point: 1,
					view: '1',
				},
				{
					src: '/images/cards/2.png',
					point: 2,
					view: '2',
				},
				{
					src: '/images/cards/3.png',
					point: 3,
					view: '3',
				},
				{
					src: '/images/cards/5.png',
					point: 5,
					view: '5',
				},
				{
					src: '/images/cards/8.png',
					point: 8,
					view: '8',
				},
				{
					src: '/images/cards/13.png',
					point: 13,
					view: '13',
				},
				{
					src: '/images/cards/dragon.png',
					point: 0,
					view: '?',
				},
				{
					src: '/images/cards/infinity.png',
					point: 0,
					view: 'ထ',
				},
				{
					src: '/images/cards/0.png',
					point: 0,
					view: '0',
				},
			],
		}),

		mounted: function() {
			this.socket.listener('room.card.shake', (data) => {
				for (var i = 0; i < this.cards.length; i++) {
					if (this.cards[i].view === data.view) {
						this.$set(this.cards[i], 'shake', true);

						setTimeout(() => {
							this.$set(this.cards[i], 'shake', false);
						}, 500);
						break;
					}
				}
			});
		},

		methods: {
			vote(point, view) {
				if (this.canVote) {
					this.selectPoint = point;
					this.socket.send({
						'action': 'room.vote',
						'room': this.room,
						'point': point,
						'view': view,
					});
				}

				if (this.approve) {
					this.socket.send({
						'action': 'room.task.approve',
						'id': this.task.id,
						'point': point,
						'view': view,
					});
					this.stopApprove();
				}
			},

			startApprove() {
				if (this.task !== null) {
					this.approve = true;
				}
			},

			stopApprove() {
				this.approve = false;
			},

			// Easter eggs
			cardShake(view) {
				this.socket.send({
					'action': 'room.card.shake',
					'view': view,
					'room': this.room,
				});
			},
		},

		computed: {
			anchorAttributes() {
				return {
					target: 'blank',
					rel: 'nofollow',
				}
			},
		},

		watch: {
			task() {
				this.$nextTick(() => {
					Prism.highlightAll();
				});
			},
		},
	}
</script>

<style scoped>
	.fade-enter-active {
		transition: all .5s;
	}
	.fade-enter, .fade-leave-to {
		opacity: 0;
	}

	.poker-card img {
		cursor: pointer;
		border-radius: 5px;
	}

	.approve {
		background-color: #cdffb6;
	}

	/* entire container, keeps perspective */
	.flip-container {
		perspective: 1000px;
	}
		/* flip the pane when hovered */
		.flip-container.hover .flipper {
			transform: rotateY(180deg);
		}

	.flip-container, .front, .back {
		/* width: 96px;
		height: 144px; */
		width: 100%;
		/* height: 100vh; */
	}

	/* flip speed goes here */
	.flipper {
		transition: ease-in-out 0.6s;
		transform-style: preserve-3d;

		position: relative;

		width: 100%;
		padding-top: 150%; /* 3:4 Aspect Ratio */
	}

	/* hide back of pane during swap */
	.front, .back {
		backface-visibility: hidden;

		position: absolute;
		top: 0;
		left: 0;
		bottom: 0;
		right: 0;
	}

	/* front pane, placed above back */
	.front {
		z-index: 2;
		/* for firefox 31 */
		transform: rotateY(0deg);
	}

	/* back, initially hidden pane */
	.back {
		transform: rotateY(180deg);
	}

	img.shake {
		animation: shake 0.5s;
		animation-iteration-count: 1;
	}

	@keyframes shake {
		0% { transform: translate(1px, 1px) rotate(0deg); }
		10% { transform: translate(-1px, -2px) rotate(0deg); }
		20% { transform: translate(-2px, 0px) rotate(0deg); }
		30% { transform: translate(2px, 1px) rotate(0deg); }
		40% { transform: translate(1px, -1px) rotate(0deg); }
		50% { transform: translate(-1px, 2px) rotate(0deg); }
		60% { transform: translate(-2px, 1px) rotate(0deg); }
		70% { transform: translate(2px, 1px) rotate(0deg); }
		80% { transform: translate(-1px, -1px) rotate(0deg); }
		90% { transform: translate(1px, 1px) rotate(0deg); }
		100% { transform: translate(1px, -1px) rotate(0deg); }
	}
</style>
