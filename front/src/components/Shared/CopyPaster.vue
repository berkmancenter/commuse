<template>
  <div class="copy-paster" @click="copyToClipboard">
    <div
      class="copy-paster-button"
      title="Click to copy"
    >
      <span class="copy-paster-text">{{ text }}</span>
      <Icon class="copy-paster-icon" :src="clipboardIcon" />
    </div>
  </div>
</template>

<script>
import clipboardIcon from '@/assets/images/clipboard.svg'
import Icon from '@/components/Shared/Icon.vue'

export default {
  name: 'CopyPaster',
  components: {
    Icon,
  },
  props: {
    text: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      clipboardIcon,
    }
  },
  methods: {
    async copyToClipboard() {
      try {
        await window.navigator.clipboard.writeText(this.text)
        this.awn.success('Copied to clipboard.')
      } catch (error) {
        console.error('Failed to copy text: ', error)
      }
    },
  },
}
</script>

<style lang="scss">
  $cl: '.copy-paster';

  #{$cl} {
    &-button {
      display: flex;
      flex-wrap: nowrap;
      align-items: center;
      cursor: pointer;
    }

    &-text {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 30rem;
      width: 100%;
    }

    &-icon {
      margin-left: 0.5rem;
    }
  }
</style>
