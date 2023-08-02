<template>
  <div class="switmenu-menu" :class="{ 'switmenu-menu-active': $store.state.app.sideMenuStatus }">
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
      closeOnClick: {
        type: Boolean,
        required: false,
        default: true,
      },
    },
    data () {
      return {
        button: null,
        body: null,
      }
    },
    mounted () {
      const that = this

      this.mitt.on('closeSideMenu', () => that.closeMenu())

      this.button = document.querySelector('' + this.buttonSelector)
      this.content = document.querySelector('' + this.contentSelector)
      this.body = document.querySelector('body')

      this.toggleBodyClass()

      if (!this.button || !this.contentSelector) {
        return
      }

      this.content.classList.add('switmenu-content')

      this.button.onclick = this.toggleMenu

      document.querySelectorAll('.switmenu-menu a').forEach(function(element) {
        if (that.closeOnClick) {
          element.onclick = function() {
            that.closeMenu()
          }
        }
      })
    },
    methods: {
      toggleMenu() {
        this.$store.dispatch('app/setSideMenuStatus', !this.$store.state.app.sideMenuStatus)
        this.toggleBodyClass()
      },
      closeMenu() {
        this.$store.dispatch('app/setSideMenuStatus', false)
        this.toggleBodyClass()
      },
      toggleBodyClass() {
        if (this.$store.state.app.sideMenuStatus) {
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
