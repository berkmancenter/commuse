import { createRouter, createWebHistory } from 'vue-router'

const basePath = import.meta.env.VITE_BASE_PATH
const appTitle = import.meta.env.VITE_APP_TITLE


const router = createRouter({
  history: createWebHistory(basePath || '/'),
  routes: [
    {
      path: '/',
      component: () => import('@/components/Home/Index.vue'),
      name: 'home.index',
      meta: {
        title: 'Home'
      },
    },
    {
      path: '/people',
      component: () => import('@/components/People/Index.vue'),
      name: 'people.index',
      meta: {
        title: 'People'
      },
    },
    {
      path: '/profile',
      component: () => import('@/components/User/Profile.vue'),
      name: 'user-profile.index',
      meta: {
        title: 'Profile'
      },
    },
  ]
})

router.afterEach(route => {
  window.scrollTo(0, 0)
})

router.beforeEach((to, from, next) => {
  document.title = `${to.meta.title} | ${appTitle}`
  next()
})

export default router
