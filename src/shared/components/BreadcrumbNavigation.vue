<template>
  <nav id="breadcrumbs" class="flex w-100 mb-4" aria-label="Breadcrumb">
    <ol class="bg-white  rounded-md shadow-sm px-6 flex flex-1 space-x-2">
      <li class="flex mb-0">
        <div class="flex items-center">
          <router-link :to="homeRoute ? homeRoute : '/'" class="text-gray-400 hover:text-gray-500">
            <svg class="flex-shrink-0 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            <span class="sr-only">Dashboard</span>
          </router-link>
        </div>
      </li>
      <li v-if="links.length === 0" class="flex mb-0">
        <div class="flex items-center">
          <svg class="flex-shrink-0 w-6 h-full text-gray-200" viewBox="0 0 24 44" preserveAspectRatio="none" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M.293 0l22 22-22 22h1.414l22-22-22-22H.293z" />
          </svg>
        </div>
      </li>
      <li v-for="link in links" :key="link.title" class="flex mb-0">
        <div class="flex items-center max-w-xs">
          <svg class="flex-shrink-0 w-6 h-full text-gray-200 " viewBox="0 0 24 44" preserveAspectRatio="none" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M.293 0l22 22-22 22h1.414l22-22-22-22H.293z" />
          </svg>
          <template v-if="link.href">
            <router-link :to="link.href" class="ml-4 text-sm truncate text-gray-500 hover:text-gray-700">
              {{ link.title }}
            </router-link>
          </template>
          <template v-else>
            <span class="ml-4 text-sm truncate text-gray-400">
              {{ link.title }}
            </span>
          </template>
        </div>
      </li>
    </ol>
  </nav>
</template>

<script setup>
defineProps({
  links: {
    type: Array,
    default: () => []
  },
  homeRoute: {
    type: String,
    default: null
  }
})
</script>

<style>
#breadcrumbs .router-link-active {

}
</style>
