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
    </section>
  </div>
</template>

<script setup>
import {onBeforeMount, reactive, computed, ref, nextTick, toRaw, inject} from 'vue'
import {TToggle, TButton, TRichSelect, TTextarea, TInput, TSelect} from '@variantjs/vue'
import Slider from '@vueform/slider'
// import Breadcrumbs from '~src/shared/components/BreadcrumbNavigation.vue'
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

const hasChanged = computed(() => {
  // compare two objects
  const a = JSON.stringify(settings)
  const b = JSON.stringify(oldSettings)
  // eslint-disable-next-line vue/no-side-effects-in-computed-properties
  ui.actionKey = ui.actionKey + 1
  return (a === b)
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

  if (!win.$jensiAiConfig.nonce) {
    win.$jensiAiConfig.nonce = config.rest.nonce
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

