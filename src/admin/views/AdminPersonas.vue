<template>
  <div>
    <breadcrumbs :links="getCrumbs()" />

    <section>
      <div class="">
        <div class="sm:flex sm:items-center py-3">
          <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold leading-6 text-gray-700">
              Personas
            </h1>
          </div>
          <t-button variant="secondary" @click="() => createPersona()">
            Create New Persona
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
                    AI personas
                  </h2>

                  <div class="my-4 border-l-4 border-blue-400 bg-blue-50 p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <InformationCircleIcon class="h-5 w-5 text-blue-400" aria-hidden="true" />
                      </div>
                      <div class="ml-3">
                        <p class="text-sm text-blue-700">
                          Personas help guide the AI response and fine-tune your results. Personas can be added to a config to automate content generation.
                        </p>
                      </div>
                    </div>
                  </div>

                  <template v-if="currentPersonas.length">
                    <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                      <li v-for="(persona, idx) in currentPersonas" :key="`person.${idx}`"
                          class="col-span-1 flex flex-col justify-between divide-y divide-gray-200 rounded-lg bg-white shadow">
                        <div class="flex w-full items-center justify-between space-x-6 p-6">
                          <div class="flex-1 truncate">
                            <div class="flex items-center space-x-3">
                              <h3 class="truncate text-sm font-medium text-gray-900">
                                {{ persona.title }}
                              </h3>
                            </div>
                            <p class="mt-1 truncate text-sm text-gray-500">{{ persona.details }}</p>
                          </div>
                        </div>
                        <div>
                          <div class="-mt-px flex divide-x divide-gray-200">
                            <div class="flex w-0 flex-1">
                              <button @click="(e) => selectAndEditPersona(persona)" type="button" class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-bl-lg border border-transparent py-4 text-sm font-semibold text-gray-900">
                                Edit
                              </button>
                            </div>
                            <div class="-ml-px flex w-0 flex-1">
                              <button @click="(e) => doDestroy(persona.id)" type="button" class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold text-gray-900">
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
                          No personas currently created, create one to get started!
                        </p>
                        <t-button @click="() => createPersona()">
                          Create New Persona
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
          Edit persona
        </template>
        <template v-if="selectedPersona" #content>
          <div class="w-full">
            <div class="flex flex-col gap-3">
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Title
                  <sup class="text-red-400">*</sup>
                </label>
                <t-input class="col-span-3"
                         v-model="personaFields.title" />
              </div>
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Max Tokens
                </label>
                <div class="col-span-3">
                  <t-input type="number" min="0" steps="1"
                           v-model="personaFields.max_tokens" />
                  <small class="text-gray-400">
                    If left blank, no token limit will be applied.
                    <a class="text-blue-500 hover:text-blue-600" href="https://help.openai.com/en/articles/4936856-what-are-tokens-and-how-to-count-them" target="_blank">
                      Learn more about tokens
                    </a> and <a class="text-blue-500 hover:text-blue-600" href="https://help.openai.com/en/articles/5072518-controlling-the-length-of-completions" target="_blank">completion length</a>.
                  </small>
                </div>
              </div>
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Details
                  <sup class="text-red-400">*</sup>
                </label>
                <t-textarea class="col-span-3" rows="5" v-model="personaFields.details" />
              </div>
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Model
                  <sup class="text-red-400">*</sup>
                </label>
                <t-select
                  class="col-span-3"
                  v-model="personaFields.model"
                  :options="models"
                />
              </div>
              <!-- <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Endpoint
                  <sup class="text-red-400">*</sup>
                </label>
                <t-select
                  class="col-span-3"
                  v-model="personaFields.type"
                  :options="apiEndpoints"
                  valueAttribute="id"
                  textAttribute="title"
                />
              </div> -->
            </div>
          </div>
        </template>
        <template v-if="selectedPersona" #footer>
          <t-button variant="secondary" @click.native="closeEditForm">
            Nevermind
          </t-button>
          <t-button
            class="ml-2" @click.native="(e) => doUpdate(selectedPersona.id)"
            :class="{ 'opacity-25': !canSubmit || isSubmitting }"
            :disabled="!canSubmit || isSubmitting">
            Update Persona
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
            <path d="M11 5a3 3 0 11-6 0 3 3 0 016 0zM2.615 16.428a1.224 1.224 0 01-.569-1.175 6.002 6.002 0 0111.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 018 18a9.953 9.953 0 01-5.385-1.572zM16.25 5.75a.75.75 0 00-1.5 0v2h-2a.75.75 0 000 1.5h2v2a.75.75 0 001.5 0v-2h2a.75.75 0 000-1.5h-2v-2z" />
          </svg>
        </template>
        >
        <template #title>
          Create persona
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
                         v-model="personaFields.title" />
              </div>
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Max Tokens Returned
                </label>
                <div class="col-span-3">
                  <t-input type="number" min="0" steps="1"
                           v-model="personaFields.max_tokens" />
                  <small class="text-gray-400">
                    If left blank, no token limit will be applied.
                    <a class="text-blue-500 hover:text-blue-600" href="https://help.openai.com/en/articles/4936856-what-are-tokens-and-how-to-count-them" target="_blank">
                      Learn more about tokens
                    </a> and <a class="text-blue-500 hover:text-blue-600" href="https://help.openai.com/en/articles/5072518-controlling-the-length-of-completions" target="_blank">completion length</a>.
                  </small>
                </div>
              </div>
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Details
                  <sup class="text-red-400">*</sup>
                </label>
                <t-textarea class="col-span-3" rows="5" v-model="personaFields.details" />
              </div>
              <div class="md:grid grid-cols-5 w-full">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Model
                  <sup class="text-red-400">*</sup>
                </label>
                <t-select
                  class="col-span-3"
                  v-model="personaFields.model"
                  :options="models"
                />
              </div>
              <div class="md:grid grid-cols-5">
                <label
                  class="col-span-2 block text-gray-600 font-bold md:text-left mb-3 md:mb-0 pr-4">
                  Endpoint
                  <sup class="text-red-400">*</sup>
                </label>
                <t-select
                  class="col-span-3"
                  v-model="personaFields.type"
                  :options="apiEndpoints"
                  valueAttribute="id"
                  textAttribute="title"
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
            :class="{ 'opacity-25': !canSubmit || isSubmitting }"
            :disabled="!canSubmit || isSubmitting">
            Create Persona
          </t-button>
        </template>
      </modal>
    </section>
  </div>
</template>

<script setup>
import {computed, inject, nextTick, onBeforeMount, ref, toRaw} from 'vue'
import {TToggle, TButton, TInputGroup, TTextarea, TInput, TSelect} from '@variantjs/vue'
import {
  InformationCircleIcon
} from '@heroicons/vue/24/outline'

import Breadcrumbs from '~src/shared/components/BreadcrumbNavigation.vue'
import Modal from '~src/shared/components/ConfirmationModal.vue'

const win = inject('win')
const swal = inject('swal')
const axios = inject('axios')

const currentPersonas = ref([])
const showingEditForm = ref(false)
const showingCreateForm = ref(false)
const selectedPersona = ref(null)
const hasLoaded = ref(false)
const isSubmitting = ref(false)

const defaultFields = {
  title: '',
  details: '',
  model: '',
  max_tokens: null,
  type: "chat", // Default to caht
  enabled: true,
}
const personaFields = ref({ ...defaultFields })

const endpoints = ref({
  personas: {
    all: '', // GET
    crud: '', // GET/POST/DELETE
  }
})
const config = inject('pluginConfig')
const apiEndpoints = computed(() => config.endpoints || [])

const models = computed(() => {
  return config?.settingStructure?.options['open_ai_model'].options || []
})

const canSubmit = computed(() => {
  if (isSubmitting.value) {
    return false
  }
  // if (!personaFields.value.type || personaFields.value.type.length === 0) {
  //   return false
  // }
  if (!personaFields.value.title || personaFields.value.title.length === 0) {
    return false
  }
  if (!personaFields.value.details || personaFields.value.details.length === 0) {
    return false
  }
  if (!personaFields.value.model || personaFields.value.model.length === 0) {
    return false
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

const createPersona = () => {
  // Clear out any old data
  personaFields.value = { ...defaultFields }
  selectedPersona.value = null
  // Show create form
  showingCreateForm.value = true
}

const closeCreateForm = () => {
  // Reset form and values on close
  showingCreateForm.value = false
  personaFields.value = { ...defaultFields }
}

const selectAndEditPersona = (persona) => {
  // Flag the selected persona and show update form
  personaFields.value.title = persona.title
  personaFields.value.details = persona.details
  personaFields.value.model = persona.model
  personaFields.value.type = persona.type
  personaFields.value.enabled = persona.enabled
  personaFields.value.max_tokens = persona.max_tokens
  selectedPersona.value = persona
  showingEditForm.value = true
}

const closeEditForm = () => {
  showingEditForm.value = false
  selectedPersona.value = null
  personaFields.value = { ...defaultFields }
}

const doUpdate = async (id) => {
  await doSave(id)
}

const doSave = async (id = null) => {
  isSubmitting.value = true
  try {
    let formData = toRaw(personaFields.value)
    if (id) {
      formData.id = id
    }
    if (config.settings.enable_debug_messages) {
      console.log(':: DEBUG ::', formData)
    }
    const rst = await axios.post(endpoints.value.personas.crud, formData)
    if (config.settings.enable_debug_messages) {
      console.log(':: DEBUG - Response ::', rst)
    }
    const { data = null, success = null } = rst?.data
    if (success === false) {
      swal.fire({
        position: 'top-end',
        icon: 'warning',
        title: 'Error updating persona',
        showConfirmButton: false,
        timer: 1500
      })
    } else if (data && rst.status === 200) {
      swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Persona updated successfully',
        showConfirmButton: false,
        timer: 1500
      })
      // Close any modals
      closeCreateForm()
      closeEditForm()

      // Fetch all items (including new/updated)
      const allItems = await deFetchAll()

      // Update config and local values
      config.personas = allItems || []
      currentPersonas.value = allItems || []

      // Reset persona form
      personaFields.value = { ...defaultFields }
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
    const rst = await axios.get(endpoints.value.personas.all)
    if (config.settings.enable_debug_messages) {
      console.log(':: DEBUG - Response ::', rst)
    }
    const { data = null, success = null } = rst?.data
    if (success === false) {
      swal.fire({
        position: 'top-end',
        icon: 'warning',
        title: 'Error fetching personas',
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
    text: 'Delete item now? This action cannot be undone. Any configurations that use this persona will also be deleted.',
    showCancelButton: true,
    confirmButtonText: 'Delete',
  }).then(async (result) => {
    if (result.isConfirmed) {
      isSubmitting.value = true
      try {
        if (config.settings.enable_debug_messages) {
          console.log(':: DEBUG :: deleting persona', id)
        }
        const rst = await axios.delete(endpoints.value.personas.crud, { data: { id } })
        if (config.settings.enable_debug_messages) {
          console.log(':: DEBUG - Response ::', rst)
        }
        const { success = null } = rst?.data
        if (success === true && rst.status === 200) {
          swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Persona deleted successfully',
            showConfirmButton: false,
            timer: 1500
          })
          // Fetch all items (including new/updated)
          const allItems = await deFetchAll()

          // Update config and local values
          config.personas = allItems || []
          currentPersonas.value = allItems || []

          // Reset persona form
          personaFields.value = { ...defaultFields }
        } else {
          swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'Unable to delete persona, please try again later',
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
  currentPersonas.value = config.personas || []

  // make sure data is loaded before ui render
  hasLoaded.value = true
}

const getCrumbs = () => {
  return [
    { title: 'Personas', href: null }
  ]
}

</script>

