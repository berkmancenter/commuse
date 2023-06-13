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
  ]
})

export default router