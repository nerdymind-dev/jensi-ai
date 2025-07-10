<template>
  <div>
    <TransitionRoot as="template" :show="sidebarOpen">
      <Dialog as="div" class="relative z-50 lg:hidden" @close="sidebarOpen = false">
        <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="transition-opacity ease-linear duration-300" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-900/80" />
        </TransitionChild>

        <div id="mobilesidebar" class="fixed inset-0 flex">
          <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="-translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0" leave-to="-translate-x-full">
            <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
              <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in-out duration-300" leave-from="opacity-100" leave-to="opacity-0">
                <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                  <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>
              <!-- Sidebar component, swap this element with another sidebar if you like -->
              <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-2">
                <div class="my-3 text-gray-900 shrink-0 items-center">
                  <p class="text-lg font-medium">JENSi AI</p>
                  <small>for WordPress</small>
                </div>
                <nav class="flex flex-1 flex-col">
                  <ul role="list" class="flex flex-1 flex-col gap-y-7">
                    <li>
                      <ul role="list" class="-mx-2 space-y-1">
                        <li v-for="item in navigation" :key="item.name">
                          <router-link
                            v-slot="{ isActive, isExactActive }"
                            :to="item.href"
                            class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold hover:bg-gray-100"
                            @click.native="sidebarOpen = false"
                          >
                            <component :is="item.icon" :class="[isActive ? 'text-blue-600' : 'text-gray-900 group-hover:text-blue-600', 'h-6 w-6 shrink-0']" aria-hidden="true" />
                            <span :class="[isActive ? 'text-blue-600' : 'text-gray-900 group-hover:text-blue-600']">
                              {{ item.name }}
                            </span>
                          </router-link>
                        </li>
                      </ul>
                    </li>
                  </ul>
                </nav>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Static sidebar for desktop -->
    <div id="sidebar" class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-52 lg:flex-col shadow-xl">
      <!-- Sidebar component, swap this element with another sidebar if you like -->
      <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6">
        <div class="my-3 text-gray-900 shrink-0 items-center">
          <p class="text-lg font-medium">JENSi AI</p>
          <small>for WordPress</small>
        </div>
        <nav class="flex flex-1 flex-col">
          <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
              <ul role="list" class="-mx-2 space-y-1">
                <li v-for="item in navigation" :key="item.name">
                  <router-link
                    v-slot="{ isActive, isExactActive }"
                    :to="item.href"
                    class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold hover:bg-gray-100"
                    @click.native="sidebarOpen = false"
                  >
                    <component :is="item.icon" :class="[isActive ? 'text-blue-600' : 'text-gray-900 group-hover:text-blue-600', 'h-6 w-6 shrink-0']" aria-hidden="true" />
                    <span :class="[isActive ? 'text-blue-600' : 'text-gray-900 group-hover:text-blue-600']">
                      {{ item.name }}
                    </span>
                  </router-link>
                </li>
              </ul>
            </li>
          </ul>
        </nav>
        <div v-if="meta.length" class="py-3">
          <div v-for="item in meta" class="text-xs text-gray-600 flex items-center justify-start gap-2">
            <p class="font-bold">
              {{ item.key }}
            </p>
            <p class="">
              {{ item.value }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div
      id="topnav"
      :class="{ 'open': sidebarOpen }"
      class="md:sticky z-40 flex items-center gap-x-6 bg-white px-4 py-4 shadow-sm sm:px-6 lg:hidden"
    >
      <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
        <span class="sr-only">Open sidebar</span>
        <Bars3Icon class="h-6 w-6" aria-hidden="true" />
      </button>
    </div>

    <main class="py-8 lg:pl-52">
      <div class="">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import {ref} from 'vue'
import {Dialog, DialogPanel, TransitionChild, TransitionRoot} from '@headlessui/vue'
import {
  Bars3Icon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  navigation: {
    type: Array,
    required: true
  },
  meta: {
    type: [Array, null],
    required: false,
    default: null
  }
})

const sidebarOpen = ref(false)
</script>

<style >
#sidebar .router-link-active,
#mobilesidebar .router-link-active {
  @apply bg-gray-100 hover:text-blue-600
}

#sidebar, #mobilesidebar {
  left: 160px;
  top: 32px;
}
#topnav {
  top: 32px;
  margin-left: -20px;
  margin-right: -20px;
}

body.folded {
  #sidebar, #mobilesidebar {
    left: 36px;
  }
}


@media screen and (max-width: 960px) {
  body.folded, body.auto-fold {
    #sidebar, #mobilesidebar {
      left: 36px;
      top: 32px;
    }
  }
}

@media screen and (max-width: 782px) {
  #sidebar, #mobilesidebar {
    left: 0 !important;
    top: 46px !important;
  }
  #topnav {
    top: 46px;
    margin-left: -10px;
    margin-right: -10px;
  }
}
</style>

<style>
html, body {
  @apply h-full;
}

#wpfooter {
  left: 210px;
}

@media screen and (max-width: 1024px){
  #wpfooter {
    left: 0;
  }
}
</style>
