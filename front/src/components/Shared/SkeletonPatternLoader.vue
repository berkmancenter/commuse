<template>
  <template v-if="loading">
    <div class="ssc">
      <slot name="skeleton"></slot>
    </div>
  </template>
  <template v-else>
    <slot name="content"></slot>
  </template>
</template>

<script>
export default {
  name: 'SkeletonPatternLoader',
  props: {
    loading: {
      type: Boolean,
      required: true,
    },
  }
}
</script>
