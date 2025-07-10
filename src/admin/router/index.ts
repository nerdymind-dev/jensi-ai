import { createRouter, createWebHashHistory, RouteRecordRaw } from 'vue-router'
import AdminDashboard from '~src/admin/views/AdminDashboard.vue'
import AdminSettings from '~src/admin/views/AdminSettings.vue'
import AdminInfo from '~src/admin/views/AdminInfo.vue'
import AdminQueue from '~src/admin/views/AdminQueue.vue'

const routes: RouteRecordRaw[] = [
  {
    name: 'home',
    path: '/',
    component: AdminDashboard
  },
  {
    name: 'settings',
    path: '/settings',
    component: AdminSettings
  },
  {
    name: 'queue',
    path: '/queue',
    component: AdminQueue
  },
  {
    name: 'help',
    path: '/help',
    component: AdminInfo
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  scrollBehavior(to, from, savedPosition) {
    // always scroll to top
    return { top: 0 }
  },
  routes
})

const cancelRequestsFor = [
  'edit-import'
]
router.beforeEach((to, from, next) => {
  // Cancel any pending axios requests...
  // const routeName = <string>from.name || ''
  // if (cancelRequestsFor.includes(routeName)) {
  //   window.$appConfig?.cancelSource?.cancel('User canceled via navigate')
  // }
  next();
})

export default router
