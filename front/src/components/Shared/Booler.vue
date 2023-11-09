<template>
  <img class="commuse-booler-icon" :src="yesIcon" v-if="isValTrue()">
  <img class="commuse-booler-icon" :src="noIcon" v-if="!isValTrue()">
</template>

<script>
  import yesIcon from '@/assets/images/yes.svg'
  import noIcon from '@/assets/images/no.svg'

  export default {
    props: {
      value: {
        type: String,
        required: true,
      },
    },
    data() {
      return {
        yesIcon,
        noIcon,
      }
    },
    methods: {
      isValTrue() {
        return this.value === 't'
      },
    },
  }
</script>

<style lang="scss">
  img.commuse-booler-icon {
    max-width: 3rem;
    max-height: 100%;
    padding: 0.5rem;
    vertical-align: middle;
  }
</style>
