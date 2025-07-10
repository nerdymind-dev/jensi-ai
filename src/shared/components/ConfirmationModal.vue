<template>
  <modal :show="show" :max-width="maxWidth" :closeable="closeable"
         @close="close">
    <div class="bg-white  px-4 pt-5 pb-4 sm:p-6 sm:pb-4 rounded-t-lg">
      <div class="sm:flex sm:items-start">
        <template v-if="showIcon">
          <div v-if="customIcon"
               class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10">
            <div
              :class="{'bg-blue-100': type === 'info','bg-red-100': type === 'danger', 'bg-yellow-100': type === 'warning'}"
              class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10">
              <span
                :class="{'text-blue-600': type === 'info','text-red-600': type === 'danger', 'text-yellow-600': type === 'warning'}">
                <slot name="icon"></slot>
              </span>
            </div>
          </div>
          <div v-else
               :class="{'bg-blue-100': type === 'info','bg-red-100': type === 'danger', 'bg-yellow-100': type === 'warning'}"
               class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10">
            <template v-if="type === 'warning' || type === 'danger'">
              <svg
                :class="{'text-red-600': type === 'danger', 'text-yellow-600': type === 'warning'}"
                class="h-6 w-6" stroke="currentColor" fill="none"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </template>
            <template v-else>
              <svg class="h-6 w-6 text-blue-600"
                   xmlns="http://www.w3.org/2000/svg" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </template>
          </div>
        </template>

        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full" :class="{ 'sm:ml-4': showIcon }">
          <h3 class="text-lg text-black">
            <slot name="title"></slot>
          </h3>

          <div class="mt-2 text-gray-900">
            <slot name="content"></slot>
          </div>
        </div>
      </div>
    </div>

    <div
      class="flex items-center justify-end px-6 py-4 bg-gray-100 text-right rounded-b-lg">
      <slot name="footer">
      </slot>
    </div>
  </modal>
</template>

<script>
import Modal from './Modal'

export default {
  emits: ['close'],

  components: {
    Modal,
  },

  props: {
    show: {
      default: false
    },
    type: {
      default: 'danger'
    },
    maxWidth: {
      default: '2xl'
    },
    closeable: {
      default: true
    },
    showIcon: {
      default: true
    },
    customIcon: {
      default: false
    }
  },

  methods: {
    close () {
      this.$emit('close')
    },
  }
}
</script>
