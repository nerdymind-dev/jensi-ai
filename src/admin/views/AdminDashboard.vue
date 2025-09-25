<template>
  <div>
    <!-- <breadcrumbs :links="getCrumbs()" /> -->

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
            Cancel
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
                <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Agent
                </label>
                <div class="col-span-3">
                  <t-button variant="secondary" @click="showAgentModal = true">
                    {{ selectedJensiAgent ? 'Change' : 'Select' }} Agent
                  </t-button>
                  <div v-if="selectedJensiAgent" class="mt-1">
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 inset-ring inset-ring-blue-700/10 dark:bg-blue-400/10 dark:text-blue-400 dark:inset-ring-blue-400/30">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 inline-block mr-1">
                        <path d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z" />
                      </svg>
                      {{ selectedJensiAgent.name }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="md:grid grid-cols-5">
                <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Data Source
                </label>
                <div class="col-span-3">
                  <template v-if="selectedJensiAgent">
                    <t-button variant="secondary" @click="openDataSourceModal">
                      {{ selectedDataSource ? 'Change' : 'Select' }} Data Source
                    </t-button>
                    <div v-if="selectedDataSource" class="mt-1">
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 inset-ring inset-ring-blue-700/10 dark:bg-blue-400/10 dark:text-blue-400 dark:inset-ring-blue-400/30">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 inline-block mr-1">
                        <path d="M5.127 3.502 5.25 3.5h9.5c.041 0 .082 0 .123.002A2.251 2.251 0 0 0 12.75 2h-5.5a2.25 2.25 0 0 0-2.123 1.502ZM1 10.25A2.25 2.25 0 0 1 3.25 8h13.5A2.25 2.25 0 0 1 19 10.25v5.5A2.25 2.25 0 0 1 16.75 18H3.25A2.25 2.25 0 0 1 1 15.75v-5.5ZM3.25 6.5c-.04 0-.082 0-.123.002A2.25 2.25 0 0 1 5.25 5h9.5c.98 0 1.814.627 2.123 1.502a3.819 3.819 0 0 0-.123-.002H3.25Z" />
                      </svg>
                      {{ selectedDataSource.name }}
                    </span>
                  </div>
                  </template>
                  <template v-else>
                      <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700 inset-ring inset-ring-red-600/10 dark:bg-red-400/10 dark:text-red-400 dark:inset-ring-red-400/20">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 inline-block mr-1">
                          <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                        </svg>
                        Select an agent first
                      </span>
                  </template>
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
            Cancel
          </t-button>
          <t-button
            class="ml-2" @click.native="(e) => doSave()"
            :class="{ 'opacity-25 cursor-not-allowed': !canSubmit || isSubmitting }"
            :disabled="!canSubmit || isSubmitting">
            Create Config
          </t-button>
        </template>
      </modal>

      <!-- Agent Selection Modal -->
      <modal
        :show="showAgentModal"
        type="info"
        :custom-icon="true"
        @close="showAgentModal = false">
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
            <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z" />
          </svg>
        </template>
        <template #title>
          Select Agent
        </template>
        <template #content>
          <div class="w-full">
            <div class="max-h-64 overflow-y-auto">
              <div v-for="agent in jensiAgents" :key="agent.id" 
                   @click="selectJensiAgent(agent)"
                   class="flex items-center justify-between p-3 border rounded-lg cursor-pointer hover:bg-gray-50 mb-2"
                   :class="{ 'border-blue-500 bg-blue-50': selectedJensiAgent?.id === agent.id }">
                <div>
                  <div class="font-medium">{{ agent.name }}</div>
                  <div class="text-sm text-gray-500">{{ agent.description || 'No description' }}</div>
                </div>
              </div>
            </div>
          </div>
        </template>
        <template #footer>
          <t-button variant="secondary" @click.native="showAgentModal = false">
            Cancel
          </t-button>
          <t-button class="ml-2" @click.native="confirmJensiAgent" :disabled="!selectedJensiAgent">
            Select Agent
          </t-button>
        </template>
      </modal>

      <!-- Data Source Selection Modal -->
      <modal
        :show="showDataSourceModal"
        type="info"
        :custom-icon="true"
        @close="showDataSourceModal = false">
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
            <path d="M5.566 4.657A4.505 4.505 0 0 1 6.75 4.5h10.5c.41 0 .806.055 1.183.157A3 3 0 0 0 15.75 3h-7.5a3 3 0 0 0-2.684 1.657ZM2.25 12a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3v-6ZM5.25 7.5c-.41 0-.806.055-1.184.157A3 3 0 0 1 6.75 6h10.5a3 3 0 0 1 2.683 1.657A4.505 4.505 0 0 0 18.75 7.5H5.25Z" />
          </svg>
        </template>
        <template #title>
          Select Data Source
        </template>
        <template #content>
          <div class="w-full">
            <!-- Create vs Select Toggle -->
            <div class="mb-4 p-3 border border-gray-200 rounded bg-gray-50">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium">
                  {{ dataSourceConfig.create_config ? 'Create new data source' : 'Select existing data source' }}
                </span>
                <t-toggle v-model="dataSourceConfig.create_config" />
              </div>
            </div>

            <!-- Create New Data Source -->
            <template v-if="dataSourceConfig.create_config">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                  <t-input v-model="dataSourceConfig.name" placeholder="Enter data source name" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <t-textarea v-model="dataSourceConfig.description" placeholder="Enter description (optional)" />
                </div>
              </div>
            </template>

            <!-- Select Existing Data Source -->
            <template v-else>
              <div class="max-h-64 overflow-y-auto">
                <div v-if="jensiDataSources.length === 0" class="p-4 text-center text-gray-500">
                  No data sources available. Try creating a new one instead.
                </div>
                <div v-for="dataSource in jensiDataSources" :key="dataSource.id" 
                     @click="selectDataSource(dataSource)"
                     class="flex items-center justify-between p-3 border rounded-lg cursor-pointer hover:bg-gray-50 mb-2"
                     :class="{ 'border-blue-500 bg-blue-50': selectedDataSource?.id === dataSource.id }">
                  <div>
                    <div class="font-medium">{{ dataSource.name }}</div>
                    <div class="text-sm text-gray-500">{{ dataSource.description || 'No description' }}</div>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </template>
        <template #footer>
          <t-button variant="secondary" @click.native="showDataSourceModal = false">
            Cancel
          </t-button>
          <t-button class="ml-2" @click.native="confirmDataSource" :disabled="!canConfirmDataSource">
            {{ dataSourceConfig.create_config ? 'Create' : 'Select' }} Data Source
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

// Form fields for create/edit
const defaultFields = {
  title: '',
  post_type: 'post', // Default to 'post' type
  taxonomy: null, // Default taxonomy
  terms: [],
  enabled: true,
  agent_id: '',
  data_source_id: '',
}
const configFields = ref({ ...defaultFields })

// Data source configuration for create/select modal
const defaultDataSourceConfig = {
  create_config: true,
  name: '',
  description: '',
  config_id: null,
  type: 'wordpress'
}
const dataSourceConfig = ref({ ...defaultDataSourceConfig })

const endpoints = ref({
  configs: {
    all: '', // GET
    crud: '', // GET/POST/DELETE
    taxonomy: '' // GET
  },
  agents: { get: '' },
  data_sources: { get: '', create: '' }
})
const config = inject('pluginConfig')

const currentConfigs = ref([])
const showingEditForm = ref(false)
const showingCreateForm = ref(false)
const selectedConfig = ref(null)
const hasLoaded = ref(false)
const isSubmitting = ref(false)

const currentAgents = ref([])
const jensiAgents = ref([])
const jensiDataSources = ref([])
const selectedAgent = ref(null)
const selectedJensiAgent = ref(null)
const selectedDataSource = ref(null)
const showAgentModal = ref(false)
const showDataSourceModal = ref(false)

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
  if (!configFields.value.agent_id || configFields.value.agent_id.length === 0) {
    return false
  }
  if (!configFields.value.data_source_id || configFields.value.data_source_id.length === 0) {
    return false
  }
  return true
})

const canConfirmDataSource = computed(() => {
  if (dataSourceConfig.value.create_config) {
    return dataSourceConfig.value.name.trim().length > 0
  } else {
    return selectedDataSource.value !== null
  }
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
  selectedJensiAgent.value = null
  selectedDataSource.value = null
  // Show create form
  showingCreateForm.value = true
}

const selectAndEditConfig = (config) => {
  // Flag the selected config and show update form
  configFields.value = { ...config }

  // Need to convert boolean and array values
  configFields.value.terms = JSON.parse(config.terms || "[]")
  configFields.value.enabled = config.enabled == 1

  // Update the selected config and show modal
  selectedConfig.value = config
  showingEditForm.value = true
}

const closeCreateForm = () => {
  showingEditForm.value = false
  selectedConfig.value = null
  configFields.value = { ...defaultFields }
  selectedJensiAgent.value = null
  selectedDataSource.value = null
}

const closeEditForm = () => {
  showingEditForm.value = false
  selectedConfig.value = null
  selectedJensiAgent.value = null
  selectedDataSource.value = null
}

const selectJensiAgent = (agent) => {
  selectedJensiAgent.value = agent
}

const confirmJensiAgent = () => {
  if (selectedJensiAgent.value) {
    const target = showingCreateForm.value ? configFields.value : selectedAgent.value
    target.agent_id = selectedJensiAgent.value.id
    showAgentModal.value = false
  }
}

const selectDataSource = (dataSource) => {
  selectedDataSource.value = dataSource
}

const confirmDataSource = async () => {
  try {
    if (dataSourceConfig.value.create_config) {
      // Create new data source
      if (!selectedJensiAgent.value) {
        swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Agent must be selected before creating a data source'
        })
        return
      }
      
      const response = await axios.post(endpoints.value.data_sources.create, {
        ...dataSourceConfig.value,
        agent_id: selectedJensiAgent.value.id
      })
      
      if (response.data.success) {
        const newDataSource = response.data.data
        jensiDataSources.value.push(newDataSource)
        selectedDataSource.value = newDataSource
        
        const target = showingCreateForm.value ? configFields.value : selectedAgent.value
        target.data_source_id = newDataSource.id
        
        // Reset data source config
        dataSourceConfig.value = { ...defaultDataSourceConfig }
        showDataSourceModal.value = false
        
        swal.fire({
          position: 'top-end',
          icon: 'success',
          title: 'Data source created successfully',
          showConfirmButton: false,
          timer: 1500
        })
      }
    } else {
      // Select existing data source
      if (selectedDataSource.value) {
        const target = showingCreateForm.value ? configFields.value : selectedAgent.value
        target.data_source_id = selectedDataSource.value.id
        showDataSourceModal.value = false
      }
    }
  } catch (err) {
    console.error('Error with data source:', err)
    swal.fire({
      icon: 'error',
      title: 'Error',
      text: err?.response?.data?.message || 'An error occurred'
    })
  }
}

const openDataSourceModal = async () => {
  if (!selectedJensiAgent.value) {
    swal.fire({
      icon: 'warning',
      title: 'Select Agent First',
      text: 'Please select an agent before choosing a data source'
    })
    return
  }
  
  // Load data sources for the selected agent
  await loadJensiDataSources(selectedJensiAgent.value.id)
  
  // Reset data source config
  dataSourceConfig.value = { ...defaultDataSourceConfig }
  selectedDataSource.value = null
  
  showDataSourceModal.value = true
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

const loadJensiAgents = async () => {
  try {
    const response = await axios.get(endpoints.value.agents.get)
    if (response.data.success) {
      jensiAgents.value = response.data.data
    }
  } catch (err) {
    console.error('Error loading JENSi agents:', err)
  }
}

const loadJensiDataSources = async (agentId = null) => {
  try {
    const params = agentId ? { agent_id: agentId } : {}
    const response = await axios.get(endpoints.value.data_sources.get, { params })
    if (response.data.success) {
      jensiDataSources.value = response.data.data
    }
  } catch (err) {
    console.error('Error loading JENSi data sources:', err)
  }
}

const doLoad = async () => {
  await nextTick()

  if (!win.$jensiAiConfig.nonce) {
    win.$jensiAiConfig.nonce = config.rest.nonce
  }

  endpoints.value = config.rest.endpoints
  currentConfigs.value = config.configs || []
  currentAgents.value = config.agents || []

  // Load JENSi agents (but not data sources - they're loaded on-demand per agent)
  await loadJensiAgents()

  // make sure data is loaded before ui render
  hasLoaded.value = true
}

const getCrumbs = () => {
  return [
  ]
}

</script>

