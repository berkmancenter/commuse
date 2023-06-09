<template>
  <div>
    <swit-menu
      button-selector=".side-menu-toggler"
      content-selector=".commuse-content"
    >
      <ul>
        <li v-for="(link) in menu">
          <router-link :to="link.href">
            <h3 class="is-size-5">{{ link.title }}</h3>
          </router-link>
        </li>
      </ul>
    </swit-menu>

    <div class="commuse-content">
      <nav class="top-nav">
        <div class="top-nav-brand">
          <a class="side-menu-toggler">
            <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><defs></defs><title/><g data-name="70-Menu" id="_70-Menu"><rect class="cls-1" height="6" width="6" x="1" y="1"/><rect class="cls-1" height="6" width="6" x="25" y="1"/><rect class="cls-1" height="6" width="6" x="13" y="1"/><rect class="cls-1" height="6" width="6" x="1" y="13"/><rect class="cls-1" height="6" width="6" x="1" y="25"/><rect class="cls-1" height="6" width="6" x="25" y="25"/><rect class="cls-1" height="6" width="6" x="25" y="13"/><rect class="cls-1" height="6" width="6" x="13" y="13"/><rect class="cls-1" height="6" width="6" x="13" y="25"/></g></svg>
          </a>

          <router-link :to="'/'" class="top-nav-name-link">
            <h3 class="top-bar-name is-size-4">{{ appTitle }}</h3>
          </router-link>
        </div>

        <div class="top-nav-end">
          <div class="username">peterh@cyber.harvard.edu</div>
        </div>
      </nav>

      <div class="container is-fluid mt-4">
        <router-view v-cloak></router-view>
      </div>
    </div>
  </div>
</template>

<script>
import SwitMenu from '@/components/Shared/SwitMenu.vue'

export default {
  name: 'CommUse',
  components: {
    SwitMenu,
  },
  data() {
    return {
      appTitle: import.meta.env.VITE_APP_TITLE || 'commuse',
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
  @import '@/assets/scss/fonts/fonts.scss';
  @import '@/assets/scss/variables.scss';
  @import '@/assets/scss/pace_theme.scss';
  @import '@/assets/scss/global.scss';
  @import '@/assets/scss/layout.scss';
</style>
