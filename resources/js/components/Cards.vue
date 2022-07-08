<template>
	<div class="row mb-0 pt-0" :class="{'approve': approve}">
		<div v-if="room.isOwner" class="btn-toolbar col-12 d-flex justify-content-between align-items-center mb-2">
			<div class="btn-group mr-3" role="group">
				<div class="btn-group" role="group">
					<button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ (room.cardset && room.cardset.name) || 'Default' }}</button>
					<div class="dropdown-menu">
						<button v-for="(cardset, index) in cardsets" :key="index" @click="setCardSet(index)" class="dropdown-item btn-sm" type="button">
							{{ index }}
						</button>
					</div>
				</div>
			</div>
			<div class="btn-group" role="group">
				<button v-if="!editing" type="button" @click="editing = !editing" class="btn btn-sm" :class="{'btn-success': editing, 'btn-outline-success': !editing}">Edit card set</button>
				<button v-if="editing" type="button" @click="saveCardSet" class="btn btn-sm btn-success">Save card set</button>
			</div>
		</div>

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
			<div v-if="approve" class="alert alert-warning mb-2" role="alert">
				You must confirm the grade for the current task in order to proceed.
			</div>
		</div>

		<div
			v-for="(card, index) in cards"
			:key="card.view"
			class="poker-card mb-3 d-flex col-3 col-md-2 justify-content-between"
		>
			<div class="flip-container" :class="{'hover': canVote || approve || editing}">
				<div class="flipper">
					<div class="front">
						<img :src="cover" @click="cardShake(card.view)" :class="{'shake': card.shake}" width="100%">
					</div>
					<div class="back" :class="{'editable': editing}">
						<div class="poker-card-front" :style="'background-color: '+card.color+';'"  @click.prevent="vote(card.point, card.view)">
							<div class="poker-card-inner-border"></div>
							<vue-markdown
								v-if="!editing"
								class="view"
								:html="false"
								:anchorAttributes="anchorAttributes"
								:source="card.view"
							></vue-markdown>
							<div v-if="editing">
								<div class="input-group input-group-sm mb-1">
									<input v-model="card.view" type="text" class="form-control" aria-label="Small" aria-describedby="view">
								</div>
								<div class="input-group input-group-sm mb-1">
									<input v-model="card.point" type="text" class="form-control" aria-label="Small" aria-describedby="point">
								</div>
								<div class="input-group input-group-sm mb-1">
									<input v-model="card.color" type="text" class="form-control" aria-label="Small" aria-describedby="color">
								</div>
								<div class="input-group input-group-sm mb-1">
									<button type="button" @click="deleteCard(index)" class="btn btn-danger btn-sm">Delete card</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div v-if="editing" class="poker-card mb-2 d-flex col-3 col-md-2 pt-3 justify-content-between">
			<div class="flip-container hover">
				<div class="flipper">
					<div class="front">
						<img :src="cover" width="100%">
					</div>
					<div class="back">
						<div class="poker-card-front new-card" @click="newCard()">
							<div class="poker-card-inner-border"></div>
							<span>New</span>
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

	import Editable from '@/js/components/Editable';

	export default {
		props: ['socket', 'canVote', 'room'],

		components: {
			Editable,
			VueMarkdown,
		},

		data: () => ({
			approve: false,
			cover: '/images/cards/cover_min.png',
			editing: false,
			cards: [],
			cardsets: {
				'Default': [
					{point: 0, view: '0', color: '#cccaff'},
					{point: 0.25, view: '1/4', color: '#8CCB5E'},
					{point: 0.5, view: '1/2', color: '#e87f6d'},
					{point: 1, view: '1', color: '#6992c8'},
					{point: 2, view: '2', color: '#f7abaa'},
					{point: 3, view: '3', color: '#b0c4d1'},
					{point: 5, view: '5', color: '#1db6a1'},
					{point: 8, view: '8', color: '#7575b3'},
					{point: 13, view: '13', color: '#f5e8b6'},
					{point: 0, view: '?', color: '#f9a12f'},
					{point: 0, view: ':coffee:', color: '#76ccea'},
					{point: 0, view: ':dragon:', color: '#f8d37b'},
				],
				'Alternative': [
					{point: 0, view: '0', color: '#cccaff'},
					{point: 0.5, view: '1/2', color: '#8CCB5E'},
					{point: 1, view: '1', color: '#e87f6d'},
					{point: 2, view: '2', color: '#6992c8'},
					{point: 3, view: '3', color: '#f7abaa'},
					{point: 5, view: '5', color: '#b0c4d1'},
					{point: 8, view: '8', color: '#1db6a1'},
					{point: 13, view: '13', color: '#7575b3'},
					{point: 20, view: '20', color: '#f5e8b6'},
					{point: 40, view: '40', color: '#f9a12f'},
					{point: 100, view: '100', color: '#76ccea'},
					{point: 0, view: ':dragon:', color: '#f8d37b'},
				],
				'Fibonacci Sequence': [
					{point: 0, view: '0', color: '#cccaff'},
					{point: 1, view: '1', color: '#8CCB5E'},
					{point: 2, view: '2', color: '#e87f6d'},
					{point: 3, view: '3', color: '#6992c8'},
					{point: 5, view: '5', color: '#f7abaa'},
					{point: 8, view: '8', color: '#b0c4d1'},
					{point: 13, view: '13', color: '#1db6a1'},
					{point: 21, view: '21', color: '#7575b3'},
					{point: 34, view: '34', color: '#f5e8b6'},
					{point: 55, view: '55', color: '#f9a12f'},
					{point: 89, view: '89', color: '#76ccea'},
					{point: 144, view: '144', color: '#f8d37b'},
				],
				'Numerical Sequence': [
					{point: 0, view: '0', color: '#cccaff'},
					{point: 1, view: '1', color: '#8CCB5E'},
					{point: 2, view: '2', color: '#e87f6d'},
					{point: 3, view: '3', color: '#6992c8'},
					{point: 4, view: '4', color: '#f7abaa'},
					{point: 5, view: '5', color: '#b0c4d1'},
					{point: 6, view: '6', color: '#1db6a1'},
					{point: 7, view: '7', color: '#7575b3'},
					{point: 8, view: '8', color: '#f5e8b6'},
					{point: 9, view: '9', color: '#f9a12f'},
					{point: 10, view: '10', color: '#76ccea'},
					{point: 11, view: '11', color: '#f8d37b'},
				],
				'Yes/No': [
					{point: 0, view: 'Yes', color: '#8CCB5E'},
					{point: 0, view: 'No', color: '#e87f6d'},
				],
			},
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
			}, 'card');
		},

		destroyed() {
			this.socket.offGroup('card');
		},

		methods: {
			vote(point, view) {
				if (this.editing) {
					return false;
				}

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

			newCard() {
				this.cards.push({view: 'Card '+(this.cards.length+1), point: '0', color: '#'});
			},

			deleteCard(id) {
				if (this.cards.length === 2) {
					alert('Minimum number of cards in a set 2.');
					return false;
				}

				if (confirm('Delete this card?')) {
					this.cards.splice(id, 1);
				}
			},

			saveCardSet() {
				if (this.room.isOwner) {
					let promise = this.socket.request('room.update', {
						id: this.room.id,
						owner: this.$root.getUser(),
						cardset: {name: 'Custom', 'cards': this.cards},
					}).then((result) => {
						this.editing = false;
					});
				}
			},

			canselEditing() {
				this.editing = false;
			},

			setCardSet(name) {
				if (this.room.isOwner) {
					let promise = this.socket.request('room.update', {
						id: this.room.id,
						owner: this.$root.getUser(),
						cardset: {name: name},
					}).then((result) => {
						this.editing = false;
					});
				}
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

		-webkit-touch-callout: none; /* iOS Safari */
		-webkit-user-select: none;   /* Chrome/Safari/Opera */
		-khtml-user-select: none;    /* Konqueror */
		-moz-user-select: none;      /* Firefox */
		-ms-user-select: none;       /* Internet Explorer/Edge */
		user-select: none; 
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

	.poker-card-front div.view {
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

	.editable {
		
	}
	.editable .poker-card-front {
		font-size:  0.7rem;
		font-weight: 100;
		line-height: 10px;
		padding: 5px;
		-webkit-text-stroke: 0;
	}
	.editable input {
		width: 50px;
	}
	.new-card {
		font-size:  2rem;
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
