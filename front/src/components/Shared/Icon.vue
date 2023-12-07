<template>
  <img class="commuse-icon" :class="{ 'commuse-icon-interactive': interactive }" :src="src">
</template>

<script>
  export default {
    props: {
      src: {
        type: String,
        required: true,
      },
      interactive: {
        type: Boolean,
        required: false,
        default: true,
      },
    },
  }
</script>

<style lang="scss">
  img.commuse-icon {
    max-width: 3rem;
    max-height: 100%;
    padding: 0.5rem;
    vertical-align: middle;

    &-interactive {
      cursor: pointer;
    }

    &-interactive:hover {
      background-color: #fff;
    }
  }

  a.button, button {
    img.commuse-icon {
      padding: 0;
      margin-right: 0.4rem;
      align-self: normal;
    }
  }
</style>
