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

		<div class="col-12">
			<div v-if="approve" class="alert alert-success mb-2" role="alert">
				You must confirm the vote for the current task.
			</div>
		</div>

		<div
			v-for="(card, index) in cards"
			:key="card.view"
			class="poker-card mb-3 d-flex col-3 col-md-2 justify-content-between"
		>
			<div class="flip-container" :class="{'hover': canVote || (approve && isApprove(card.view)) || editing}">
				<div class="flipper">
					<div class="front" @click="cardShake(card.view)" :class="{'shake': card.shake}"></div>
					<div class="back" :class="{'editable': editing}">
						<div class="poker-card-front" :style="'background-color: '+card.color+';'" :class="{'shake': card.shake}"  @click.prevent="vote(card.point, card.view)">
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
	
	import CardSets from '@/js/modules/CardSets.ts';
	import Editable from '@/js/components/Editable';

	export default {
		props: ['socket', 'canVote', 'room', 'users'],

		components: {
			Editable,
			VueMarkdown,
		},

		data: () => ({
			approve: false,
			cover: '/images/cards/cover_min.png',
			editing: false,
			cards: [],
			cardsets: {},
		}),

		mounted: function() {
			this.cardsets = CardSets;

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

				for (var i = 0; i < this.cards.length; i++) {
					if (this.cards[i].view === view) {
						this.$set(this.cards[i], 'shake', true);

						clearTimeout(this.cards[i]['animation']);
						this.$set(this.cards[i], 'animation', setTimeout(() => {
							this.$set(this.cards[i], 'shake', false);
						}, 500));
						break;
					}
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

			isApprove(view) {
				for (let i = 0; i < this.users.length; i++) {
					const user = this.users[i];
					if (user.voteView === view)
						return true;
				}
			}
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
		/* border-radius: 5px; */
	}

	.poker-card-front {
		position: relative;
		width: 100%;
		height: 100%;
		text-align: center;
		line-height: 100%;
		letter-spacing: 0px;
		color: #eee;
		white-space: nowrap;
		-webkit-text-stroke: 1px rgba(0,0,0,1);
		border-radius: 0.5em;

		background: linear-gradient(to left top, rgba(0, 0, 0, 0) 48.9%, rgba(0, 0, 0, .08) 51%, rgba(0, 0, 0, .08) 78%, rgba(0, 0, 0, 0) 80%), linear-gradient(to left top, rgba(0, 0, 0, .08) 28%, rgba(0, 0, 0, 0) 30%);
		background-size: .5em 1.5em;

		-webkit-touch-callout: none; /* iOS Safari */
		-webkit-user-select: none;   /* Chrome/Safari/Opera */
		-khtml-user-select: none;    /* Konqueror */
		-moz-user-select: none;      /* Firefox */
		-ms-user-select: none;       /* Internet Explorer/Edge */
		user-select: none;

		box-shadow: 0px 0px 5px rgba(0,0,0,0.7);
		outline: 1px solid rgba(0,0,0,0.2);
		outline-offset: -5px;
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
		z-index: 10;
		font-size: 3.3rem;
		transform: translate(-50%, -50%);
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
		/* padding-top: 150%; */
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

	.shake {
		animation: tilt-n-move-shaking2 .5s;
		animation-iteration-count: infinite;
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

	@keyframes tilt-n-move-shaking {
		0% { transform: translate(0, 0) rotate(0deg); }
		25% { transform: translate(5px, 5px) rotate(5deg); }
		50% { transform: translate(0, 0) rotate(0deg); }
		75% { transform: translate(-5px, 5px) rotate(-5deg); }
		100% { transform: translate(0, 0) rotate(0deg); }
	}

	@keyframes tilt-n-move-shaking2 {
		0% { transform: translate(0, 0) rotate(0deg); }
		50% { transform: translate(2px, 10px) rotate(-5deg); }
		100% { transform: translate(0, 0) rotate(0deg); }
	}
</style>


<style lang="scss" scoped>
	$w: 100%;
	$h: 150px;
	$f: $h/$w;
	$n: 7;
	$g: 1em;
	$c0: #015965;//#f2b6a6;//
	$c1: #36BBCE;//#d82c30;//

	@function int_ch($ch0, $ch1) {
		@return calc(
			var(--i)*#{$ch1} + var(--noti)*#{$ch0})
	}

	@function int_col($c0, $c1) {
		$ch: 'red' 'green' 'blue';
		$n: length($ch);
		$args: ();
		
		@each $fn in $ch {
			$args: $args, int_ch(call(get-function($fn), $c0), call(get-function($fn), $c1))
		}
		
		@return RGB(#{$args})
	}

	.flipper .front {
		overflow: hidden;
		//position: relative;
		width: var(--w, #{$w});
		height: var(--h, #{$h});
		border-radius: .5em;
		background: linear-gradient(var(--ang, 180deg), $c0, $c1);
		box-shadow: 0px 0px 5px #000;
		
		&, &:before, &:after {
			--strip-stop: 100%;
			--strip-f: .25;
			--strip-stop-0: calc(var(--strip-f)*var(--strip-stop));
			--strip-stop-1: calc(var(--strip-stop) - var(--strip-stop-0));
			--strip-end: red;
			--strip-mid: transparent;
			--strip-list:
				var(--strip-end) 0,
				var(--strip-end) var(--strip-stop-0), 
				var(--strip-mid) 0, 
				var(--strip-mid) var(--strip-stop-1), 
				var(--strip-end) 0, 
				var(--strip-end) var(--strip-stop);
			--joint-list: 
				var(--joint-end, red) var(--joint-stop, 25%), 
				var(--joint-mid, transparent) 0;
			--joint-0: 
				linear-gradient(135deg, var(--joint-list));
			--joint-1: 
				linear-gradient(-135deg, var(--joint-list));
			--joint-2:
				linear-gradient(-45deg, var(--joint-list));
			--joint-3: 
				linear-gradient(45deg, var(--joint-list));
		}
		
		&:before, &:after {
			--i: 0;
			--noti: calc(1 - var(--i));
			--sgni: calc(2*var(--i) - 1);
			--c: hsl(0, 0%, 0%, var(--i));
			--notc: hsl(0, 0%, 0%, var(--noti));
			--fill: linear-gradient(var(--c), var(--c));
			position: absolute;
			top: 0; right: 0; bottom: 0; left: 0;
			--c0: #{int_col($c0, $c1)};
			--c1: #{int_col($c1, $c0)};
			-webkit-mask: var(--mask);
							mask: var(--mask);
			-webkit-mask-position: var(--mask-o, 50% 50%);
							mask-position: var(--mask-o, 50% 50%);
			-webkit-mask-size: var(--mask-d);
							mask-size: var(--mask-d);
			content: ''
		}
		
		&:after { --i: 1 }
	}

	.flipper .front {
		outline: 1px solid rgba(255,255,255,0.5);
		outline-offset: -5px;

		position: relative;
		--d: 2em;
		--ang: 45deg;
		--strip-stop: calc(.0625*var(--d));
		--narr: var(--strip-list);
		
		&:before, &:after {
			background: var(--c0);
			--strip-end: var(--c);
			--strip-mid: var(--notc);
			--strip-stop: var(--d);
			--mask: 
				linear-gradient(90deg, var(--c), var(--notc)), 
				repeating-linear-gradient(45deg, var(--narr)), 
				repeating-linear-gradient(90deg, var(--strip-list)), 
				repeating-linear-gradient(-45deg, var(--strip-list));
			-webkit-mask-composite: source-in, source-in, source-in, source-over;
							mask-composite: intersect
		}
	}
</style>
