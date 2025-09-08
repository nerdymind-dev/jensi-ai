<template>
  <div>
    <breadcrumbs :links="getCrumbs()" />

    <section>
      <div class="">
        <div class="sm:flex sm:items-center py-3">
          <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold leading-6 text-gray-700">
              Sync
            </h1>
          </div>

          <t-button
            variant="secondary"
            class="mr-2"
            :class="{ 'opacity-25 cursor-not-allowed': !hasLoaded && isLoading }"
            :disabled="!hasLoaded || isLoading"
            @click="() => doSync()">
            Sync All
          </t-button>
        </div>
        <div class="mt-8 flow-root">
          <div class="">
            <div class="inline-block min-w-full py-2 align-middle">
              <div class="">
                <div class="pb-4">
                  <div class="my-4 border-l-4 border-blue-400 bg-blue-50 p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <InformationCircleIcon class="h-5 w-5 text-blue-400" aria-hidden="true" />
                      </div>
                      <div class="ml-3">
                        <p class="text-sm text-blue-700">
                          Use the buttons below to manually sync content with the JENSi AI service. You can sync all content that is currently configured for syncing, or you can sync individual configurations.
                        </p>
                      </div>
                    </div>
                  </div>
                  <template v-if="currentConfigs.length">
                    <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                      <li v-for="(conf, idx) in currentConfigs" :key="`person.${idx}`"
                          class="col-span-1 flex flex-col justify-between divide-y divide-gray-200 rounded-lg bg-white shadow">
                        <div class="flex w-full items-center justify-between space-x-6 p-6">
                          <div class="flex-1">
                            <div class="flex items-center justify-between space-x-2">
                              <div class="flex items-center space-x-1">
                                <h3 class="truncate text-sm font-medium text-gray-900">
                                  {{ conf.title }}
                                </h3>
                                <span v-if="conf.enabled === '1'">
                                  <CheckCircleIcon class="h-5 w-5 text-green-400" aria-hidden="true" />
                                </span>
                                <span v-else>
                                  <MinusCircleIcon class="h-5 w-5 text-red-300" aria-hidden="true" />
                                </span>
                              </div>
                              <div>
                                <div class="ml-auto inline-flex items-center rounded-full bg-gray-200 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-gray-500/10 ring-inset">
                                  {{ conf.post_type }}
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div>
                          <div class="-mt-px flex divide-x divide-gray-200">
                            <div class="flex w-0 flex-1">
                              <button 
                                :disabled="conf.enabled !== '1' || isLoading"
                                @click="(e) => doSync(conf)" type="button"
                                :class="{ 'opacity-25 cursor-not-allowed': conf.enabled !== '1' || isLoading }"
                                class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-bl-lg border border-transparent py-4 text-sm font-semibold text-gray-900">
                                Sync Now
                              </button>
                            </div>
                          </div>
                        </div>
                      </li>
                    </ul>

                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import {ref, inject, onBeforeMount, nextTick} from 'vue'
import Breadcrumbs from '~src/shared/components/BreadcrumbNavigation.vue'
import {TButton} from '@variantjs/vue'

import {
  InformationCircleIcon,
  CheckCircleIcon,
  MinusCircleIcon,
} from '@heroicons/vue/24/outline'

const win = inject('win')
const swal = inject('swal')
const axios = inject('axios')

const hasLoaded = ref(false)
const isLoading = ref(false)
const currentConfigs = ref([])

const endpoints = ref({
  sync: '',
})

const config = inject('pluginConfig')

onBeforeMount(() => {
  if (document.readyState === 'complete') {
    doLoad()
  } else {
    document.onreadystatechange = () => {
      if (document.readyState === 'complete') {
        doLoad()
      }
    }
  }
})

const doSync = async (cf = null) => {
  const title = cf ? `Sync Configuration: ${cf.title}` : 'Sync All Configurations'
  swal.fire({
    icon: 'warning',
    title,
    text: cf 
      ? 'This will process all valid pages, posts and media that match the select configuration. This action cannot be undone.' 
      : 'This will process all valid pages, posts and media that are currently configured for syncing with the JENSi AI service. This action cannot be undone.',
    showCancelButton: true,
    confirmButtonText: 'Sync Now',
  }).then(async (result) => {
    if (result.isConfirmed) {
      isLoading.value = true
      try {
        if (config.settings.enable_debug_messages) {
          console.log(':: DEBUG :: processing sync', cf ? cf : 'all')
        }
        const rst = await axios.post(endpoints.value.sync, { id: cf ? cf.id : null })
        if (config.settings.enable_debug_messages) {
          console.log(':: DEBUG - Response ::', rst)
        }
        const { success = null } = rst?.data
        if (success === true && rst.status === 200) {
          swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Sync job successfully queued',
            showConfirmButton: false,
            timer: 1500
          })
        } else {
          swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'Unable to process sync job, please try again later',
            showConfirmButton: false,
            timer: 1500
          })
        }
      } catch (err) {
        if (config.settings.enable_debug_messages) {
          console.error(':: DEBUG - Error ::', err)
        }
        if (err?.code !== 'ERR_CANCELED') {
          const text = err?.response?.data?.error || 'Server responded with an error'
          swal.fire({
            icon: 'error',
            title: 'Error',
            text,
            footer: '<div class="overflow-footer w-full">' + err.message + '</div>'
          })
        }
      } finally {
        isLoading.value = false
      }
    }
  })
}

const doLoad = async () => {
  await nextTick()

  if (!win.$appConfig.nonce) {
    win.$appConfig.nonce = config.rest.nonce
  }

  endpoints.value = config.rest.endpoints
  currentConfigs.value = config.configs || []

  // make sure data is loaded before ui render
  hasLoaded.value = true
}

const getCrumbs = () => {
  return [
    { title: 'Sync', href: null }
  ]
}
</script>

