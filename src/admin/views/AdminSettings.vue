<template>
  <div>
    <!-- <breadcrumbs :links="getCrumbs()" /> -->

    <section>
      <div v-if="hasLoaded" class="">
        <div class="sticky top-0 sm:top-10 md:top-20 lg:top-8 z-10 sm:flex sm:items-center py-3" style="background-color: #f0f0f1">
          <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold leading-6 text-gray-700">
              Settings
            </h1>
          </div>
          <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <div class="flex items-center justify-end gap-3">
              <t-button
                variant="outline"
                :disabled="isSubmitting"
                :data-rerendered="ui.actionKey"
                @click="resetToDefaults()"
              >
                Reset to Defaults
              </t-button>
              <t-button
                :disabled="hasChanged || isSubmitting"
                :loading="isSubmitting"
                :data-rerendered="ui.actionKey"
                @click="doSave()"
              >
                Save
              </t-button>
              <t-button
                variant="secondary"
                :disabled="hasChanged || isSubmitting"
                :data-rerendered="ui.actionKey"
                @click="doCancel()"
              >
                Cancel
              </t-button>
            </div>
          </div>
        </div>
        <div class="mt-8 flow-root">
          <div class="overflow-x-auto">
            <div class="inline-block min-w-full py-2 align-middle">
              <div class="">
                <div
                  v-for="(value, name) in structure.sections"
                  :key="`container.${name}`"
                  class="pb-4" >
                  <!--Title-->
                  <h2 class="font-sans break-normal text-gray-600 pb-1 text-lg w-full text-left">
                    {{ value }}
                  </h2>

                  <!--Card-->
                  <div class="p-8 mt-6 lg:mt-0 rounded shadow bg-white">
                    <div
                      v-for="item in getOptions(name)"
                      :key="item.id"
                      class="md:flex mb-6 gap-3">
                      <div class="md:w-2/5">
                        <label
                          class="block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4"
                          for="my-checkbox">
                          {{ item.name }}
                        </label>
                        <p class="py-2 text-sm text-gray-600">{{ item.description }}</p>
                      </div>
                      <div class="md:w-3/5">
                        <div v-if="item.type === 'toggle'">
                          <t-toggle v-model="settings[item.id]" />
                        </div>
                        <div v-else-if="item.type === 'dropdownMultiselect'">
                          <t-rich-select
                            v-model="settings[item.id]"
                            :options="item.options"
                            multiple
                            tags
                          />
                        </div>
                        <div v-else-if="item.type === 'dropdown'">
                          <t-select
                            v-model="settings[item.id]"
                            :options="item.options"
                          />
                        </div>
                        <div v-else-if="['textarea'].indexOf(item.type) > -1">
                          <t-textarea
                            v-model="settings[item.id]"
                          />
                        </div>
                        <div v-else-if="item.type === 'code'">
                          <v-ace-editor
                            v-model:value="settings[item.id]"
                            :lang="item.lang || 'html'"
                            theme="chrome"
                            style="height: 300px" />
                        </div>
                        <div v-else-if="item.type === 'color'">
                          <color-input v-model="settings[item.id]" :format="item.format || 'hex'" class="ring ring-offset-1 rounded-lg ring-gray-200" />
                        </div>
                        <div v-else-if="item.type === 'file' || item.type === 'multifile'">
                          <label
                            :for="item.id"
                            class="block h-64 relative overflow-hidden rounded">
                            <input
                              :id="item.id"
                              type="file"
                              class="overlayed"
                              :multiple="item.type === 'multifile'"
                              @change="(e) => handleUpload(e, item.id)"
                            />
                            <span
                              class="overlayed bg-gray-100 border-gray-200 border-2 text-gray-800 pointer-events-none flex justify-center items-center">
                              <span class="block text-center">
                                <slot>
                                  <strong>Upload File</strong>
                                </slot>
                                <small
                                  v-if="settings[item.id]"
                                  class="text-gray-600 block">
                                  {{ getUploadInfo(item.id) }}
                                </small>
                              </span>
                            </span>
                          </label>
                        </div>
                        <div v-else-if="item.type === 'slider'">
                          <div class="mx-3 mt-8 md:mt-6">
                            <slider
                              v-model="settings[item.id]"
                              :min="item.min"
                              :max="item.max"
                              class="slider-blue"
                            />
                          </div>
                        </div>
                        <div v-else-if="item.type === 'password'">
                          <div class="mx-3 mt-8 md:mt-6">
                            <t-input
                              v-model="settings[item.id]"
                              :type="toggledPasswordFields[item.id] ? 'text' : 'password'"
                            />
                            <div class="mt-2 flex">
                              <t-toggle v-model="toggledPasswordFields[item.id]" />
                              <span class="text-xs text-gray-300 ml-2 self-center">
                                {{ toggledPasswordFields[item.id] ? 'Hide' : 'Show' }}
                              </span>
                            </div>
                          </div>
                        </div>
                        <div v-else-if="item.type === 'config' && item.id === 'jensi_ai_agent'" class="mx-3 mt-8 md:mt-6">
                          <template v-if="settings.jensi_ai_api_key">
                            <t-button
                              variant="secondary"
                              @click="showAgentModal = true"
                            >
                              {{ selectedAgent ? 'Change' : 'Select' }} Agent
                            </t-button>
                            <div v-if="selectedAgent" class="mt-3 p-3 border border-gray-200 rounded bg-gray-50">
                              <p><strong>Name:</strong> {{ selectedAgent.name }}</p>
                              <p v-if="selectedAgent.description"><strong>Description:</strong> {{ selectedAgent.description }}</p>
                              <p><strong>ID:</strong> {{ getId(selectedAgent) }}</p>
                            </div>
                          </template>
                          <template v-else>
                            <p class="text-red-400">You must set your API key before selecting an agent.</p> 
                          </template>
                        </div>
                        <div v-else-if="item.type === 'config' && item.id === 'jensi_ai_data_source'" class="mx-3 mt-8 md:mt-6">
                          <template v-if="settings.jensi_ai_api_key && settings.jensi_ai_agent">
                            <t-button
                              variant="secondary"
                              @click="showDSModal = true"
                            >
                              {{ selectedDataSource ? 'Change' : 'Select' }} Data Source
                            </t-button>
                            <div v-if="selectedDataSource" class="mt-3 p-3 border border-gray-200 rounded bg-gray-50">
                              <p><strong>Name:</strong> {{ selectedDataSource.name }}</p>
                              <p v-if="selectedDataSource.description"><strong>Description:</strong> {{ selectedDataSource.description }}</p>
                              <p><strong>ID:</strong> {{ getId(selectedDataSource) }}</p>
                            </div>
                          </template>
                          <template v-else>
                            <p class="text-red-400">You must set your API key and select an agent before selecting a data source.</p> 
                          </template>
                        </div>
                        <div v-else>
                          <t-input
                            v-model="settings[item.id]"
                            :type="item.type"
                          />
                        </div>
                      </div>
                    </div>

                  </div>
                  <!--/Card-->
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <modal 
        :show="showAgentModal"
        type="info"
        :custom-icon="true"
        @close="showAgentModal = false">
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
            <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd" />
            <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z" />
          </svg>
        </template>
        <template #title>
          JENSi AI Agents
        </template>
        <template #content>
          <div class="w-full">
            <div class="flex flex-col gap-3">
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Select an agent
                  <sup class="text-red-400">*</sup>
                </label>
                <t-rich-select
                  class="col-span-3"
                  v-model="settings.jensi_ai_agent"
                  :fetch-options="fetchAgentOptions"
                  valueAttribute="id"
                  textAttribute="name"
                  :options="currentAgents"
                />
              </div>
            </div>
          </div>
        </template>
        <template #footer>
          <t-button variant="secondary" @click.native="showAgentModal = false">
            Nevermind
          </t-button>
          <t-button
            class="ml-2" @click.native="(e) => doSaveAgent()"
            :class="{ 'opacity-25 cursor-not-allowed': !agentCantSubmit || isSubmitting }"
            :disabled="!agentCantSubmit || isSubmitting">
            Save
          </t-button>
        </template>
      </modal>

      <modal 
        :show="showDSModal"
        type="info"
        :custom-icon="true"
        @close="showDSModal = false">
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
            <path d="M5.566 4.657A4.505 4.505 0 0 1 6.75 4.5h10.5c.41 0 .806.055 1.183.157A3 3 0 0 0 15.75 3h-7.5a3 3 0 0 0-2.684 1.657ZM2.25 12a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3v-6ZM5.25 7.5c-.41 0-.806.055-1.184.157A3 3 0 0 1 6.75 6h10.5a3 3 0 0 1 2.683 1.657A4.505 4.505 0 0 0 18.75 7.5H5.25Z" />
          </svg>
        </template>
        <template #title>
          JENSi AI Data Source
        </template>
        <template #content>
          <div class="w-full">
            <div class="flex flex-col gap-3">
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  {{ dataSourceConfig.create_config ? 'Create new data source' : 'Select existing data source' }}
                </label>
                <div>
                  <t-toggle v-model="dataSourceConfig.create_config" />
                </div>
              </div>

              <template v-if="dataSourceConfig.create_config">
                <div class="md:grid grid-cols-5">
                  <label
                    class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Name
                    <sup class="text-red-400">*</sup>
                  </label>
                  <t-input
                    class="col-span-3"
                    v-model="dataSourceConfig.name"
                  />
                </div>
                <div class="md:grid grid-cols-5">
                  <label
                    class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Description
                  </label>
                  <t-textarea
                    class="col-span-3"
                    v-model="dataSourceConfig.description"
                  />
                </div>
              </template>
              <template v-else>
                  <div class="md:grid grid-cols-5">
                  <label
                    class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Current data sources
                    <sup class="text-red-400">*</sup>
                  </label>
                  <t-rich-select
                    class="col-span-3"
                    v-model="dataSourceConfig.config_id"
                    :fetch-options="fetchDsOptions"
                    valueAttribute="id"
                    textAttribute="name"
                    :options="currentDataSources"
                  />
                </div>
              </template>
            </div>
          </div>
        </template>
        <template #footer>
          <t-button variant="secondary" @click.native="showDSModal = false">
            Nevermind
          </t-button>
          <t-button
            class="ml-2" @click.native="(e) => doSaveDataSource()"
            :class="{ 'opacity-25 cursor-not-allowed': !dsCanSubmit || isSubmitting }"
            :disabled="!dsCanSubmit || isSubmitting">
            Save
          </t-button>
        </template>
      </modal>
    </section>
  </div>
</template>

<script setup>
import {onBeforeMount, reactive, computed, ref, nextTick, toRaw, inject} from 'vue'
import {TToggle, TButton, TRichSelect, TTextarea, TInput, TSelect} from '@variantjs/vue'
import Slider from '@vueform/slider'
import Breadcrumbs from '~src/shared/components/BreadcrumbNavigation.vue'
import Modal from '~src/shared/components/ConfirmationModal.vue'
import ColorInput from 'vue-color-input'

import {VAceEditor} from 'vue3-ace-editor'
import ace from 'ace-builds'
ace.config.set(
  'basePath',
  'https://cdn.jsdelivr.net/npm/ace-builds@' + require('ace-builds').version + '/src-noconflict/',
)

const win = inject('win')
const swal = inject('swal')
const axios = inject('axios')

const defaultDsConfig = {
  create_config: true,
  name: '',
  description: '',
  config_id: null,
  type: 'wordpress',
}

const config = inject('pluginConfig')

const oldSettings = {}
const settings = reactive({ ...oldSettings })
const ui = reactive({ actionKey: 0, loaded: false })
const structure = reactive({ sections: {}, options: {} })

const hasLoaded = ref(false)
const isSubmitting = ref(false)

const endpoints = ref({ settings: '', data_sources: '' })
const settingsDefaults = ref({})
const toggledPasswordFields = ref({})

const showAgentModal = ref(false)
const selectedAgent = ref(null)
const currentAgents = ref([])

const showDSModal = ref(false)
const selectedDataSource = ref(null)
const currentDataSources = ref([])
const dataSourceConfig = ref({ ...defaultDsConfig })

const hasChanged = computed(() => {
  // compare two objects
  const a = JSON.stringify(settings)
  const b = JSON.stringify(oldSettings)
  // eslint-disable-next-line vue/no-side-effects-in-computed-properties
  ui.actionKey = ui.actionKey + 1
  return (a === b)
})

const agentCantSubmit = computed(() => {
  if (isSubmitting.value) {
    return false
  }
  if (!settings.jensi_ai_agent) {
    return false
  }
  return true
})

const dsCanSubmit = computed(() => {
  if (isSubmitting.value) {
    return false
  }
  if (dataSourceConfig.value.create_config) {
    if (!dataSourceConfig.value.name) {
      return false
    }
  } else {
    if (!dataSourceConfig.value.config_id) {
      return false
    }
    if (dataSourceConfig.value.config_id === settings.jensi_ai_data_source) {
      return false
    }
  }
  return true
})

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

const getId = (item) => {
  // Return the last 8 characters of the id
  if (item && item.id) {
    return item.id.slice(-8)
  }
  return ''
}

const resetToDefaults = () => {
  // Reset settings to defaults
  Object.assign(settings, settingsDefaults.value)
  swal.fire({
    position: 'top-end',
    icon: 'success',
    title: 'Settings have been reset to defaults. Press "Save" to apply.',
    showConfirmButton: false,
    timer: 1500
  })
}

const fetchAgentOptions = async (q) => {
  try {
    // Need to add the bearer token to the request
    const rst = await axios.get(endpoints.value.agents.get, {
      params: {
        search: q
      },
    })
    if (rst.status === 200) {
      return {
        results: rst.data.data || []
      }
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
  return []
}

const fetchDsOptions = async (q) => {
  try {
    // Need to add the bearer token to the request
    const rst = await axios.get(endpoints.value.data_sources.get, {
      params: {
        agent_id: settings.jensi_ai_agent,
        search: q
      },
    })
    if (rst.status === 200) {
      return {
        results: rst.data.data || []
      }
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
  return []
}

const doSaveAgent = async () => {
  if (!agentCantSubmit.value) {
    return
  }

  // Fetch the data sources for the selected agent
  const options = await fetchDsOptions('')
  console.log('options', options)
  currentDataSources.value = options.results || []

  // If the current selected data source is not in the list, clear it
  if (settings.jensi_ai_data_source) {
    const existing = currentDataSources.value.find((item) => item.id === settings.jensi_ai_data_source)
    if (existing) {
      selectedDataSource.value = existing
    } else {
      selectedDataSource.value = null
      settings.jensi_ai_data_source = null
    }
  }

  // Just save the settings
  await doSave()

  // Close the modal
  showAgentModal.value = false
}

const doSaveDataSource = async () => {
  if (!dsCanSubmit.value) {
    return
  }

  isSubmitting.value = true
  try {
    if (dataSourceConfig.value.create_config) {
      const rst = await axios.post(endpoints.value.data_sources.create, Object.assign(dataSourceConfig.value, {
        agent_id: settings.jensi_ai_agent,
      }))
      if (rst.status === 200) {
        const config = rst.data.data || null
        if (config) {
          // Set the selected data source
          currentDataSources.value.push(config)
          selectedDataSource.value = config
          settings.jensi_ai_data_source = config.id
          
          // Save the settings and close the modal
          await doSave()
          showDSModal.value = false

          // Reset the data source config
          dataSourceConfig.value = { ...defaultDsConfig }
        }
      } else {
        this.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'API responded with an error',
          footer: '<div class="overflow-footer w-full">' + JSON.stringify(rst, null, 2) + '</div>'
        })
      }
    } else {
      // Just save the selected config id to the settings
      settings.jensi_ai_data_source = dataSourceConfig.value.config_id
      selectedDataSource.value = currentDataSources.value.find((item) => item.id === dataSourceConfig.value.config_id)

      // Save the settings and close the modal
      await doSave()
      showDSModal.value = false

      // Reset the data source config
      dataSourceConfig.value = { ...defaultDsConfig }
    }
  } catch (err) {
    if (err?.code !== 'ERR_CANCELED') {
      console.log(err)
      const text = err?.response?.data?.message || 'Server responded with an error'
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

const doSave = async () => {
  isSubmitting.value = true
  try {
    if (settings.enable_debug_messages) {
      console.log(':: DEBUG ::', settings)
    }
    let data = toRaw(settings)
    const rst = await axios.post(endpoints.value.settings, data)
    if (settings.enable_debug_messages) {
      console.log(':: DEBUG - Response ::', rst)
    }
    // const rst = { success: true }
    if (rst.status === 200) {
      swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Your settings has been saved.',
        showConfirmButton: false,
        timer: 1500
      })

      Object.keys(settings).forEach((key) => {
        oldSettings[key] = settings[key]
      })

      // Update the shared config
      config.settings = data

      // force rerendered
      ui.actionKey = ui.actionKey + 1
    } else {
      this.$swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Wordpress responded with an error',
        footer: '<div class="overflow-footer w-full">' + JSON.stringify(rst, null, 2) + '</div>'
      })
    }
  } catch (err) {
    if (err?.code !== 'ERR_CANCELED') {
      const text = err?.response?.data?.message || 'Server responded with an error'
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

const getOptions = (section) => {
  const options = { ...structure.options }
  const result = []

  Object.keys(options).forEach((key) => {
    const item = options[key]
    if (item.section === section) {
      item.id = key
      result.push(item)
    }
  })

  return result
}

const getUploadInfo = (key) => {
  // display the uploaded file names (think computed)
  const files = settings[key]
  return files.length === 1
    ? files[0].name
    : `${files.length} files selected`
}

const handleUpload = (e, key) => {
  // handle the file upload event (think methods)
  const files = Array.from(e.target.files) || []
  settings[key] = files.value
}

const doCancel = () => {
  const _settings = oldSettings
  Object.keys(_settings).forEach((key) => {
    oldSettings[key] = _settings[key]
    settings[key] = _settings[key]
  })
}

const doLoad = async () => {
  await nextTick()

  if (!win.$appConfig.nonce) {
    win.$appConfig.nonce = config.rest.nonce
  }

  const _structure = config.settingStructure
  structure['sections'] = _structure['sections']
  structure['options'] = _structure['options']

  const _settings = config.settings || {}
  endpoints.value = config.rest.endpoints

  // copy settings from server output
  Object.keys(_settings).forEach((key) => {
    oldSettings[key] = _settings[key]
    settings[key] = _settings[key]
  })

  // Set defaults for settings reset
  settingsDefaults.value = config.defaultSettings || {}

  // fetch available data sources
  if (settings.jensi_ai_api_key) {
    // Fetch agents first
    try {
      const rst = await axios.get(endpoints.value.agents.get)
      if (rst.status === 200) {
        currentAgents.value = rst.data.data || []
        // if we have a selected data source, set it
        if (settings.jensi_ai_agent) {
          const existing = currentAgents.value.find((item) => item.id === settings.jensi_ai_agent)
          if (existing) {
            selectedAgent.value = existing
          }
        }
      }
    } catch (err) {
      if (err?.code !== 'ERR_CANCELED') {
        const text = err?.response?.data?.message || 'Server responded with an error'
        swal.fire({
          icon: 'error',
          title: 'Error',
          text,
          footer: '<div class="overflow-footer w-full">' + err.message + '</div>'
        })
      }
    }

    // If agent set, fetch it's data sources
    if (settings.jensi_ai_agent && settings.jensi_ai_agent.length > 0) {
        try {
        const rst = await axios.get(endpoints.value.data_sources.get, {
          params: {
            agent_id: settings.jensi_ai_agent
          },
        })
        if (rst.status === 200) {
          currentDataSources.value = rst.data.data || []
          // if we have a selected data source, set it
          if (settings.jensi_ai_data_source) {
            const existing = currentDataSources.value.find((item) => item.id === settings.jensi_ai_data_source)
            if (existing) {
              selectedDataSource.value = existing
            }
          }
        }
      } catch (err) {
        if (err?.code !== 'ERR_CANCELED') {
          const text = err?.response?.data?.message || 'Server responded with an error'
          swal.fire({
            icon: 'error',
            title: 'Error',
            text,
            footer: '<div class="overflow-footer w-full">' + err.message + '</div>'
          })
        }
      }
    }
  }

  // make sure data is loaded before ui render
  hasLoaded.value = true
}

const getCrumbs = () => {
  return [
    { title: 'Settings', href: null }
  ]
}

</script>

<style lang="css" scoped>
.active {
  --tw-border-opacity: 1;

  /* yellow.500 */
  border-color: rgb(234 179 8 / var(--tw-border-opacity));
  font-weight: 700;
}

.overlayed {
  @apply absolute top-0 left-0 right-0 bottom-0 w-full block;
}
.slider-blue {
  --slider-connect-bg: #3B82F6;
  --slider-tooltip-bg: #3B82F6;
  --slider-handle-ring-color: #3B82F630;
}
</style>

<style src="@vueform/slider/themes/default.css"></style>

