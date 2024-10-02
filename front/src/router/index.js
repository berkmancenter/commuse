import { createRouter, createWebHistory } from 'vue-router'

const basePath = import.meta.env.VITE_BASE_URL
const appTitle = import.meta.env.VITE_APP_TITLE

const router = createRouter({
  history: createWebHistory(basePath || '/'),
  routes: [
    {
      path: '/',
      component: () => import('@/components/Home/Index.vue'),
      name: 'home.index',
      meta: {
        title: 'Home',
      },
    },
    {
      path: '/people',
      component: () => import('@/components/People/Index.vue'),
      name: 'people.index',
      meta: {
        title: 'People',
      },
    },
    {
      path: '/people/:id',
      component: () => import('@/components/People/PersonDetails.vue'),
      name: 'people.details',
    },
    {
      path: '/people_map',
      component: () => import('@/components/People/Map.vue'),
      name: 'people.map',
    },
    {
      path: '/profile',
      component: () => import('@/components/User/Profile.vue'),
      name: 'user-profile.index',
      meta: {
        title: 'Profile',
      },
    },
    {
      path: '/profile/:id',
      component: () => import('@/components/User/Profile.vue'),
      name: 'user-profile-admin.index',
      meta: {
        title: 'Profile',
      },
    },
    {
      path: '/account',
      component: () => import('@/components/User/Account.vue'),
      name: 'user-account.index',
      meta: {
        title: 'Account',
      },
    },
    {
      path: '/buzz',
      component: () => import('@/components/Buzz/Index.vue'),
      name: 'buzz.index',
      meta: {
        title: 'Buzz',
      },
    },
    {
      path: '/admin/users',
      component: () => import('@/components/Admin/Users/Index.vue'),
      name: 'admin-users.index',
      meta: {
        title: 'Users admin',
        admin: true,
      },
    },
    {
      path: '/admin/invitations',
      component: () => import('@/components/Admin/Invitations/Index.vue'),
      name: 'admin-invitations.index',
      meta: {
        title: 'Invitations admin',
        admin: true,
      },
    },
    {
      path: '/admin/custom_fields',
      component: () => import('@/components/Admin/CustomFields/Index.vue'),
      name: 'admin-custom-fields.index',
      meta: {
        title: 'Custom fields admin',
        admin: true,
      },
    },
    {
      path: '/admin/data_editor',
      component: () => import('@/components/Admin/DataEditor/Index.vue'),
      name: 'admin-data-editor.index',
      meta: {
        title: 'Data editor',
        admin: true,
      },
    },
    {
      path: '/admin/profile_data_audit',
      component: () => import('@/components/Admin/ProfileDataAudit/Index.vue'),
      name: 'admin-profile-data-audit.index',
      meta: {
        title: 'Profile data audit',
        admin: true,
      },
    },
    {
      path: '/admin/profile_data_audit/:id',
      component: () => import('@/components/Admin/ProfileDataAudit/Index.vue'),
      name: 'admin-profile-data-audit-single.index',
      meta: {
        title: 'Profile data audit',
        admin: true,
      },
    },
    {
      path: '/admin/settings',
      component: () => import('@/components/Admin/Settings/Index.vue'),
      name: 'admin-settings.index',
      meta: {
        title: 'Settings',
        admin: true,
      },
    },
    {
      path: '/404',
      name: 'pagenotfound.index',
      meta: {
        title: 'Page not found',
      },
      component: () => import('@/components/PageNotFound/Index.vue')
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '404',
    },
  ]
})

router.afterEach(route => {
  window.scrollTo(0, 0)
})

router.beforeEach((to, from, next) => {
  if (to.meta.title) {
    document.title = `${to.meta.title} | ${appTitle}`
  }

  next()
})

export default router
