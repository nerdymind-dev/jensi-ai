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
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
            <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
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
                  <t-input v-model="agentFields.name" />
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

              <!-- Widget Settings -->
              <div class="border-t pt-3 mt-3 space-y-3">
                <h3 class="text-md font-medium text-gray-700 mb-3">Widget Settings</h3>
                
                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Enabled
                  </label>
                  <div>
                    <t-toggle v-model="agentFields.enabled" />
                  </div>
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Avatar URL
                  </label>
                  <t-input class="col-span-3" v-model="agentFields.avatar_url" placeholder="https://..." />
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Welcome Message
                  </label>
                  <t-textarea class="col-span-3" v-model="agentFields.welcome_message" />
                </div>

                <!-- Position & Colors -->
                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Bottom Offset (px)
                  </label>
                    <div class="col-span-3">
                        <slider
                            v-model="agentFields.bottom_offset"
                            :min="0"
                            :max="200"
                            class="slider-blue"
                            showTooltip="drag"
                        />
                    </div>
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Right Offset (px)
                  </label>
                  <div class="col-span-3">
                    <slider
                        v-model="agentFields.right_offset"
                        :min="0"
                        :max="200"
                        class="slider-blue"
                        showTooltip="drag"
                    />
                  </div>
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Primary Color
                  </label>
                  <div class="col-span-3">
                  <color-input v-model="agentFields.primary_color" :format="'hex'" class="ring ring-offset-1 rounded-lg ring-gray-200" />
                    </div>  
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Secondary Color
                  </label>
                  <div class="col-span-3">
                  <color-input v-model="agentFields.secondary_color" :format="'hex'" class="ring ring-offset-1 rounded-lg ring-gray-200" />
                  </div>
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Background Color
                  </label>
                  <div class="col-span-3">
                  <color-input v-model="agentFields.background_color" :format="'hex'" class="ring ring-offset-1 rounded-lg ring-gray-200" />
                  </div>
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Text Color
                  </label>
                  <div class="col-span-3">
                  <color-input v-model="agentFields.text_color" :format="'hex'" class="ring ring-offset-1 rounded-lg ring-gray-200" />
                    </div>
                </div>

                <div class="md:grid grid-cols-5">
                  <label class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                    Secondary Text Color
                  </label>
                  <div class="col-span-3">
                  <color-input v-model="agentFields.secondary_text_color" :format="'hex'" class="ring ring-offset-1 rounded-lg ring-gray-200" />
                    </div>
                </div>
              </div>

              <!-- Filtering Settings -->
              <div class="border-t pt-3 mt-3 space-y-3">
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
                        <label
                        class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                        Post Type
                        <sup class="text-red-400">*</sup>
                        </label>
                        <t-rich-select
                        class="col-span-3"
                        v-model="agentFields.post_type"
                        @input="() => agentFields.terms = []"
                        :options="config.postTypes || []"
                        />
                    </div>
                    <div v-if="agentFields.post_type" class="md:grid grid-cols-5">
                        <label
                        class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                        Taxonomy
                        </label>
                        <t-rich-select
                        class="col-span-3"
                        v-model="agentFields.taxonomy"
                        :options="config.postTerms[agentFields.post_type]['_taxonomies'] || []"
                        />
                    </div>
                    <div v-if="agentFields.taxonomy" class="md:grid grid-cols-5">
                        <label
                        class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                        Terms
                        </label>
                        <t-rich-select
                        class="col-span-3"
                        v-model="agentFields.terms"
                        :options="config.postTerms[agentFields.post_type][agentFields.taxonomy] || []"
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
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
            <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
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

              <!-- Widget Settings (simplified for create) -->
              <div class="border-t pt-3 mt-3 space-y-3">
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
                        <label
                        class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                        Post Type
                        <sup class="text-red-400">*</sup>
                        </label>
                        <t-rich-select
                        class="col-span-3"
                        v-model="agentFields.post_type"
                        @input="() => agentFields.terms = []"
                        :options="config.postTypes || []"
                        />
                    </div>
                    <div v-if="agentFields.post_type" class="md:grid grid-cols-5">
                        <label
                        class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                        Taxonomy
                        </label>
                        <t-rich-select
                        class="col-span-3"
                        v-model="agentFields.taxonomy"
                        :options="config.postTerms[agentFields.post_type]['_taxonomies'] || []"
                        />
                    </div>
                    <div v-if="agentFields.taxonomy" class="md:grid grid-cols-5">
                        <label
                        class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                        Terms
                        </label>
                        <t-rich-select
                        class="col-span-3"
                        v-model="agentFields.terms"
                        :options="config.postTerms[agentFields.post_type][agentFields.taxonomy] || []"
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
    </section>
  </div>
</template>

<script setup>
import {computed, inject, nextTick, onBeforeMount, ref, toRaw} from 'vue'
import {TToggle, TButton, TTextarea, TInput, TRichSelect} from '@variantjs/vue'
import Slider from '@vueform/slider'
import ColorInput from 'vue-color-input'
import {
  InformationCircleIcon,
  CheckCircleIcon,
  MinusCircleIcon,
} from '@heroicons/vue/24/outline'

// import Breadcrumbs from '~src/shared/components/BreadcrumbNavigation.vue'
import Modal from '~src/shared/components/ConfirmationModal.vue'

const win = inject('win')
const swal = inject('swal')
const axios = inject('axios')

// Form fields for create/edit
const defaultFields = {
  name: '',
  agent_id: '',
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
  post_type: 'post',
  taxonomy: null,
  terms: [],
  display_everywhere: false
}
const agentFields = ref({ ...defaultFields })

const endpoints = ref({
  agent_crud: {
    all: '', // GET
    crud: '', // GET/POST/DELETE
  },
  agents: { get: '' },
})

const config = inject('pluginConfig')

const currentAgents = ref([])
const jensiAgents = ref([])
const selectedAgent = ref(null)
const selectedJensiAgent = ref(null)

const hasLoaded = ref(false)
const isSubmitting = ref(false)
const showingCreateForm = ref(false)
const showingEditForm = ref(false)
const showAgentModal = ref(false)

const canSubmit = computed(() => {
  const fields = showingCreateForm.value ? agentFields.value : selectedAgent.value
  if (fields?.display_everywhere === false) {
    return fields?.name && fields?.agent_id && fields?.post_type
  }
  return fields?.name && fields?.agent_id
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

const createAgent = () => {
  // Clear out any old data
  agentFields.value = { ...defaultFields }
  selectedJensiAgent.value = null
  // Show create form
  showingCreateForm.value = true
}

const selectAndEditAgent = async (agent) => {
  agentFields.value = { ...agent }

  // Need to convert boolean and array values
  agentFields.value.terms = JSON.parse(agent.terms || "[]")
  agentFields.value.enabled = agent.enabled == 1
  agentFields.value.display_everywhere = agent.display_everywhere == 1
  
  // Find the corresponding JENSi agent and data source
  selectedJensiAgent.value = jensiAgents.value.find(a => a.id === agent.agent_id) || null

  // Update the selected config and show modal
  selectedAgent.value = agent;
  showingEditForm.value = true
}

const closeCreateForm = () => {
  showingCreateForm.value = false
  selectedAgent.value = null
  agentFields.value = { ...defaultFields }
  selectedJensiAgent.value = null
}

const closeEditForm = () => {
  showingEditForm.value = false
  selectedAgent.value = null
  selectedJensiAgent.value = null
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

const doUpdate = async (id) => {
  await doSave(id)
}

const doSave = async (id = null) => {
  if (!canSubmit.value || isSubmitting.value) return
  isSubmitting.value = true
  try {
    const formData = toRaw(agentFields.value)
    if (id) {
      formData.id = id
    }
    if (config.settings.enable_debug_messages) {
      console.log(':: DEBUG ::', formData)
    }
    const rst = await axios.post(endpoints.value.agent_crud.crud, formData)
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
        title: 'Agent updated successfully',
        showConfirmButton: false,
        timer: 1500
      })
      
      // Close any modals
      closeCreateForm()
      closeEditForm()

      // Fetch all items (including new/updated)
      const allItems = await deFetchAll()

      // Update config and local values
      config.agents = allItems || []
      currentAgents.value = allItems || []

      // Reset agent form
      agentFields.value = { ...defaultFields }
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
    const rst = await axios.get(endpoints.value.agent_crud.all)
    if (config.settings.enable_debug_messages) {
      console.log(':: DEBUG - Response ::', rst)
    }
    const { data = null, success = null } = rst?.data
    if (success === false) {
      swal.fire({
        position: 'top-end',
        icon: 'warning',
        title: 'Error fetching agents',
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
            title: 'Agent deleted successfully',
            showConfirmButton: false,
            timer: 1500
          })

          // Fetch all items (including new/updated)
          const allItems = await deFetchAll()

          // Update config and local values
          config.agents = allItems || []
          currentAgents.value = allItems || []

          // Reset agent form
          agentFields.value = { ...defaultFields }
        } else {
          swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'Unable to delete agent, please try again later',
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

const doLoad = async () => {
  await nextTick()

  if (!win.$jensiAiConfig.nonce) {
    win.$jensiAiConfig.nonce = config.rest.nonce
  }

  endpoints.value = config.rest.endpoints
  currentAgents.value = config.agents || []

  // Load JENSi agents (but not data sources - they're loaded on-demand per agent)
  await loadJensiAgents()

  hasLoaded.value = true
}

const getCrumbs = () => {
  return [
  ]
}

</script>
