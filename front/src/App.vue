<template>
  <div>
    <swit-menu
      button-selector=".side-menu-toggler"
      content-selector=".commuse-content"
      :close-on-click="false"
    >
      <ul>
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

      <div class="side-menu-admin" v-if="$store.state.user.currentUser.admin">
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
        <a class="side-menu-toggler hvr-grow">
          <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><defs></defs><title/><g data-name="70-Menu" id="_70-Menu"><rect class="cls-1" height="6" width="6" x="1" y="1"/><rect class="cls-1" height="6" width="6" x="25" y="1"/><rect class="cls-1" height="6" width="6" x="13" y="1"/><rect class="cls-1" height="6" width="6" x="1" y="13"/><rect class="cls-1" height="6" width="6" x="1" y="25"/><rect class="cls-1" height="6" width="6" x="25" y="25"/><rect class="cls-1" height="6" width="6" x="25" y="13"/><rect class="cls-1" height="6" width="6" x="13" y="13"/><rect class="cls-1" height="6" width="6" x="13" y="25"/></g></svg>
        </a>

        <router-link :to="'/'" class="top-nav-name-link">
          <div class="top-nav-name-link-logo">
            <img :src="logo">
          </div>
          <h3 class="is-size-4">{{ appTitle }}</h3>
        </router-link>
      </div>

      <div class="top-nav-end">
        <div class="top-nav-user-menu">
          <VDropdown>
            <div class="top-nav-user-menu-toggler no-select">
              <svg enable-background="new 0 0 48 48" height="48px" id="Layer_1" version="1.1" viewBox="0 0 48 48" width="48px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path clip-rule="evenodd" d="M24,45C12.402,45,3,35.598,3,24S12.402,3,24,3s21,9.402,21,21S35.598,45,24,45z   M35.633,39c-0.157-0.231-0.355-0.518-0.514-0.742c-0.277-0.394-0.554-0.788-0.802-1.178C34.305,37.062,32.935,35.224,28,35  c-1.717,0-2.965-1.288-2.968-3.066L25,31c0-0.135-0.016,0.148,0,0v-1l1-1c0.731-0.339,1.66-0.909,2.395-1.464l0.135-0.093  C29.111,27.074,29.923,26.297,30,26l0.036-0.381C30.409,23.696,31,20.198,31,19c0-4.71-2.29-7-7-7c-4.775,0-7,2.224-7,7  c0,1.23,0.591,4.711,0.963,6.616l0.035,0.352c0.063,0.313,0.799,1.054,1.449,1.462l0.098,0.062C20.333,28.043,21.275,28.657,22,29  l1,1v1c0.014,0.138,0-0.146,0,0l-0.033,0.934c0,1.775-1.246,3.064-2.883,3.064c-0.001,0-0.002,0-0.003,0  c-4.956,0.201-6.393,2.077-6.395,2.077c-0.252,0.396-0.528,0.789-0.807,1.184c-0.157,0.224-0.355,0.51-0.513,0.741  c3.217,2.498,7.245,4,11.633,4S32.416,41.498,35.633,39z M24,5C13.507,5,5,13.507,5,24c0,5.386,2.25,10.237,5.85,13.694  C11.232,37.129,11.64,36.565,12,36c0,0,1.67-2.743,8-3c0.645,0,0.967-0.422,0.967-1.066h0.001C20.967,31.413,20.967,31,20.967,31  c0-0.13-0.021-0.247-0.027-0.373c-0.724-0.342-1.564-0.814-2.539-1.494c0,0-2.4-1.476-2.4-3.133c0,0-1-5.116-1-7  c0-4.644,1.986-9,9-9c6.92,0,9,4.356,9,9c0,1.838-1,7-1,7c0,1.611-2.4,3.133-2.4,3.133c-0.955,0.721-1.801,1.202-2.543,1.546  c-0.005,0.109-0.023,0.209-0.023,0.321c0,0-0.001,0.413-0.001,0.934h0.001C27.033,32.578,27.355,33,28,33c6.424,0.288,8,3,8,3  c0.36,0.565,0.767,1.129,1.149,1.694C40.749,34.237,43,29.386,43,24C43,13.507,34.493,5,24,5z" fill-rule="evenodd"/></svg>
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
  import SwitMenu from '@/components/Shared/SwitMenu.vue'
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
  import logo from '@/assets/images/logo.png'
  import { isMobile } from '@/lib/mobile_utils.js'
  import Spinner from '@/components/Shared/Spinner.vue'

  const apiUrl = import.meta.env.VITE_API_URL

  export default {
    name: 'CommUse',
    components: {
      SwitMenu,
      Spinner,
    },
    created() {
      this.loadCurrentUser()
      this.loadSystemSettings()
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
        const systemSettings = await this.$store.dispatch('app/fetchPublicSystemSettings')
        this.$store.dispatch('app/setPublicSystemSettings', systemSettings)
      },
      logout() {
        window.location.href = `${this.apiUrl}/logout`
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
