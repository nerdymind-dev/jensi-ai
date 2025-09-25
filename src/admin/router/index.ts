import { createRouter, createWebHashHistory, RouteRecordRaw } from 'vue-router'
import AdminDashboard from '~src/admin/views/AdminDashboard.vue'
import AdminSettings from '~src/admin/views/AdminSettings.vue'
import AdminInfo from '~src/admin/views/AdminInfo.vue'
import AdminQueue from '~src/admin/views/AdminQueue.vue'
import AdminSync from '~src/admin/views/AdminSync.vue'
import AdminWidgets from '~src/admin/views/AdminWidgets.vue'

const routes: RouteRecordRaw[] = [
  {
    name: 'home',
    path: '/',
    component: AdminDashboard
  },
  {
    name: 'widgets',
    path: '/widgets',
    component: AdminWidgets
  },
  {
    name: 'settings',
    path: '/settings',
    component: AdminSettings
  },
  {
    name: 'sync',
    path: '/sync',
    component: AdminSync
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
  //   window.$jensiAiConfig?.cancelSource?.cancel('User canceled via navigate')
  // }
  next();
})

export default router
