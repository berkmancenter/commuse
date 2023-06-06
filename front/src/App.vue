<template>
  <div>
    <sidebar-menu
      :menu="menu"
      v-model:collapsed="menuCollapsed"
    ></sidebar-menu>
    <div class="commuse-content" :class="[{ menuCollapsed: menuCollapsed }]">
      <router-view v-cloak></router-view>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CommUse',
  data() {
    return {
      menuCollapsed: false,
      menu: [
        {
          href: '/',
          title: 'Home',
        },
        {
          href: '/people',
          title: 'People',
        },
      ],
    }
  },
}
</script>

<style lang="scss">
  /*  latin  */
  @font-face {
    font-family: 'Roboto Slab';
    font-style: normal;
    font-weight: 100;
    font-display: swap;
    src: url('@/fonts/robotoslab-latin-normal-100.woff2') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
  }

  /*  latin  */
  @font-face {
    font-family: 'Roboto Slab';
    font-style: normal;
    font-weight: 200;
    font-display: swap;
    src: url('@/fonts/robotoslab-latin-normal-200.woff2') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
  }

  /*  latin  */
  @font-face {
    font-family: 'Roboto Slab';
    font-style: normal;
    font-weight: 300;
    font-display: swap;
    src: url('@/fonts/robotoslab-latin-normal-300.woff2') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
  }

  /*  latin  */
  @font-face {
    font-family: 'Roboto Slab';
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    src: url('@/fonts/robotoslab-latin-normal-400.woff2') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
  }

  /*  latin  */
  @font-face {
    font-family: 'Roboto Slab';
    font-style: normal;
    font-weight: 500;
    font-display: swap;
    src: url('@/fonts/robotoslab-latin-normal-500.woff2') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
  }

  /*  latin  */
  @font-face {
    font-family: 'Roboto Slab';
    font-style: normal;
    font-weight: 600;
    font-display: swap;
    src: url('@/fonts/robotoslab-latin-normal-600.woff2') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
  }

  /*  latin  */
  @font-face {
    font-family: 'Roboto Slab';
    font-style: normal;
    font-weight: 700;
    font-display: swap;
    src: url('@/fonts/robotoslab-latin-normal-700.woff2') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
  }

  /*  latin  */
  @font-face {
    font-family: 'Roboto Slab';
    font-style: normal;
    font-weight: 800;
    font-display: swap;
    src: url('@/fonts/robotoslab-latin-normal-800.woff2') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
  }

  /*  latin  */
  @font-face {
    font-family: 'Roboto Slab';
    font-style: normal;
    font-weight: 900;
    font-display: swap;
    src: url('@/fonts/robotoslab-latin-normal-900.woff2') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
  }

  body, button, input, optgroup, select, textarea {
    font-family: 'Roboto Slab', serif !important;
  }

  .commuse-content {
    padding-left: 290px;
    transition: 0.3s ease;

    &.menuCollapsed {
      padding-left: 65px;
    }
  }
</style>
