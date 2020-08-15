export let clickOutside = {}

clickOutside.install = function install (_Vue) {
	_Vue.directive('click-outside', {
		bind: function (el, binding, vnode) {
			window.event = function (event) {
				if (!(el == event.target || el.contains(event.target))) {
					vnode.context[binding.expression](event);
				}
			};
			document.body.addEventListener('click', window.event)
		},
		unbind: function (el) {
			document.body.removeEventListener('click', window.event)
		},
	});
}

export default clickOutside;
