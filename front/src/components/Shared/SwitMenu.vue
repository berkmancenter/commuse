<template>
  <div class="switmenu-menu" :class="{ 'switmenu-menu-active': open }">
    <slot></slot>
  </div>
</template>

<script>
  export default {
    name: 'SwitMenuIndex',
    props: {
      buttonSelector: {
        type: String,
        required: true,
      },
      contentSelector: {
        type: String,
        required: true,
      },
    },
    data () {
      return {
        button: null,
        body: null,
        open: false,
      }
    },
    mounted () {
      this.button = document.querySelector('' + this.buttonSelector)
      this.content = document.querySelector('' + this.contentSelector)
      this.body = document.querySelector('body')

      if (!this.button || !this.contentSelector) {
        return
      }

      this.content.classList.add('switmenu-content')

      this.button.onclick = this.toggleMenu
      document.querySelector('.switmenu-menu a').onclick = this.closeMenu
    },
    methods: {
      toggleMenu() {
        this.open = !this.open
        this.toggleBodyClass(this.open)
      },
      closeMenu() {
        this.open = false
        this.toggleBodyClass(false)
      },
      toggleBodyClass(status) {
        if (status) {
          this.body.classList.add('switmenu-body-open')
        } else {
          this.body.classList.remove('switmenu-body-open')
        }
      },
    },
  }
</script>

<style lang="scss">
  @import '@/assets/scss/swit_menu.scss'
</style>
