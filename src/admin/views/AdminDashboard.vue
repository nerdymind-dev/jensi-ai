<template>
  <div>
    <breadcrumbs :links="getCrumbs()" />

    <section>
      <div class="">
        <div class="sm:flex sm:items-center py-3">
          <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold leading-6 text-gray-700">
              Configuration
            </h1>
          </div>
          <t-button variant="secondary" @click="() => createConfig()">
            Create New Configuration
          </t-button>
        </div>
        <div class="mt-8 flow-root">
          <div class="overflow-x-auto">
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
                          Configurations are used to automate content syncs. Based on the settings, when a new post is created or updated, the sync will be triggered.
                          If multiple configuration overlap, only the most recent <strong>enabled</strong> one will be used to prevent double syncing.
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
                            <div class="my-3">
                              <p>
                                Taxonomy: <strong>{{ getTaxonomy(conf) }}</strong>
                              </p>
                              <p class="text-xs text-gray-500 whitespace-normal">
                                Terms: <strong>{{ getTerms(conf) }}</strong>
                              </p>
                            </div>
                          </div>
                        </div>
                        <div>
                          <div class="-mt-px flex divide-x divide-gray-200">
                            <div class="flex w-0 flex-1">
                              <button @click="(e) => selectAndEditConfig(conf)" type="button" class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-bl-lg border border-transparent py-4 text-sm font-semibold text-gray-900">
                                Edit
                              </button>
                            </div>
                            <div class="-ml-px flex w-0 flex-1">
                              <button @click="(e) => doDestroy(conf.id)" type="button" class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold text-gray-900">
                                Delete
                              </button>
                            </div>
                          </div>
                        </div>
                      </li>
                    </ul>

                  </template>
                  <template v-else>
                    <!--Card-->
                    <div class="p-8 mt-6 lg:mt-0 rounded shadow bg-white">
                      <div class="flex flex-col items-center justify-center">
                        <p class="text-lg mb-3">
                          No configs currently created, create one to get started!
                        </p>
                        <t-button @click="() => createConfig()">
                          Create New Config
                        </t-button>
                      </div>
                    </div>
                    <!--/Card-->
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <modal
        :show="showingEditForm"
        type="info"
        :custom-icon="true"
        @close="closeEditForm">
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
            <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" />
            <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" />
          </svg>
        </template>
        >
        <template #title>
          Edit config
        </template>
        <template v-if="selectedConfig" #content>
          <div class="w-full">
            <div class="flex flex-col gap-3">
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Title
                  <sup class="text-red-400">*</sup>
                </label>
                <t-input class="col-span-3"
                         v-model="configFields.title" />
              </div>
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Enabled
                </label>
                <div>
                  <t-toggle v-model="configFields.enabled" />
                </div>
              </div>
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Post Type
                </label>
                <div class="col-span-3">
                  <span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-gray-500/10 ring-inset">
                    {{ selectedConfig.post_type }}
                  </span>
                </div>
              </div>
              <div class="md:grid grid-cols-5" v-if="selectedConfig.taxonomy">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Taxonomy
                </label>
                <div class="col-span-3">
                  <span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-gray-500/10 ring-inset">
                    {{ selectedConfig.taxonomy }}
                  </span>
                </div>
              </div>
              <div class="md:grid grid-cols-5" v-if="selectedConfig.taxonomy">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Terms
                </label>
                <t-rich-select
                  class="col-span-3"
                  v-model="configFields.terms"
                  :options="config.postTerms[configFields.post_type][configFields.taxonomy] || []"
                  valueAttribute="term_id"
                  textAttribute="name"
                  multiple
                  tags
                />
              </div>
            </div>
          </div>
        </template>
        <template v-if="selectedConfig" #footer>
          <t-button variant="secondary" @click.native="closeEditForm">
            Nevermind
          </t-button>
          <t-button
            class="ml-2" @click.native="(e) => doUpdate(selectedConfig.id)"
            :class="{ 'opacity-25 cursor-not-allowed': !canSubmit || isSubmitting }"
            :disabled="!canSubmit || isSubmitting">
            Update Config
          </t-button>
        </template>
      </modal>

      <modal
        :show="showingCreateForm"
        type="info"
        :custom-icon="true"
        @close="closeCreateForm">
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
            <path d="M17 2.75a.75.75 0 00-1.5 0v5.5a.75.75 0 001.5 0v-5.5zM17 15.75a.75.75 0 00-1.5 0v1.5a.75.75 0 001.5 0v-1.5zM3.75 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5a.75.75 0 01.75-.75zM4.5 2.75a.75.75 0 00-1.5 0v5.5a.75.75 0 001.5 0v-5.5zM10 11a.75.75 0 01.75.75v5.5a.75.75 0 01-1.5 0v-5.5A.75.75 0 0110 11zM10.75 2.75a.75.75 0 00-1.5 0v1.5a.75.75 0 001.5 0v-1.5zM10 6a2 2 0 100 4 2 2 0 000-4zM3.75 10a2 2 0 100 4 2 2 0 000-4zM16.25 10a2 2 0 100 4 2 2 0 000-4z" />
          </svg>
        </template>
        >
        <template #title>
          Create config
        </template>
        <template #content>
          <div class="w-full">
            <div class="flex flex-col gap-3">
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Title
                  <sup class="text-red-400">*</sup>
                </label>
                <t-input class="col-span-3"
                         v-model="configFields.title" />
              </div>
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Enabled
                </label>
                <div>
                  <t-toggle v-model="configFields.enabled" />
                </div>
              </div>
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Post Type
                  <sup class="text-red-400">*</sup>
                </label>
                <t-rich-select
                  class="col-span-3"
                  v-model="configFields.post_type"
                  @input="() => configFields.terms = []"
                  :options="config.postTypes || []"
                />
              </div>
              <div v-if="configFields.post_type" class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Taxonomy
                </label>
                <t-rich-select
                  class="col-span-3"
                  v-model="configFields.taxonomy"
                  :options="config.postTerms[configFields.post_type]['_taxonomies'] || []"
                />
              </div>
              <div v-if="configFields.taxonomy" class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Terms
                </label>
                <t-rich-select
                  class="col-span-3"
                  v-model="configFields.terms"
                  :options="config.postTerms[configFields.post_type][configFields.taxonomy] || []"
                  valueAttribute="term_id"
                  textAttribute="name"
                  multiple
                  tags
                />
              </div>
            </div>
          </div>
        </template>
        <template #footer>
          <t-button variant="secondary" @click.native="closeCreateForm">
            Nevermind
          </t-button>
          <t-button
            class="ml-2" @click.native="(e) => doSave()"
            :class="{ 'opacity-25 cursor-not-allowed': !canSubmit || isSubmitting }"
            :disabled="!canSubmit || isSubmitting">
            Create Config
          </t-button>
        </template>
      </modal>
    </section>
  </div>
</template>

<script setup>
import {computed, inject, nextTick, onBeforeMount, watch, ref, toRaw} from 'vue'
import {TToggle, TButton, TInputGroup, TTextarea, TInput, TSelect, TRichSelect} from '@variantjs/vue'
import {
  InformationCircleIcon,
  CheckIcon,
  CheckCircleIcon,
  MinusIcon,
  MinusCircleIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'

import Breadcrumbs from '~src/shared/components/BreadcrumbNavigation.vue'
import Modal from '~src/shared/components/ConfirmationModal.vue'

const win = inject('win')
const swal = inject('swal')
const axios = inject('axios')

const defaultFields = {
  title: '',
  post_type: 'post', // Default to 'post' type
  taxonomy: null, // Default taxonomy
  terms: [],
  enabled: true
}
const configFields = ref({ ...defaultFields })

const endpoints = ref({
  configs: {
    all: '', // GET
    crud: '', // GET/POST/DELETE
    taxonomy: '' // GET
  }
})
const config = inject('pluginConfig')

const currentConfigs = ref([])
const showingEditForm = ref(false)
const showingCreateForm = ref(false)
const selectedConfig = ref(null)
const hasLoaded = ref(false)
const isSubmitting = ref(false)

watch(configFields, (newVal, oldVal) => {
  if (newVal) {
    const { post_type, taxonomy } = newVal
    if (post_type && taxonomy) {
      // Clear out taxonomy and terms if post_type or taxonomy changes
      const availableTaxonomies = config.postTerms[post_type]['_taxonomies'] || [];
      if (!availableTaxonomies.includes(taxonomy)) {
        configFields.value.taxonomy = null;
        configFields.value.terms = [];
      }
    }
  }
}, { deep: true, immediate: true })

const canSubmit = computed(() => {
  if (isSubmitting.value) {
    return false
  }
  if (!configFields.value.title || configFields.value.title.length === 0) {
    return false
  }
  if (!configFields.value.post_type || configFields.value.post_type.length === 0) {
    return false
  }
  return true
})

const getTaxonomy = (conf) => {
  // If no taxonomy is selected, return 'All Taxonomies' (as all will be included)
  if (!conf.taxonomy) {
    return 'Any Taxonomy'
  }
  
  // Convert to uppercase first letter
  let titleCase = conf.taxonomy.charAt(0).toUpperCase() + conf.taxonomy.slice(1);

  // Replace underscores with spaces
  titleCase = titleCase.replace(/_/g, ' ');

  return titleCase;
}

const getTerms = (conf) => {
  if (!conf.taxonomy) {
    return 'Any Term'
  }
  const terms = JSON.parse(conf.terms || "[]")
  const selectedTerms = terms.map(term_id => {
    const selected = (config.postTerms[conf.post_type][conf.taxonomy] || []).find(cat => cat.term_id == term_id)
    if (selected) {
      return selected.name
    }
    return null
  }).filter(t => t !== null)
  return selectedTerms.toString()
}

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

const createConfig = () => {
  // Clear out any old data
  configFields.value = { ...defaultFields }
  selectedConfig.value = null
  // Show create form
  showingCreateForm.value = true
}

const closeCreateForm = () => {
  // Reset form and values on close
  showingCreateForm.value = false
  configFields.value = { ...defaultFields }
}

const selectAndEditConfig = (config) => {
  // Flag the selected config and show update form
  configFields.value.title = config.title
  configFields.value.post_type = config.post_type
  configFields.value.taxonomy = config.taxonomy
  configFields.value.terms = JSON.parse(config.terms || "[]")
  configFields.value.enabled = config.enabled == 1
  selectedConfig.value = config
  showingEditForm.value = true
}

const closeEditForm = () => {
  showingEditForm.value = false
  selectedConfig.value = null
  configFields.value = { ...defaultFields }
}

const doUpdate = async (id) => {
  await doSave(id)
}

const doSave = async (id = null) => {
  isSubmitting.value = true
  try {
    let formData = toRaw(configFields.value)
    if (id) {
      formData.id = id
    }
    if (config.settings.enable_debug_messages) {
      console.log(':: DEBUG ::', formData)
    }
    const rst = await axios.post(endpoints.value.configs.crud, formData)
    if (config.settings.enable_debug_messages) {
      console.log(':: DEBUG - Response ::', rst)
    }
    const { data = null, success = null } = rst?.data
    if (success === false) {
      swal.fire({
        position: 'top-end',
        icon: 'warning',
        title: 'Error updating config',
        showConfirmButton: false,
        timer: 1500
      })
    } else if (data && rst.status === 200) {
      swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Config updated successfully',
        showConfirmButton: false,
        timer: 1500
      })
      // Close any modals
      closeCreateForm()
      closeEditForm()

      // Fetch all items (including new/updated)
      const allItems = await deFetchAll()

      // Update config and local values
      config.configs = allItems || []
      currentConfigs.value = allItems || []

      // Reset config form
      configFields.value = { ...defaultFields }
    } else {
      swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Wordpress responded with an error',
        footer: '<div class="overflow-footer w-full">' + JSON.stringify(rst, null, 2) + '</div>'
      })
    }
  } catch (err) {
    if (err?.code !== 'ERR_CANCELED') {
      const text = err?.response?.data?.error || 'Server responded with an error'
      swal.fire({
        icon: 'error',
        title: 'Error',
        text,
        footer: '<div class="overflow-footer w-full">' + err.message + '</div>'
      })
    }
  }
  finally {
    isSubmitting.value = false
  }
}

const deFetchAll = async () => {
  try {
    const rst = await axios.get(endpoints.value.configs.all)
    if (config.settings.enable_debug_messages) {
      console.log(':: DEBUG - Response ::', rst)
    }
    const { data = null, success = null } = rst?.data
    if (success === false) {
      swal.fire({
        position: 'top-end',
        icon: 'warning',
        title: 'Error fetching configs',
        showConfirmButton: false,
        timer: 1500
      })
    } else if (data && rst.status === 200) {
      return data
    } else {
      swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Wordpress responded with an error',
        footer: '<div class="overflow-footer w-full">' + JSON.stringify(rst, null, 2) + '</div>'
      })
      return false
    }
  } catch (err) {
    if (err?.code !== 'ERR_CANCELED') {
      const text = err?.response?.data?.error || 'Server responded with an error'
      swal.fire({
        icon: 'error',
        title: 'Error',
        text,
        footer: '<div class="overflow-footer w-full">' + err.message + '</div>'
      })
    }
    return false
  }
}

const doDestroy = (id) => {
  swal.fire({
    icon: 'warning',
    title: 'Are you sure?',
    text: 'Delete item now? This action cannot be undone.',
    showCancelButton: true,
    confirmButtonText: 'Delete',
  }).then(async (result) => {
    if (result.isConfirmed) {
      isSubmitting.value = true
      try {
        if (config.settings.enable_debug_messages) {
          console.log(':: DEBUG :: deleting config', id)
        }
        const rst = await axios.delete(endpoints.value.configs.crud, { data: { id } })
        if (config.settings.enable_debug_messages) {
          console.log(':: DEBUG - Response ::', rst)
        }
        const { success = null } = rst?.data
        if (success === true && rst.status === 200) {
          swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Config deleted successfully',
            showConfirmButton: false,
            timer: 1500
          })
          // Fetch all items (including new/updated)
          const allItems = await deFetchAll()

          // Update config and local values
          config.configs = allItems || []
          currentConfigs.value = allItems || []

          // Reset config form
          configFields.value = { ...defaultFields }
        } else {
          swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'Unable to delete config, please try again later',
            showConfirmButton: false,
            timer: 1500
          })
          isSubmitting.value = false
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
        isSubmitting.value = false
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
  ]
}

</script>

