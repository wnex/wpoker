<template>
	<div class="row mb-3 pt-0" :class="{'approve': approve}">
		<transition name="fade">
			<div v-if="room.task" class="mb-2 col-12">
				<vue-markdown
					:html="false"
					:anchorAttributes="anchorAttributes"
					:source="room.task.text"
					class="alert alert-primary mb-0 p-2"
				></vue-markdown>
			</div>
		</transition>

		<div class="col-12">
			<div v-if="approve" class="alert alert-warning mt-3 mb-2" role="alert">
				You must confirm the grade for the current task in order to proceed.
			</div>
		</div>

		<div
			v-for="(card, index) in cards"
			:key="card.view"
			class="poker-card mb-2 d-flex col-3 col-md-2 pt-3 justify-content-between"
		>
			<div class="flip-container" :class="{'hover': canVote || approve}">
				<div class="flipper">
					<div class="front">
						<img :src="cover" @click="cardShake(card.view)" :class="{'shake': card.shake}" width="100%">
					</div>
					<div class="back">
						<div class="poker-card-front" :style="'background-color: '+colors[index]+';'"  @click="vote(card.point, card.view)">
							<div class="poker-card-inner-border"></div>
							<span>{{card.view}}</span>
						</div>
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
		props: ['socket', 'canVote', 'room'],

		components: {
			VueMarkdown,
		},

		data: () => ({
			approve: false,
			cover: '/images/cards/cover_min.png',
			colors: [
				'#8CCB5E', '#e87f6d', '#6992c8', '#f7abaa',
				'#b0c4d1', '#1db6a1', '#7575b3', '#f5e8b6',
				'#f9a12f', '#76ccea', '#f8d37b', '#cccaff',
			],
			cards: [
				{point: 0.25, view: '1/4'},
				{point: 0.5, view: '1/2'},
				{point: 1, view: '1'},
				{point: 2, view: '2'},
				{point: 3, view: '3'},
				{point: 5, view: '5'},
				{point: 8, view: '8'},
				{point: 13, view: '13'},
				{point: 21, view: '21'},
				{point: 0, view: '?'},
				{point: 0, view: 'ထ'},
				{point: 0, view: '0'},
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
						'room': this.room.hash,
						'point': point,
						'view': view,
					});
				}

				if (this.approve) {
					this.socket.send({
						'action': 'room.task.approve',
						'id': this.room.task.id,
						'point': point,
						'view': view,
					});
					this.stopApprove();
				}
			},

			startApprove() {
				if (this.room.task !== null) {
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
					'room': this.room.hash,
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
			'room.task'() {
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

	.poker-card > div {
		cursor: pointer;
		border-radius: 5px;
	}

	.poker-card-front {
		position: relative;
		width: 100%;
		height: 100%;
		text-align: center;
		line-height: 100%;
		font-size: 3.3rem;
		letter-spacing: 0px;
		color: #eee;
		white-space: nowrap;
		-webkit-text-stroke: 1px rgba(0,0,0,1);
		border-radius: 5px;

		background: linear-gradient(to left top, rgba(0, 0, 0, 0) 48.9%, rgba(0, 0, 0, .08) 51%, rgba(0, 0, 0, .08) 78%, rgba(0, 0, 0, 0) 80%), linear-gradient(to left top, rgba(0, 0, 0, .08) 28%, rgba(0, 0, 0, 0) 30%);
		background-size: .5em 1.5em;
	}

	@media (max-width: 576px) {
		.poker-card-front {
			font-size: 2rem;
		}
	}

	@media (max-width: 992px) {
		.poker-card-front {
			font-size: 2rem;
		}
	}

	.poker-card-front span {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
	}

	.poker-card-inner-border {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		width: calc(100% - 10px);
		height: calc(100% - 10px);
		border: 1px solid rgba(0, 0, 0, .3);
		border-radius: 5px;
	}

	.approve .back {
		border-radius: 5px;
		animation: neon 1.5s ease-in-out infinite alternate;
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
		width: 100%;
	}

	/* flip speed goes here */
	.flipper {
		transition: ease-in-out 0.6s;
		transform-style: preserve-3d;

		position: relative;

		width: 100%;
		/* 3:4 Aspect Ratio */
		padding-top: 150%;
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
		transition: all 0.5s;
	}

	img.shake {
		animation: shake .5s;
		animation-iteration-count: 1;
	}

	@keyframes shake {
		0% { transform:   rotate(0deg); }
		10% { transform:  rotate(2deg); }
		20% { transform:  rotate(4deg); }
		30% { transform:  rotate(2deg); }
		40% { transform:  rotate(0deg); }
		50% { transform:  rotate(-2deg); }
		60% { transform:  rotate(-4deg); }
		70% { transform:  rotate(-2deg); }
		80% { transform:  rotate(0deg); }
		90% { transform:  rotate(2deg); }
		100% { transform: rotate(0deg); }
	}

	@keyframes neon {
		from {
			/* box-shadow: 0 0 0px #fff, 0 0 0px #007bff, 0 0 10px #007bff; */
			box-shadow: 0 0 10px #007bff;
		}
		to {
			/* box-shadow: 0 0 10px #fff, 0 0 10px #007bff, 0 0 20px #007bff; */
			box-shadow: 0 0 20px #007bff;
		}
	}
</style>
