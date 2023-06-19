import { createRouter, createWebHistory } from 'vue-router'

const basePath = import.meta.env.VITE_BASE_PATH

const router = createRouter({
  history: createWebHistory(basePath || '/'),
  routes: [
    {
      path: '/',
      component: () => import('@/components/Home/Index.vue'),
      name: 'home.index',
    },
    {
      path: '/people',
      component: () => import('@/components/People/Index.vue'),
      name: 'people.index',
    },
    {
      path: '/profile',
      component: () => import('@/components/User/Profile.vue'),
      name: 'user-profile.index',
    },
  ]
})

router.afterEach(route => {
  window.scrollTo(0, 0)
})

export default router
