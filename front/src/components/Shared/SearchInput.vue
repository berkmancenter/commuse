<template>
  <div :class="['commuse-search-input', additionalClasses]">
    <input
      type="text"
      :value="modelValue"
      @input="updateValue"
      placeholder="Search"
      class="input"
    >
    <span>
      <img :src="searchIcon" alt="Search Icon">
    </span>
  </div>
</template>

<script>
  import searchIcon from '@/assets/images/search.svg'

  export default {
    name: 'SearchInput',
    props: {
      additionalClasses: {
        type: [String, Array],
        default: '',
      },
      modelValue: {
        type: String,
        default: '',
      },
    },
    data() {
      return {
        searchIcon,
      };
    },
    emits: ['update:modelValue'],
    methods: {
      updateValue(event) {
        this.$emit('update:modelValue', event.target.value)
      },
    },
  }
</script>

<style >
  .commuse-search-input {
    position: relative;
    max-width: 300px;

    span {
      display: block;
      width: 1.5rem;
      height: 1.5rem;
      position: absolute;
      top: 0;
      right: 0.5rem;
      height: 100%;
      display: flex;
      pointer-events: none;
      user-select: none;
    }

    input {
      border-bottom: 2px solid var(--main-color);
      border-radius: 0;
    }

    input::placeholder {
      color: rgba(54, 54, 54, 0.8);
    }
  }
</style>
