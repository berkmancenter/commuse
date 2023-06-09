<template>
  <div class="switmenu-menu" :class="{ 'switmenu-menu-active': open }">
    <a class="switmenu-menu-closer" @click="toggleMenu">
      x
    </a>
    <slot></slot>
  </div>
</template>

<script>
  export default {
    name: 'switmenu-index',
    props: ['buttonId', 'menuId'],
    data () {
      return {
        button: false,
        open: false
      }
    },
    mounted () {
      this.button = document.querySelector('#' + this.buttonId)
      if (!this.button) {
        return
      }

      this.button.onclick = this.toggleMenu
      document.querySelector('.switmenu-menu a').onclick = this.closeMenu
    },
    methods: {
      toggleMenu () {
        this.open = !this.open
      },
      closeMenu () {
        this.open = false
      },
    }
  }
</script>

<style lang="scss">
  @import '@/assets/scss/swit_menu.scss'
</style>
