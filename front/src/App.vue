<template>
  <div>
    <swit-menu
      button-selector=".side-menu-toggler"
      content-selector=".commuse-content"
      :close-on-click="false"
    >
      <ul v-if="menuActive">
        <li v-for="(link) in menu">
          <a :href="link.href" class="hvr-fade" @click="hideMenuMobile" v-if="link.external === true">
            <img class="side-menu-icon" :src="link.icon">
            {{ link.title }}
          </a>
          <router-link :to="link.href" class="hvr-fade" @click="hideMenuMobile" v-if="link.external !== true">
            <img class="side-menu-icon" :src="link.icon">
            {{ link.title }}
          </router-link>
        </li>
      </ul>

      <div class="side-menu-admin" v-if="$store.state.user.currentUser.admin && adminMenuActive">
        <div class="is-size-5 px-2 py-1">Admin</div>

        <ul>
          <li v-for="(link) in adminMenu">
            <a :href="link.href" class="hvr-fade" @click="hideMenuMobile" v-if="link.external === true">
              <img class="side-menu-icon" :src="link.icon">
              {{ link.title }}
            </a>
            <router-link :to="link.href" class="hvr-fade" @click="hideMenuMobile" v-if="link.external !== true">
              <img class="side-menu-icon" :src="link.icon">
              {{ link.title }}
            </router-link>
          </li>
        </ul>
      </div>
    </swit-menu>

    <nav class="top-nav">
      <div class="top-nav-brand no-select">
        <a class="side-menu-toggler top-nav-button">
          <Icon :src="menuIcon" />
        </a>

        <router-link :to="'/'" class="top-nav-brand-name-link">
          <div class="top-nav-brand-name-link-logo">
            <img :src="logo">
          </div>
          <h3 class="is-size-4">{{ appTitle }}</h3>
        </router-link>
      </div>

      <div class="top-nav-end">
        <div class="top-nav-help top-nav-button">
          <a href="https://berkman-klein-center.gitbook.io/commuse" target="_blank">
            <Icon :src="helpIcon" />
          </a>
        </div>

        <div class="top-nav-user-menu">
          <VDropdown>
            <div class="top-nav-user-menu-toggler top-nav-button no-select">
              <Icon :src="userIcon" />
            </div>

            <template #popper>
              <div class="dropdown-item">
                You are logged in as
                <div>{{ $store.state.user.currentUser.email }}</div>
              </div>
              <hr class="dropdown-divider">
              <router-link class="dropdown-item" :to="'/profile'" v-close-popper>
                Edit profile
              </router-link>
              <router-link class="dropdown-item" :to="'/account'" v-close-popper>
                Account settings
              </router-link>
              <a class="dropdown-item" @click="logout" v-close-popper>
                Logout
              </a>
            </template>
          </VDropdown>
        </div>
      </div>
    </nav>

    <div class="commuse-content">
      <div class="container is-fluid mt-4">
        <router-view v-cloak></router-view>
      </div>
    </div>
  </div>

  <Spinner />
</template>

<script>
  // Icons
  import newsMenuIcon from '@/assets/images/news.svg'
  import peopleMenuIcon from '@/assets/images/people_menu.svg'
  import profileMenuIcon from '@/assets/images/profile_menu.svg'
  import usersMenuIcon from '@/assets/images/users.svg'
  import invitationsMenuIcon from '@/assets/images/invitations.svg'
  import shieldMenuIcon from '@/assets/images/shield.svg'
  import fieldsMenuIcon from '@/assets/images/fields_menu.svg'
  import worldMenuIcon from '@/assets/images/world.svg'
  import dataMenuIcon from '@/assets/images/data.svg'
  import magnifierMenuIcon from '@/assets/images/magnifier_menu.svg'
  import settingsMenuIcon from '@/assets/images/settings_menu.svg'
  import buzzMenuIcon from '@/assets/images/buzz_menu.svg'
  import helpIcon from '@/assets/images/help.svg'
  import menuIcon from '@/assets/images/menu.svg'
  import userIcon from '@/assets/images/user_top.svg'

  import SwitMenu from '@/components/Shared/SwitMenu.vue'
  import logo from '@/assets/images/logo.png'
  import { isMobile } from '@/lib/mobile_utils.js'
  import Spinner from '@/components/Shared/Spinner.vue'
  import { waitUntil } from '@/lib/wait_until.js'
  import Icon from '@/components/Shared/Icon.vue'

  const apiUrl = import.meta.env.VITE_API_URL

  export default {
    name: 'CommUse',
    components: {
      SwitMenu,
      Spinner,
      Icon,
    },
    created() {
      this.loadCurrentUser()
      this.loadSystemSettings()
      this.checkEnabledModules()
    },
    data() {
      return {
        appTitle: import.meta.env.VITE_APP_TITLE || 'commuse',
        apiUrl: apiUrl,
        menu: [
          {
            href: '/',
            title: 'News & events',
            icon: newsMenuIcon,
          },
          {
            href: '/buzz',
            title: 'Buzz',
            icon: buzzMenuIcon,
          },
          {
            href: '/people',
            title: 'People',
            icon: peopleMenuIcon,
          },
          {
            href: '/people_map',
            title: 'People map',
            icon: worldMenuIcon,
          },
          {
            href: '/profile',
            title: 'Edit profile',
            icon: profileMenuIcon,
          },
          {
            href: '/account',
            title: 'Account settings',
            icon: shieldMenuIcon,
          },
        ],
        adminMenu: [
          {
            href: '/admin/settings',
            title: 'Settings',
            icon: settingsMenuIcon,
          },
          {
            href: '/admin/invitations',
            title: 'Invitations',
            icon: invitationsMenuIcon,
          },
          {
            href: '/admin/users',
            title: 'Users',
            icon: usersMenuIcon,
          },
          {
            href: '/admin/custom_fields',
            title: 'Custom fields',
            icon: fieldsMenuIcon,
          },
          {
            href: '/admin/data_editor',
            title: 'Data editor',
            icon: dataMenuIcon,
          },
          {
            href: '/admin/profile_data_audit',
            title: 'Profile data audit',
            icon: magnifierMenuIcon,
          },
        ],
        logo,
        menuActive: false,
        adminMenuActive: false,
        helpIcon,
        menuIcon,
        userIcon,
      }
    },
    methods: {
      hideMenuMobile() {
        if (isMobile()) {
          this.mitt.emit('closeSideMenu')
        }
      },
      async loadCurrentUser() {
        const currentUser = await this.$store.dispatch('user/fetchCurrentUser')
        this.$store.dispatch('user/setCurrentUser', currentUser)

        // Redirect to the 404 page if the currentuser is not admin and tries to access an admin page
        if (this.$router.currentRoute._value.meta.admin && this.$store.state.user.currentUser.admin === false) {
          this.$router.push({ name: 'pagenotfound.index' })
        }
      },
      async loadSystemSettings() {
        const systemSettings = await this.$store.dispatch('systemSettings/fetchPublicSystemSettings')
        this.$store.dispatch('systemSettings/setPublicSystemSettings', systemSettings)
      },
      logout() {
        window.location.href = `${this.apiUrl}/logout`
      },
      async checkEnabledModules() {
        await waitUntil(() => {
          return this.$store.state.systemSettings.publicSystemSettings?.SystemEnabledModules
        })

        if (!this.$store.state.systemSettings.publicSystemSettings?.SystemEnabledModules?.value.some(module => module.id === 'buzz')) {
          this.menu = this.menu.filter((item) => item.href !== '/buzz');
        }

        if (!this.$store.state.systemSettings.publicSystemSettings?.SystemEnabledModules?.value.some(module => module.id === 'people_map')) {
          this.menu = this.menu.filter((item) => item.href !== '/people_map');
        }

        this.menuActive = true
        this.adminMenuActive = true
      },
    },
  }
</script>

<style lang="scss">
  @import '@/assets/scss/fonts/fonts.scss';
  @import '@/assets/scss/variables.scss';
  @import '@/assets/scss/global.scss';
  @import '@/assets/scss/layout.scss';
  @import '@/assets/scss/helpers.scss';
  @import '@/assets/scss/aws_custom.scss';
</style>
