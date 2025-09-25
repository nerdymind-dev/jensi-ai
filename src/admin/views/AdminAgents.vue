<template>
  <div>
    <section>
      <div class="">
        <div class="sm:flex sm:items-center py-3">
          <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold leading-6 text-gray-700">
              Agents
            </h1>
          </div>
          <t-button variant="secondary" @click="() => createAgent()">
            Create New Agent
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
                          Agents are AI chat widgets that can be displayed on specific pages/posts or globally. Each agent can have its own styling, agent configuration, and filtering rules.
                        </p>
                      </div>
                    </div>
                  </div>

                  <template v-if="currentAgents.length">
                    <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                      <li v-for="(agent, idx) in currentAgents" :key="`agent.${idx}`"
                          class="col-span-1 flex flex-col justify-between divide-y divide-gray-200 rounded-lg bg-white shadow">
                        <div class="flex w-full items-center justify-between space-x-6 p-6">
                          <div class="flex-1">
                            <div class="flex items-center justify-between space-x-2">
                              <div class="flex items-center space-x-1">
                                <h3 class="text-sm font-medium text-gray-900">{{ agent.name }}</h3>
                                <div v-if="agent.enabled">
                                  <CheckCircleIcon class="w-4 h-4 text-green-400" />
                                </div>
                                <div v-else>
                                  <MinusCircleIcon class="w-4 h-4 text-red-400" />
                                </div>
                              </div>
                            </div>
                            <div class="mt-1 flex items-center space-x-2 text-xs text-gray-500">
                              <template v-if="agent.display_everywhere">
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 ring-1 ring-blue-600/20 ring-inset">
                                  Global
                                </span>
                              </template>
                              <template v-else-if="agent.post_type && agent.post_type.length > 0">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-gray-500/10 ring-inset">
                                  {{ agent.post_type.join(', ') }}
                                </span>
                              </template>
                            </div>
                          </div>
                        </div>
                        <div>
                          <div class="-mt-px flex divide-x divide-gray-200">
                            <div class="flex w-0 flex-1">
                              <button @click="(e) => selectAndEditAgent(agent)" type="button" class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-bl-lg border border-transparent py-4 text-sm font-semibold text-gray-900">
                                Edit
                              </button>
                            </div>
                            <div class="-ml-px flex w-0 flex-1">
                              <button @click="(e) => doDestroy(agent.id)" type="button" class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold text-gray-900">
                                Delete
                              </button>
                            </div>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </template>
                  <template v-else>
                    <div class="pb-4">
                      <div class="p-8 mt-6 lg:mt-0 rounded shadow bg-white">
                        <div class="flex flex-col items-center justify-center">
                          <p class="text-lg mb-3">
                            No agents currently created, create one to get started!
                          </p>
                          <t-button @click="() => createAgent()">
                            Create New Agent
                          </t-button>
                        </div>
                      </div>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Agent Modal -->
      <modal
        :show="showingEditForm"
        type="info"
        :custom-icon="true"
        @close="closeEditForm">
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
            <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" />
            <path d="M13 3.5c0-.69.56-1.25 1.25-1.25H20A.75.75 0 0020 3v5.75c0 .41-.34.75-.75.75s-.75-.34-.75-.75V4.56l-7.22 7.22a.75.75 0 01-1.06-1.06L17.44 3.5H13.25c-.41 0-.75-.34-.75-.75z" />
          </svg>
        </template>
        <template #title>
          Edit agent
        </template>
        <template v-if="selectedAgent" #content>
          <div class="w-full">
            <div class="flex flex-col gap-3">
              <!-- Agent Details -->
              <div class="md:grid grid-cols-5">
                <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Name <sup class="text-red-400">*</sup>
                </label>
                <div class="col-span-3">
                  <t-input v-model="selectedAgent.name" />
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
                  <p v-if="selectedJensiAgent" class="text-sm text-gray-500 mt-1">
                    {{ selectedJensiAgent.name }}
                  </p>
                </div>
              </div>

              <div class="md:grid grid-cols-5">
                <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Data Source
                </label>
                <div class="col-span-3">
                  <t-button variant="secondary" @click="showDataSourceModal = true">
                    {{ selectedDataSource ? 'Change' : 'Select' }} Data Source
                  </t-button>
                  <p v-if="selectedDataSource" class="text-sm text-gray-500 mt-1">
                    {{ selectedDataSource.name }}
                  </p>
                </div>
              </div>

              <!-- Widget Settings -->
              <div class="border-t pt-3 mt-3">
                <h3 class="text-md font-medium text-gray-700 mb-3">Widget Settings</h3>
                
                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Enabled
                  </label>
                  <div>
                    <t-toggle v-model="selectedAgent.enabled" />
                  </div>
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Avatar URL
                  </label>
                  <t-input class="col-span-3" v-model="selectedAgent.avatar_url" placeholder="https://..." />
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Welcome Message
                  </label>
                  <t-textarea class="col-span-3" v-model="selectedAgent.welcome_message" />
                </div>

                <!-- Position & Colors -->
                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Bottom Offset (px)
                  </label>
                  <t-input class="col-span-3" type="number" v-model="selectedAgent.bottom_offset" />
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Right Offset (px)
                  </label>
                  <t-input class="col-span-3" type="number" v-model="selectedAgent.right_offset" />
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Primary Color
                  </label>
                  <t-input class="col-span-3" type="color" v-model="selectedAgent.primary_color" />
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Secondary Color
                  </label>
                  <t-input class="col-span-3" type="color" v-model="selectedAgent.secondary_color" />
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Background Color
                  </label>
                  <t-input class="col-span-3" type="color" v-model="selectedAgent.background_color" />
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Text Color
                  </label>
                  <t-input class="col-span-3" type="color" v-model="selectedAgent.text_color" />
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Secondary Text Color
                  </label>
                  <t-input class="col-span-3" type="color" v-model="selectedAgent.secondary_text_color" />
                </div>
              </div>

              <!-- Filtering Settings -->
              <div class="border-t pt-3 mt-3">
                <h3 class="text-md font-medium text-gray-700 mb-3">Display Rules</h3>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Display Everywhere
                  </label>
                  <div>
                    <t-toggle v-model="selectedAgent.display_everywhere" />
                  </div>
                </div>

                <template v-if="!selectedAgent.display_everywhere">
                  <div class="md:grid grid-cols-5">
                    <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                      Post Types
                    </label>
                    <t-rich-select
                      class="col-span-3"
                      v-model="selectedAgent.post_type"
                      :options="config.postTypes"
                      multiple
                      tags
                    />
                  </div>

                  <div class="md:grid grid-cols-5" v-if="selectedAgent.post_type && selectedAgent.post_type.length > 0">
                    <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                      Taxonomy
                    </label>
                    <t-select
                      class="col-span-3"
                      v-model="selectedAgent.taxonomy"
                      :options="taxonomyOptions"
                      placeholder="Select taxonomy (optional)"
                    />
                  </div>

                  <div class="md:grid grid-cols-5" v-if="selectedAgent.taxonomy">
                    <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                      Terms
                    </label>
                    <t-rich-select
                      class="col-span-3"
                      v-model="selectedAgent.terms"
                      :options="termsOptions"
                      valueAttribute="term_id"
                      textAttribute="name"
                      multiple
                      tags
                    />
                  </div>
                </template>
              </div>
            </div>
          </div>
        </template>
        <template v-if="selectedAgent" #footer>
          <t-button variant="secondary" @click.native="closeEditForm">
            Cancel
          </t-button>
          <t-button
            class="ml-2" @click.native="(e) => doUpdate(selectedAgent.id)"
            :class="{ 'opacity-25 cursor-not-allowed': !canSubmit || isSubmitting }"
            :disabled="!canSubmit || isSubmitting">
            Update Agent
          </t-button>
        </template>
      </modal>

      <!-- Create Agent Modal -->
      <modal
        :show="showingCreateForm"
        type="info"
        :custom-icon="true"
        @close="closeCreateForm">
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
          </svg>
        </template>
        <template #title>
          Create agent
        </template>
        <template #content>
          <div class="w-full">
            <!-- Same form fields as edit, but using agentFields -->
            <div class="flex flex-col gap-3">
              <div class="md:grid grid-cols-5">
                <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Name <sup class="text-red-400">*</sup>
                </label>
                <t-input class="col-span-3" v-model="agentFields.name" />
              </div>

              <div class="md:grid grid-cols-5">
                <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Agent <sup class="text-red-400">*</sup>
                </label>
                <div class="col-span-3">
                  <t-button variant="secondary" @click="showAgentModal = true">
                    {{ selectedJensiAgent ? 'Change' : 'Select' }} Agent
                  </t-button>
                  <p v-if="selectedJensiAgent" class="text-sm text-gray-500 mt-1">
                    {{ selectedJensiAgent.name }}
                  </p>
                </div>
              </div>

              <div class="md:grid grid-cols-5">
                <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Data Source <sup class="text-red-400">*</sup>
                </label>
                <div class="col-span-3">
                  <t-button variant="secondary" @click="showDataSourceModal = true">
                    {{ selectedDataSource ? 'Change' : 'Select' }} Data Source
                  </t-button>
                  <p v-if="selectedDataSource" class="text-sm text-gray-500 mt-1">
                    {{ selectedDataSource.name }}
                  </p>
                </div>
              </div>

              <!-- Widget Settings (simplified for create) -->
              <div class="border-t pt-3 mt-3">
                <h3 class="text-md font-medium text-gray-700 mb-3">Display Rules</h3>
                
                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Display Everywhere
                  </label>
                  <div>
                    <t-toggle v-model="agentFields.display_everywhere" />
                  </div>
                </div>

                <template v-if="!agentFields.display_everywhere">
                  <div class="md:grid grid-cols-5">
                    <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                      Post Types
                    </label>
                    <t-rich-select
                      class="col-span-3"
                      v-model="agentFields.post_type"
                      :options="config.postTypes"
                      multiple
                      tags
                    />
                  </div>

                  <div class="md:grid grid-cols-5" v-if="agentFields.post_type && agentFields.post_type.length > 0">
                    <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                      Taxonomy
                    </label>
                    <t-select
                      class="col-span-3"
                      v-model="agentFields.taxonomy"
                      :options="taxonomyOptionsForCreate"
                      placeholder="Select taxonomy (optional)"
                    />
                  </div>

                  <div class="md:grid grid-cols-5" v-if="agentFields.taxonomy">
                    <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                      Terms
                    </label>
                    <t-rich-select
                      class="col-span-3"
                      v-model="agentFields.terms"
                      :options="termsOptionsForCreate"
                      valueAttribute="term_id"
                      textAttribute="name"
                      multiple
                      tags
                    />
                  </div>
                </template>
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
            Create Agent
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
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
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
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
          </svg>
        </template>
        <template #title>
          Select Data Source
        </template>
        <template #content>
          <div class="w-full">
            <div class="max-h-64 overflow-y-auto">
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
          </div>
        </template>
        <template #footer>
          <t-button variant="secondary" @click.native="showDataSourceModal = false">
            Cancel
          </t-button>
          <t-button class="ml-2" @click.native="confirmDataSource" :disabled="!selectedDataSource">
            Select Data Source
          </t-button>
        </template>
      </modal>
    </section>
  </div>
</template>

<script setup>
import {computed, inject, nextTick, onBeforeMount, watch, ref, toRaw} from 'vue'
import {TToggle, TButton, TTextarea, TInput, TSelect, TRichSelect} from '@variantjs/vue'
import {
  InformationCircleIcon,
  CheckCircleIcon,
  MinusCircleIcon,
} from '@heroicons/vue/24/outline'

import Modal from '~src/shared/components/ConfirmationModal.vue'

const win = inject('win')
const swal = inject('swal')
const axios = inject('axios')

const defaultAgentFields = {
  name: '',
  agent_id: '',
  data_source_id: '',
  enabled: true,
  avatar_url: '',
  welcome_message: 'Hello! How can I assist you today?',
  bottom_offset: 20,
  right_offset: 20,
  primary_color: '#667eea',
  secondary_color: '#764ba2',
  background_color: '#ffffff',
  text_color: '#000000',
  secondary_text_color: '#ffffff',
  post_type: [],
  taxonomy: '',
  terms: [],
  display_everywhere: false
}

const agentFields = ref({ ...defaultAgentFields })

const endpoints = ref({
  agent_crud: {
    all: '',
    crud: ''
  },
  agents: { get: '' },
  data_sources: { get: '' }
})

const config = inject('pluginConfig')

const currentAgents = ref([])
const jensiAgents = ref([])
const jensiDataSources = ref([])
const selectedAgent = ref(null)
const selectedJensiAgent = ref(null)
const selectedDataSource = ref(null)

const hasLoaded = ref(false)
const isSubmitting = ref(false)
const showingCreateForm = ref(false)
const showingEditForm = ref(false)
const showAgentModal = ref(false)
const showDataSourceModal = ref(false)

// Computed properties for taxonomy and terms options
const taxonomyOptions = computed(() => {
  if (!selectedAgent.value?.post_type || selectedAgent.value.post_type.length === 0) {
    return []
  }
  
  const postType = selectedAgent.value.post_type[0] // Use first post type for simplicity
  const taxonomies = config.postTerms[postType]?._taxonomies || []
  
  return taxonomies.map(tax => ({ value: tax, text: tax }))
})

const termsOptions = computed(() => {
  if (!selectedAgent.value?.taxonomy || !selectedAgent.value?.post_type || selectedAgent.value.post_type.length === 0) {
    return []
  }
  
  const postType = selectedAgent.value.post_type[0]
  return config.postTerms[postType]?.[selectedAgent.value.taxonomy] || []
})

const taxonomyOptionsForCreate = computed(() => {
  if (!agentFields.value.post_type || agentFields.value.post_type.length === 0) {
    return []
  }
  
  const postType = agentFields.value.post_type[0]
  const taxonomies = config.postTerms[postType]?._taxonomies || []
  
  return taxonomies.map(tax => ({ value: tax, text: tax }))
})

const termsOptionsForCreate = computed(() => {
  if (!agentFields.value.taxonomy || !agentFields.value.post_type || agentFields.value.post_type.length === 0) {
    return []
  }
  
  const postType = agentFields.value.post_type[0]
  return config.postTerms[postType]?.[agentFields.value.taxonomy] || []
})

const canSubmit = computed(() => {
  const fields = showingCreateForm.value ? agentFields.value : selectedAgent.value
  return fields?.name && fields?.agent_id && fields?.data_source_id
})

// Methods
const createAgent = () => {
  agentFields.value = { ...defaultAgentFields }
  selectedJensiAgent.value = null
  selectedDataSource.value = null
  showingCreateForm.value = true
}

const selectAndEditAgent = async (agent) => {
  selectedAgent.value = { ...agent }
  
  // Find the corresponding JENSi agent and data source
  selectedJensiAgent.value = jensiAgents.value.find(a => a.id === agent.agent_id) || null
  selectedDataSource.value = jensiDataSources.value.find(ds => ds.id === agent.data_source_id) || null
  
  showingEditForm.value = true
}

const closeCreateForm = () => {
  showingCreateForm.value = false
  agentFields.value = { ...defaultAgentFields }
  selectedJensiAgent.value = null
  selectedDataSource.value = null
}

const closeEditForm = () => {
  showingEditForm.value = false
  selectedAgent.value = null
  selectedJensiAgent.value = null
  selectedDataSource.value = null
}

const selectJensiAgent = (agent) => {
  selectedJensiAgent.value = agent
}

const confirmJensiAgent = () => {
  if (selectedJensiAgent.value) {
    const target = showingCreateForm.value ? agentFields.value : selectedAgent.value
    target.agent_id = selectedJensiAgent.value.id
    showAgentModal.value = false
  }
}

const selectDataSource = (dataSource) => {
  selectedDataSource.value = dataSource
}

const confirmDataSource = () => {
  if (selectedDataSource.value) {
    const target = showingCreateForm.value ? agentFields.value : selectedAgent.value
    target.data_source_id = selectedDataSource.value.id
    showDataSourceModal.value = false
  }
}

const doSave = async () => {
  if (!canSubmit.value || isSubmitting.value) return

  isSubmitting.value = true
  
  try {
    const data = toRaw(agentFields.value)
    
    const response = await axios.post(endpoints.value.agent_crud.crud, data)
    
    if (response.data.success) {
      currentAgents.value.unshift(response.data.data)
      closeCreateForm()
      
      swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Agent created successfully',
        showConfirmButton: false,
        timer: 1500
      })
    } else {
      throw new Error(response.data.data || 'Failed to create agent')
    }
  } catch (err) {
    console.error('Error creating agent:', err)
    const message = err?.response?.data?.data || err.message || 'Failed to create agent'
    swal.fire({
      icon: 'error',
      title: 'Error',
      text: message
    })
  }
  
  isSubmitting.value = false
}

const doUpdate = async (agentId) => {
  if (!canSubmit.value || isSubmitting.value) return

  isSubmitting.value = true
  
  try {
    const data = toRaw(selectedAgent.value)
    
    const response = await axios.post(endpoints.value.agent_crud.crud, data)
    
    if (response.data.success) {
      // Update the agent in the list
      const index = currentAgents.value.findIndex(a => a.id === agentId)
      if (index !== -1) {
        currentAgents.value[index] = response.data.data
      }
      
      closeEditForm()
      
      swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Agent updated successfully',
        showConfirmButton: false,
        timer: 1500
      })
    } else {
      throw new Error(response.data.data || 'Failed to update agent')
    }
  } catch (err) {
    console.error('Error updating agent:', err)
    const message = err?.response?.data?.data || err.message || 'Failed to update agent'
    swal.fire({
      icon: 'error',
      title: 'Error',
      text: message
    })
  }
  
  isSubmitting.value = false
}

const doDestroy = (agentId) => {
  swal.fire({
    icon: 'warning',
    title: 'Delete Agent',
    text: 'Are you sure you want to delete this agent? This action cannot be undone.',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const response = await axios.delete(`${endpoints.value.agent_crud.crud}?id=${agentId}`)
        
        if (response.data.success) {
          currentAgents.value = currentAgents.value.filter(a => a.id !== agentId)
          
          swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Agent deleted successfully',
            showConfirmButton: false,
            timer: 1500
          })
        } else {
          throw new Error(response.data.data || 'Failed to delete agent')
        }
      } catch (err) {
        console.error('Error deleting agent:', err)
        const message = err?.response?.data?.data || err.message || 'Failed to delete agent'
        swal.fire({
          icon: 'error',
          title: 'Error',
          text: message
        })
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

const loadJensiDataSources = async () => {
  try {
    const response = await axios.get(endpoints.value.data_sources.get)
    if (response.data.success) {
      jensiDataSources.value = response.data.data
    }
  } catch (err) {
    console.error('Error loading JENSi data sources:', err)
  }
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

const doLoad = async () => {
  await nextTick()

  if (!win.$jensiAiConfig.nonce) {
    win.$jensiAiConfig.nonce = config.rest.nonce
  }

  endpoints.value = config.rest.endpoints
  currentAgents.value = config.agents || []

  // Load JENSi agents and data sources
  await Promise.all([
    loadJensiAgents(),
    loadJensiDataSources()
  ])

  hasLoaded.value = true
}
</script>
