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
      path: '/profile',
      component: () => import('@/components/User/Profile.vue'),
      name: 'user-profile.index',
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
      path: '/admin/users',
      component: () => import('@/components/Admin/Users/Index.vue'),
      name: 'admin-users.index',
      meta: {
        title: 'Users admin',
      },
    },
    {
      path: '/admin/invitations',
      component: () => import('@/components/Admin/Invitations/Index.vue'),
      name: 'admin-invitations.index',
      meta: {
        title: 'Invitations admin',
      },
    },
    {
      path: '/admin/custom_fields',
      component: () => import('@/components/Admin/CustomFields/Index.vue'),
      name: 'admin-custom-fields.index',
      meta: {
        title: 'Custom fields admin',
      },
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
