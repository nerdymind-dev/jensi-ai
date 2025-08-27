<template>
  <div>
    <breadcrumbs :links="getCrumbs()" />

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
                          <color-input v-model="settings[item.id]" format="hex" />
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
                        <div v-else-if="item.type === 'config'" class="mx-3 mt-8 md:mt-6">
                          <template v-if="settings.jensi_ai_api_key">
                            <t-button
                              variant="secondary"
                              @click="showDSModal = true"
                            >
                              {{ selectedDataSource ? 'Change' : 'Select' }} Data Source
                            </t-button>
                            <div v-if="selectedDataSource" class="mt-3 p-3 border border-gray-200 rounded bg-gray-50">
                              <p><strong>Name:</strong> {{ selectedDataSource.name }}</p>
                              <p v-if="selectedDataSource.description"><strong>Description:</strong> {{ selectedDataSource.description }}</p>
                              <p><strong>ID:</strong> {{ selectedDataSource.id }}</p>
                            </div>
                          </template>
                          <template v-else>
                            <p class="text-red-400">You must set your API key before selecting a data source.</p> 
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
        :show="showDSModal"
        type="info"
        :custom-icon="true"
        @close="showDSModal = false">
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
            <path d="M17 2.75a.75.75 0 00-1.5 0v5.5a.75.75 0 001.5 0v-5.5zM17 15.75a.75.75 0 00-1.5 0v1.5a.75.75 0 001.5 0v-1.5zM3.75 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5a.75.75 0 01.75-.75zM4.5 2.75a.75.75 0 00-1.5 0v5.5a.75.75 0 001.5 0v-5.5zM10 11a.75.75 0 01.75.75v5.5a.75.75 0 01-1.5 0v-5.5A.75.75 0 0110 11zM10.75 2.75a.75.75 0 00-1.5 0v1.5a.75.75 0 001.5 0v-1.5zM10 6a2 2 0 100 4 2 2 0 000-4zM3.75 10a2 2 0 100 4 2 2 0 000-4zM16.25 10a2 2 0 100 4 2 2 0 000-4z" />
          </svg>
        </template>
        >
        <template #title>
          JENSi AI Data Source
        </template>
        <template #content>
          <div class="w-full">
            <div class="flex flex-col gap-3">
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Create new config
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
                    Select existing config
                    <sup class="text-red-400">*</sup>
                  </label>
                  <t-rich-select
                    class="col-span-3"
                    v-model="dataSourceConfig.config_id"
                    :fetch-options="fetchOptions"
                    valueAttribute="id"
                    textAttribute="name"
                    :options="currentDateSources"
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
            :class="{ 'opacity-25': !canSubmit || isSubmitting }"
            :disabled="!canSubmit || isSubmitting">
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

const oldSettings = {}
const settings = reactive({ ...oldSettings })
const ui = reactive({ actionKey: 0, loaded: false })
const structure = reactive({ sections: {}, options: {} })

const hasLoaded = ref(false)
const isSubmitting = ref(false)

const endpoints = ref({ settings: '', data_sources: '' })
const config = inject('pluginConfig')
const toggledPasswordFields = ref({})

const showDSModal = ref(false)
const selectedDataSource = ref(null)
const currentDateSources = ref([])
const dataSourceConfig = ref({ ...defaultDsConfig })

const hasChanged = computed(() => {
  // compare two objects
  const a = JSON.stringify(settings)
  const b = JSON.stringify(oldSettings)
  // eslint-disable-next-line vue/no-side-effects-in-computed-properties
  ui.actionKey = ui.actionKey + 1
  return (a === b)
})

const canSubmit = computed(() => {
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

const fetchOptions = async (q) => {
  try {
    // Need to add the bearer token to the request
    const rst = await axios.get(endpoints.value.data_sources.get, {
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

const doSaveDataSource = async () => {
  if (!canSubmit.value) {
    return
  }

  isSubmitting.value = true
  try {
    if (dataSourceConfig.value.create_config) {
      const rst = await axios.post(endpoints.value.data_sources.create, dataSourceConfig.value)
      if (rst.status === 200) {
        const config = rst.data.data || null
        if (config) {
          // Set the selected data source
          currentDateSources.value.push(config)
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
      selectedDataSource.value = currentDateSources.value.find((item) => item.id === dataSourceConfig.value.config_id)

      // Save the settings and close the modal
      await doSave()
      showDSModal.value = false

      // Reset the data source config
      dataSourceConfig.value = { ...defaultDsConfig }
    }
  } catch (err) {
    if (err?.code !== 'ERR_CANCELED') {
      console.log(err)
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

  // fetch available data sources
  if (settings.jensi_ai_api_key) {
    try {
      const rst = await axios.get(endpoints.value.data_sources.get)
      if (rst.status === 200) {
        currentDateSources.value = rst.data || []
        // if we have a selected data source, set it
        if (settings.jensi_ai_data_source) {
          const existing = currentDateSources.value.find((item) => item.id === settings.jensi_ai_data_source)
          if (existing) {
            selectedDataSource.value = existing
          }
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

