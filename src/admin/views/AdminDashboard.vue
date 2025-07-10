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
                  <!--Title-->
                  <h2
                    class="font-sans break-normal text-gray-600 pb-1 text-lg w-full text-left">
                    AI configs
                  </h2>

                  <div class="my-4 border-l-4 border-blue-400 bg-blue-50 p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <InformationCircleIcon class="h-5 w-5 text-blue-400" aria-hidden="true" />
                      </div>
                      <div class="ml-3">
                        <p class="text-sm text-blue-700">
                          Configurations are used to automate content generation. Based on the settings, when a new post is created, the automation will be triggered.
                          If multiple configuration overlap, the most recent one will be used.
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
                            <div class="flex items-center space-x-2">
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
                            <p class="mt-3">
                              Sections
                            </p>
                            <template v-for="section in JSON.parse(conf.sections || '[]')">
                              <span class="mt-1 mr-1 inline-flex items-center gap-x-2 rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">
                                <small>
                                  {{ getType(section) }}
                                </small>
                                <strong>{{ getPersona(section) }}</strong>
                              </span>
                            </template>
                            <div class="my-3">
                              <p>
                                Category
                              </p>
                              <p class="text-xs text-gray-500 whitespace-normal">
                                {{ getTerms(conf) }}
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
                  Category
                  <sup class="text-red-400">*</sup>
                </label>
                <t-rich-select
                  class="col-span-3"
                  v-model="configFields.terms"
                  :options="config.postTerms"
                  valueAttribute="term_id"
                  textAttribute="name"
                  multiple
                  tags
                />
              </div>
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Sections
                  <sup class="text-red-400">*</sup>
                </label>
                <div class="col-span-3">
                  <t-button @click="() => addSection(configFields)" variant="secondary">
                    Add Section
                  </t-button>
                </div>
              </div>
              <div class="w-full">
                <ul role="list" class="mt-3 grid grid-cols-1">
                  <li v-for="(section, idx) in configFields.sections"
                      class="col-span-1 flex rounded-md shadow">
                    <div :class="['bg-gray-100', 'flex px-3 flex-shrink-0 items-center justify-center rounded-l-md text-sm font-medium']">
                      <div>
                        <label
                          class="col-span-2 block font-bold md:text-left mb-3 md:mb-0 pr-4">
                          Section
                          <sup class="text-red-600">*</sup>
                        </label>
                        <t-select
                          class="col-span-3"
                          v-model="configFields.sections[idx].type"
                          :options="availableOptions(configFields)"
                          valueAttribute="id"
                          textAttribute="title"
                        />
                      </div>
                    </div>
                    <div class="flex flex-1 items-center justify-between truncate rounded-r-md border-b border-r border-t border-gray-100 bg-white">
                      <div class="flex-1 px-4 py-2">
                        <label
                          class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                          Persona
                          <sup class="text-red-400">*</sup>
                        </label>
                        <t-select
                          class="col-span-3"
                          v-model="configFields.sections[idx].persona_id"
                          :options="config.personas"
                          valueAttribute="id"
                          textAttribute="title"
                        />
                      </div>
                      <div class="flex-shrink-0 pr-2">
                        <button @click="removeSection(configFields, idx)" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-red-400 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                          <TrashIcon class="h-5 w-5" />
                        </button>
                      </div>
                    </div>
                  </li>
                </ul>
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
            :class="{ 'opacity-25': !canSubmit || isSubmitting }"
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
                  Category
                  <sup class="text-red-400">*</sup>
                </label>
                <t-rich-select
                  class="col-span-3"
                  v-model="configFields.terms"
                  :options="config.postTerms"
                  valueAttribute="term_id"
                  textAttribute="name"
                  multiple
                  tags
                />
              </div>
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Sections
                  <sup class="text-red-400">*</sup>
                </label>
                <div class="col-span-3">
                  <t-button @click="() => addSection(configFields)" variant="secondary">
                    Add Section
                  </t-button>
                </div>
              </div>
              <div class="w-full">
                <ul role="list" class="mt-3 grid grid-cols-1">
                  <li v-for="(section, idx) in configFields.sections"
                      class="col-span-1 flex rounded-md shadow">
                    <div :class="['bg-gray-100', 'flex px-3 flex-shrink-0 items-center justify-center rounded-l-md text-sm font-medium']">
                      <div>
                        <label
                          class="col-span-2 block font-bold md:text-left mb-3 md:mb-0 pr-4">
                          Section
                          <sup class="text-red-600">*</sup>
                        </label>
                        <t-select
                          class="col-span-3"
                          v-model="configFields.sections[idx].type"
                          :options="availableOptions(configFields)"
                          valueAttribute="id"
                          textAttribute="title"
                        />
                      </div>
                    </div>
                    <div class="flex flex-1 items-center justify-between truncate rounded-r-md border-b border-r border-t border-gray-100 bg-white">
                      <div class="flex-1 px-4 py-2">
                        <label
                          class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                          Persona
                          <sup class="text-red-400">*</sup>
                        </label>
                        <t-select
                          class="col-span-3"
                          v-model="configFields.sections[idx].persona_id"
                          :options="config.personas"
                          valueAttribute="id"
                          textAttribute="title"
                        />
                      </div>
                      <div class="flex-shrink-0 pr-2">
                        <button @click="removeSection(configFields, idx)" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-red-400 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                          <TrashIcon class="h-5 w-5" />
                        </button>
                      </div>
                    </div>
                  </li>
                </ul>
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
            :class="{ 'opacity-25': !canSubmit || isSubmitting }"
            :disabled="!canSubmit || isSubmitting">
            Create Config
          </t-button>
        </template>
      </modal>
    </section>
  </div>
</template>

<script setup>
import {computed, inject, nextTick, onBeforeMount, ref, toRaw} from 'vue'
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
  post_type: 'post',
  terms: [],
  sections: [],
  enabled: true
}
const configFields = ref({ ...defaultFields })

const endpoints = ref({
  configs: {
    all: '', // GET
    crud: '', // GET/POST/DELETE
  }
})
const config = inject('pluginConfig')

const currentConfigs = ref([])
const showingEditForm = ref(false)
const showingCreateForm = ref(false)
const selectedConfig = ref(null)
const hasLoaded = ref(false)
const isSubmitting = ref(false)

const availableOptions = (conf) => {
  // Return values not currently set
  return (config.contentTypes || []).map(ct => {
    return {
      ...ct,
      disabled: conf.sections.findIndex(c => c.type === ct.id) !== -1
    }
  })
}

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
  if (!configFields.value.terms || configFields.value.terms.length === 0) {
    return false
  }
  if (!configFields.value.sections || configFields.value.sections.length === 0) {
    return false
  }
  if (configFields.value.sections.filter(s => !s.persona_id || s.persona_id.length === 0).length) {
    return false
  }
  if (configFields.value.sections.filter(s => !s.type || s.type.length === 0).length) {
    return false
  }
  return true
})

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
const getTerms = (conf) => {
  const terms = JSON.parse(conf.terms || "[]")
  const selectedTerms = terms.map(term_id => {
    const selected = config.postTerms.find(cat => cat.term_id == term_id)
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
  if (!configFields.value.sections.length) {
    addSection(configFields.value)
  }
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
  configFields.value.sections = JSON.parse(config.sections || "[]")
  if (!configFields.value.sections.length) {
    addSection(configFields.value)
  }
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

const addSection = (config) => {
  config.sections.push({
    type: null,
    persona_id: null,
  })
}

const removeSection = (config, idx) => {
  swal.fire({
    icon: 'warning',
    title: 'Delete section?',
    showCancelButton: true,
    confirmButtonText: 'Delete',
  }).then(async (result) => {
    if (result.isConfirmed) {
      config.sections.splice(idx, 1)
    }
  })
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

