<template>
  <component :is="tagName" class="commuse-action-button button" :class="{ 'commuse-action-button-active': active }" @click="handleClick">
    <Icon :src="icon" :interactive="false" />
    <div>{{ buttonText }}</div>
  </component>
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
      button: {
        type: Boolean,
        required: false,
        default: false,
      },
      active: {
        type: Boolean,
        required: false,
        default: false,
      },
    },
    computed: {
      tagName() {
        if (this.button) {
          return 'button'
        } else {
          return 'a'
        }
      }
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

    &.commuse-action-button-active {
      background-color: var(--super-light-color);
    }
  }
</style>
