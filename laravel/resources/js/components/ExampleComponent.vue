<template>
  <div class="example-component">
    <h1>{{ title }}</h1>
    <p>This is an example Vue component in your Laravel application.</p>
    <button @click="incrementCount">Count: {{ count }}</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      title: 'Example Component',
      count: 0
    }
  },
  methods: {
    incrementCount() {
      this.count++
    }
  }
}
</script>

<style scoped>
.example-component {
  padding: 20px;
  background-color: #f8f9fa;
  border-radius: 5px;
  margin: 20px 0;
}
</style>
