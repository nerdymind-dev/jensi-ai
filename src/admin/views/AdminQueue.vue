<template>
  <div>
    <breadcrumbs :links="getCrumbs()" />

    <section v-if="hasLoaded">
      <div class="">
        <div class="sm:flex sm:items-center py-3" style="background-color: #f0f0f1">
          <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold leading-6 text-gray-700">
              Queue
            </h1>
            <p class="mt-2 text-sm text-gray-700">
              Recently queued items
            </p>
          </div>
          <t-button
            variant="secondary"
            class="mr-2"
            :class="{ 'opacity-25': !canReload }"
            :disabled="!canReload"
            @click="reloadData">
            Reload Table
          </t-button>

          <t-button
            variant="secondary"
            :class="{ 'opacity-25': !canReload }"
            :disabled="!canReload"
            @click="() => doProcessQueue()">
            Process Next Job
          </t-button>
        </div>
        <div class="mt-8 flow-root">
          <div class="overflow-x-auto">
            <div class="inline-block min-w-full py-2 align-middle">
              <div class="bg-white overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg relative">

                <div v-if="isLoading" class="absolute transform rounded-md z-10 w-full h-full flex items-center justify-center bg-gray-200 bg-opacity-60">
                  <loader :loading="isLoading" />
                </div>

                <template v-if="queueTable && queueTable.rows.length">

                  <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                        <span class="sr-only">Details</span>
                      </th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Post</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Failed</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Queued At</th>
                      <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Actions</span>
                      </th>
                    </tr>
                    </thead>
                    <tbody v-if="Array.isArray(queueTable.rows) && queueTable.rows.length" class="divide-y divide-gray-200 bg-white">
                    <template v-for="(row, index) in queueTable.rows" :key="`queue_row_${index}`">
                      <tr :class="{'bg-red-50': row.failed === '1'}">
                        <td class="whitespace-normal py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                          <t-button v-if="row.processed === '1' && (row.meta || row.errors)" type="button" class="flex gap-1" variant="link" @click="selectRow(row)">
                            Details
                            <svg class="w-5 h-5" :class="{'rotate-180': isRowSelected(row)}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                          </t-button>
                        </td>
                        <td class="whitespace-normal w-80 px-3 py-4 text-sm font-medium text-gray-900">
                          <a :href="`/wp-admin/post.php?post=${row.post_id}&action=edit`" target="_blank"
                             class="hover:text-blue-500">
                            {{ row.name }}
                          </a>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                          {{ getType(row) }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                          {{ row.processed === '1' ? 'Processed' : 'Queued' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                          <span v-if="row.failed === '1'" class="text-red-500">
                            True
                          </span>
                          <span v-else>
                            False
                          </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                          {{ getFormattedDate(row.created) }}
                        </td>
                        <td class="relative justify-end whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                          <div class="flex items-center gap-2 divide-x">
                            <button v-if="row.processed !== '1'" type="button" class="pl-1 text-blue-600 hover:text-blue-700" @click="() => doProcessQueue(row)">
                              Process Now
                            </button>
                            <button type="button" class="pl-1 text-red-600 hover:text-red-700" @click="() => doDestroyItem(row)">
                              Delete
                            </button>
                          </div>
                        </td>
                      </tr>
                      <tr v-if="isRowSelected(row)" :class="[row.failed === '1' ? 'bg-red-100' : 'bg-gray-100']">
                        <td colspan="7" :class="[index !== queueTable.rows.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-wrap px-3 py-4 text-sm text-gray-500']">
                          <template v-if="row.failed === '1'">
                            <div class="grid md:grid-cols-5 gap-x-3">
                              <p class="text-gray-400 col-span-1">
                                Error:
                              </p>
                              <p class="col-span-4 text-bold">
                                {{ JSON.parse(row.errors)?.message || 'N/A' }}
                              </p>
                            </div>
                            <div class="mt-3 grid md:grid-cols-5 gap-x-3">
                              <p class="text-gray-400 col-span-1">
                                File:
                              </p>
                              <p class="col-span-4 text-bold">
                                {{ JSON.parse(row.errors)?.file || 'N/A' }}
                              </p>
                            </div>
                            <div class="mt-3 grid md:grid-cols-5 gap-x-3">
                              <p class="text-gray-400 col-span-1">
                                Line:
                              </p>
                              <p class="col-span-4 text-bold">
                                {{ JSON.parse(row.errors)?.line || 'N/A' }}
                              </p>
                            </div>
                          </template>
                          <template v-else>
                            <div class="grid md:grid-cols-5 gap-x-3">
                              <p class="text-gray-400 col-span-1">
                                Original Content:
                              </p>
                              <div class="col-span-4" v-html="row.content" />
                            </div>
                            <div class="mt-3 grid md:grid-cols-5 items-center gap-x-3">
                              <p class="text-gray-400 col-span-1">
                                Generated Content:
                              </p>
                              <div class="col-span-4" v-html="JSON.parse(row.meta)?.generated || 'N/A'" />
                            </div>
                            <div class="mt-3 grid md:grid-cols-5 items-center gap-x-3">
                              <p class="text-gray-400 col-span-1">
                                Tokens used:
                              </p>
                              <p class="col-span-4 text-bold">
                                {{ JSON.parse(row.meta)?.tokens || 'N/A' }}
                              </p>
                            </div>
                          </template>
                        </td>
                      </tr>
                    </template>
                    </tbody>
                  </table>

                  <div class="border-t bg-gray-50 border-gray-200 p-3 justify-center flex sm:flex-1 sm:items-center sm:justify-between">
                    <div class="hidden sm:block">
                      <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ queueTable.from }}</span>
                        to
                        <span class="font-medium">{{ queueTable.to }}</span>
                        of
                        <span class="font-medium">{{ queueTable.total }}</span>
                        results
                      </p>
                    </div>
                    <div>

                      <nav
                        class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                        aria-label="Pagination"
                      >
                        <button
                          class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                          type="button"
                          :class="{ 'opacity-25': !queueTable.prev_page_url }"
                          :disabled="!queueTable.prev_page_url"
                          @click="getTableData(queueTable.prev_page_url)"
                        >
                          <span class="sr-only">Previous</span>
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                          >
                            <path
                              fill-rule="evenodd"
                              d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                              clip-rule="evenodd"
                            />
                          </svg>
                        </button>

                        <div v-for="(link, key) in queueTable.links" :key="key">
                          <slot name="link">
                            <button
                              v-if="!isNaN(link.label) || link.label === '...'"
                              class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                              :class="{'opacity-25': !link.url, 'hover:bg-gray-50': link.url, 'bg-gray-100': link.active}"
                              type="button"
                              :disabled="!link.url"
                              @click="getTableData(link.url)"
                            >{{ link.label }}</button>
                          </slot>
                        </div>

                        <button
                          class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                          type="button"
                          :class="{ 'opacity-25': !queueTable.next_page_url }"
                          :disabled="!queueTable.next_page_url"
                          @click="getTableData(queueTable.next_page_url)"
                        >
                          <span class="sr-only">Next</span>
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                          >
                            <path
                              fill-rule="evenodd"
                              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                              clip-rule="evenodd"
                            />
                          </svg>
                        </button>
                      </nav>
                    </div>
                  </div>
                </template>
                <template v-else>
                  <!--Card-->
                  <div class="p-8 mt-6 lg:mt-0 rounded shadow bg-white">
                    <div class="flex flex-col items-center justify-center">
                      <p class="text-lg mb-3">
                        No items currently queued. Once AI generation has been queued for an item you can view it here.
                      </p>
                    </div>
                  </div>
                  <!--/Card-->
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import {computed, inject, nextTick, onBeforeMount, ref} from 'vue'
import moment from 'moment'

import Breadcrumbs from '~src/shared/components/BreadcrumbNavigation.vue'
import Loader from '~src/shared/components/LoadingIndicator.vue'
import {TButton} from '@variantjs/vue'

const win = inject('win')
const swal = inject('swal')
const axios = inject('axios')

const hasLoaded = ref(false)
const isLoading = ref(false)
const queueTable = ref({ rows: [] })
const selectedRows = ref({})

const endpoints = ref({
  queue: {
    table: '',
    job: '',
    process: ''
  }
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

const selectRow = (row) => {
  selectedRows.value[row.id] = !selectedRows.value[row.id]
}

const isRowSelected = (row) => {
  return selectedRows.value[row.id] || false
}

const getType = (item) => {
  const selectedType = (config.contentTypes || []).find(ct => ct.id == item.type)
  if (selectedType) {
    return selectedType.title
  }
  return 'N/A'
}

const getPersona = (item) => {
  const selectedPersona = config.personas.find(p => p.id == item.persona_id)
  if (selectedPersona) {
    return selectedPersona.title
  }
  return '(none selected)'
}

const canReload = computed(() => {
  return !isLoading.value && queueTable.value.hasOwnProperty('first_page_url')
})

const reloadData = async () => {
  if (queueTable.value?.first_page_url) {
    await getTableData(queueTable.value.first_page_url)
  }
  return true
}

const getTableData = async (link) => {
  isLoading.value = true
  try {
    if (config.settings.enable_debug_messages) {
      console.log(':: DEBUG ::', link)
    }
    const rst = await axios.get(link)
    if (config.settings.enable_debug_messages) {
      console.log(':: DEBUG - Response ::', rst)
    }
    if (rst.status === 200) {
      // Get the data object from the response
      const { data = null } = rst?.data
      if (data) {
        // Update the shared config
        config.queueTable = data.queueTable

        // Update the uploaded files list
        queueTable.value = data.queueTable
      }
    } else {
      swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Wordpress responded with an error',
        footer: '<div class="overflow-footer w-full">' + JSON.stringify(rst, null, 2) + '</div>'
      })
    }
  } catch (err) {
    if (config.settings.enable_debug_messages) {
      console.log(':: DEBUG - Error ::', err)
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

const doProcessQueue = async (item = null) => {
  swal.fire({
    icon: 'warning',
    title: 'Are you sure?',
    text: item
      ? 'This item will be processed automatically by the WordPress CRON manager. However, if you do not wish to wait, you can process this item now.'
      : 'The next item will be processed automatically by the WordPress CRON manager. However, if you do not wish to wait, you can process the next item now.',
    showCancelButton: true,
    confirmButtonText: 'Process Now',
  }).then(async (result) => {
    if (result.isConfirmed) {
      isLoading.value = true
      try {
        if (config.settings.enable_debug_messages) {
          console.log(':: DEBUG :: processing job', item)
        }
        const rst = await axios.post(endpoints.value.queue.process, { id: item ? item.id : null })
        if (config.settings.enable_debug_messages) {
          console.log(':: DEBUG - Response ::', rst)
        }
        const { success = null } = rst?.data
        if (success === true && rst.status === 200) {
          swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Job processing started successfully',
            showConfirmButton: false,
            timer: 1500
          })
          await getTableData(queueTable.value.first_page_url)
        } else {
          swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'Unable to process job, please try again later',
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

const doDestroyItem = (item) => {
  swal.fire({
    icon: 'warning',
    title: 'Are you sure?',
    text: 'Delete job now? This action cannot be undone.',
    showCancelButton: true,
    confirmButtonText: 'Delete',
  }).then(async (result) => {
    if (result.isConfirmed) {
      isLoading.value = true
      try {
        if (config.settings.enable_debug_messages) {
          console.log(':: DEBUG :: deleting job', item)
        }
        const params = { id: item.id, }
        const rst = await axios.delete(endpoints.value.queue.job, { data: { ...params } })
        if (config.settings.enable_debug_messages) {
          console.log(':: DEBUG - Response ::', rst)
        }
        const { success = null } = rst?.data
        if (success === true && rst.status === 200) {
          swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Import deleted successfully',
            showConfirmButton: false,
            timer: 1500
          })
          await getTableData(queueTable.value.first_page_url)
        } else {
          swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'Unable to delete job, please try again later',
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

const getFormattedDate = (date) => moment(date).local().format('lll')

const doLoad = async () => {
  await nextTick()

  if (!win.$appConfig.nonce) {
    win.$appConfig.nonce = config.rest.nonce
  }

  queueTable.value = config.queueTable
  endpoints.value = config.rest.endpoints

  // make sure data is loaded before ui render
  hasLoaded.value = true
}

const getCrumbs = () => {
  return [
    { title: 'Queue', href: null }
  ]
}
</script>

