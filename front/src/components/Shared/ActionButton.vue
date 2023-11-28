<template>
  <a class="button" @click="handleClick">
    <Icon :src="icon" :interactive="false" />
    {{ buttonText }}
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
</style>
