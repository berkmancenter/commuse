<template>
  <div class="page-not-found">
    <h1 class="title">Page Not Found</h1>
    <h2 class="subtitle">The page you are looking for does not exist.</h2>
    <img :src="notFound">
  </div>
</template>

<script>
  import notFound from '@/assets/images/404.jpeg'

  export default {
    name: 'PageNotFoundIndex',
    data() {
      return {
        notFound,
      }
    },
  }
</script>

<style lang="scss">
  .page-not-found {
    img {
      position: fixed;
      bottom: 0;
      right: 0;
    }
  }
</style>
