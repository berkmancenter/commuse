<template>
  <component 
    :is="tagName"
    :class="[
      {
        'commuse-action-button-active': active,
        'running': working,
        'commuse-action-button': true,
        'button': true,
        'ld-ext-right': true,
      },
      ...classesExtra,
    ]"
    @click="handleClick"
    v-bind:disabled="disabled ? true : null"
  >
    <Icon :src="icon" :interactive="false" v-if="icon" />
    <div>{{ buttonText }}</div>
    <div class="ld ld-ring ld-spin"></div>
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
      disabled: {
        type: Boolean,
        required: false,
        default: false,
      },
      working: {
        type: Boolean,
        required: false,
        default: false,
      },
      classesExtra: {
        type: Array,
        required: false,
        default: [],
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
