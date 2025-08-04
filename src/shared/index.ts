import type {App} from 'vue'
import {Axios, CancelSource} from './axios'
import debounce from 'lodash/debounce'
import installI18n from './i18n'
import VueAxios from 'vue-axios'
import swal from 'sweetalert2'

export default (app: App, configName: string) => {
  installI18n(app)
  const win: any = window

  win.$appConfig = {}
  win.$appConfig.axios = Axios
  win.$appConfig.cancelSource = CancelSource
  win.$appConfig.debounce = debounce
  win.$appConfig.swal = swal.mixin({
    showClass: {
      // backdrop: 'swal2-noanimation', // disable backdrop animation
      popup: '', // disable popup animation
      // icon: '' // disable icon animation
    },
    hideClass: {
      popup: '', // disable popup fade-out animation
    },
    background: '#fff',
    customClass: {
      popup: 'bg-white',
      htmlContainer: 'bg-white text-center',
      title: 'bg-white text-lg font-bold text-gray-700',
      denyButton: 'mx-2 inline-flex items-center justify-center px-6 py-2 bg-red-600 border border-transparent rounded-md text-white tracking-widest hover:bg-red-500 focus:outline-none focus:border-red-600 focus:shadow-outline-red active:bg-red-600 disabled:opacity-25 transition ease-in-out duration-150',
      confirmButton: 'mx-2 inline-flex items-center px-6 py-2 bg-blue-500 border border-transparent rounded-md text-white tracking-widest hover:bg-blue-600 active:bg-blue-600 focus:outline-none focus:border-blue-600 focus:shadow-outline-blue disabled:opacity-25 transition ease-in-out duration-150',
      cancelButton: 'mx-2 inline-flex items-center px-6 py-2 bg-gray-100 border border-transparent rounded-md text-gray-700 tracking-widest hover:bg-gray-200 active:bg-gray-200 focus:outline-none focus:border-gray-200 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150',
      actions: 'z-0',
      footer: 'h-10 text-gray-400'
    },
    reverseButtons: true,
    buttonsStyling: false
  })

  // Allow for using this.$win/axios inside a component
  app.config.globalProperties.$win = win;
  app.config.globalProperties.axios = win.$appConfig.axios
  app.config.globalProperties.cancelSource = win.$appConfig.cancelSource
  app.config.globalProperties.$swal = win.$appConfig.swal

  app.use(VueAxios, win.$appConfig.axios)

  app.provide('win', app.config.globalProperties.$win)
  app.provide('axios', app.config.globalProperties.axios)
  app.provide('cancelSource', app.config.globalProperties.cancelSource)
  app.provide('swal', app.config.globalProperties.$swal)
  app.provide('pluginConfig', win[configName])
}
