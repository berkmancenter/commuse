<template>
  <div class="commuse-show-more" :class="{ 'commuse-show-more-yes': isLong && !this.showMore }">
    <div>
      <div class="commuse-show-more-text" v-html="text"></div>
      <div>
        <ActionButton class="commuse-show-more-button button mt-2 is-pulled-right" v-if="isLong" :buttonText="showMoreLabel" @click="toggleShowMore" :icon="moreButtonIcon"></ActionButton>
      </div>
    </div>
  </div>
</template>

<script>
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import upIcon from '@/assets/images/up.svg'
  import downIcon from '@/assets/images/down.svg'

  export default {
    name: 'ShowMore',
    props: {
      text: {
        type: String,
        required: true,
      },
      maxLength: {
        type: Number,
        default: 500,
      },
    },
    components: {
      ActionButton,
    },
    data() {
      return {
        showMore: false,
      }
    },
    computed: {
      isLong() {
        return this.text.length > this.maxLength
      },
      showMoreLabel() {
        return this.showMore ? 'Show less' : 'Show more'
      },
      moreButtonIcon() {
        return this.showMore ? upIcon : downIcon
      },
    },
    methods: {
      toggleShowMore() {
        this.showMore = !this.showMore
      },
    }
  }
</script>

<style lang="scss">
  .commuse-show-more-yes {
    .commuse-show-more-text {
      position: relative;
      max-height: 6rem;
      overflow: hidden;

      &:after {
        content: "";
        position: absolute;
        z-index: 1;
        bottom: 0;
        left: 0;
        pointer-events: none;
        background-image: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255, 1) 90%);
        width: 100%;
        height: 4rem;
      }
    }
  }
</style>
