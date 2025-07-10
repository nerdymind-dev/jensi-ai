import axios, { AxiosInstance, AxiosResponse } from 'axios'
import {
  ProgressFinisher,
  useProgress
} from '@marcoschulte/vue3-progress'

const progresses = [] as ProgressFinisher[]

// @ts-ignore
const Axios: AxiosInstance = axios.create({
  baseURL: '/',
  withCredentials: false,
  headers: {
    'Accept': 'application/json; charset=utf-8',
    'Content-Type': 'application/json; charset=utf-8',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

/* Allows Us To Authorized Api Request If Authenticated Using Web Middleware */
Axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const CancelSource = axios.CancelToken.source();
Axios.interceptors.request.use((config: any) => {
  progresses.push(useProgress().start())

  // set nonce
  config.headers['X-WP-Nonce'] = window.$appConfig.nonce

  // update cancel token
  config.cancelToken = CancelSource.token;

  return config
})

Axios.interceptors.response.use((response: AxiosResponse) => {
  progresses.pop()?.finish()

  // replace old nonce
  if (response.headers['X-WP-Nonce']) {
    window.$appConfig.nonce = response.headers['X-WP-Nonce']
  }

  return response
}, function (error) {
  progresses.pop()?.finish();
  return Promise.reject(error);
})

export {
  Axios,
  CancelSource
}
