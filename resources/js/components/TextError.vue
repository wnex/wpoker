<template>
	<div v-if="text !== null" class="alert alert-danger px-3 py-1 mb-2" role="alert">
		{{text}}
	</div>
</template>

<script>
	export default {
		props: ['text'],
	}
</script>
