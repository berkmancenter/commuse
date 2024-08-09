<template>
  <VueFinalModal
    class="commuse-modal"
    :class="{ 'commuse-modal-vertically-aligned': centerVertically }"
    content-class="commuse-modal-content"
    overlay-transition="vfm-fade"
    content-transition="vfm-fade"
    :clickOutside="clickOutside"
  >
    <div class="commuse-modal-title is-size-4">
      {{ title }}
    </div>

    <div class="commuse-modal-content-slot mt-5">
      <slot />
    </div>

    <div class="commuse-modal-buttons pt-5 mt-5">
      <button class="commuse-modal-buttons-confirm button is-success ld-ext-right" :class="{ running: working }" v-if="showConfirmButton" accesskey="s" @click="$emit('confirm')" ref="confirmButton">
        {{ confirmButtonTitle }}
        <div class="ld ld-ring ld-spin"></div>
      </button>

      <button class="button ml-2" @click="$emit('cancel')" v-if="showCancelButton">
        {{ cancelButtonTitle }}
      </button>
    </div>
  </VueFinalModal>
</template>

<script>
  import { VueFinalModal } from 'vue-final-modal'
  import { waitUntil } from '@/lib/wait_until'

  export default {
    name: 'Modal',
    data() {
      return {
        working: false,
      }
    },
    props: {
      title: {
        type: String,
        required: true,
      },
      confirmButtonTitle: {
        type: String,
        required: false,
        default: 'Confirm',
      },
      cancelButtonTitle: {
        type: String,
        required: false,
        default: 'Cancel',
      },
      focusOnConfirm: {
        type: Boolean,
        required: false,
        default: true,
      },
      centerVertically: {
        type: Boolean,
        required: false,
        default: true,
      },
      showConfirmButton: {
        type: Boolean,
        required: false,
        default: true,
      },
      showCancelButton: {
        type: Boolean,
        required: false,
        default: true,
      },
      clickOutside: {
        type: Function,
        required: false,
        default: () => {},
      },
    },
    components: {
      VueFinalModal,
    },
    created() {
      this.mitt.on('modalIsWorking', () => { this.working = true })
      this.mitt.on('modalIsNotWorking', () => { this.working = false })
    },
    updated() {
      this.setupFocusOnConfirm()
    },
    methods: {
      async setupFocusOnConfirm() {
        if (this.focusOnConfirm) {
          await waitUntil(() => {
            return this.$refs.confirmButton
          })

          this.$refs.confirmButton.focus()
        }
      },
    },
  }
</script>

<style lang="scss">
  .commuse-modal {
    display: flex;
    justify-content: center;

    &.commuse-modal-vertically-aligned {
      align-items: center;
    }
  }

  .commuse-modal-title {
    border-radius: 1rem;
    box-shadow: rgba(17, 18, 54, 0.16) 0px 1px 4px 0px;
    padding: 0.25rem 1rem;
  }

  .commuse-modal-content {
    padding: 1rem;
    background: #ffffff;
    border-radius: 0.5rem;
    width: 32em;
    max-width: 100%;
    max-height: 80vh;
    overflow-y: auto;
    margin: 1rem 0;
  }

  .commuse-modal-content > * + *{
    margin: 0.5rem 0;
  }

  .dark .commuse-modal-content {
    background: #000000;
  }

  .commuse-modal-buttons {
    border-top: 1px solid #dbdbdb;

    .commuse-modal-buttons-confirm {
      &.button.is-success:focus:not(:active),
      &.button.is-success.is-focused:not(:active) {
        box-shadow: 0 0 0 0.4rem #48c78e40;
      }
    }
  }
</style>
