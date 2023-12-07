<template>
  <a :class="`commuse-action-button button ${classes}`" @click="handleClick">
    <Icon :src="icon" :interactive="false" />
    <div>{{ buttonText }}</div>
  </a>
</template>

<script>
  import Icon from '@/components/Shared/Icon.vue'


  export default {
    components: {
      Icon,
    },
    props: {
      icon: String,
      buttonText: String,
      onClick: Function,
      classes: String,
    },
    methods: {
      handleClick() {
        if (typeof this.onClick === 'function') {
          this.onClick()
        }
      },
    },
  }
</script>

<style>
  .commuse-action-button {
    padding: 0.5rem 0.5rem;

    > * {
      height: 100%;
    }
  }
</style>
