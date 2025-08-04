<template>
  <div>
    <breadcrumbs :links="getCrumbs()" />

    <section>
      <div class="">
        <div class="sm:flex sm:items-center py-3">
          <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold leading-6 text-gray-700">
              Sync
            </h1>
          </div>
        </div>
        <div class="mt-8 flow-root">
          <div class="overflow-x-auto">
            <div class="inline-block min-w-full py-2 align-middle">
              <div class="">
                <div class="pb-4">
                  <!--Title-->
                  <h2 class="font-sans break-normal text-gray-600 pb-1 text-lg w-full text-left">
                    Sync
                  </h2>

                  <!--Card-->
                  <div class="p-8 mt-6 lg:mt-0 rounded shadow bg-white">
                    <div class="md:flex">
                      Manually sync your posts, pages and media.
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
import {ref} from 'vue'
import Breadcrumbs from '~src/shared/components/BreadcrumbNavigation.vue'

const getCrumbs = () => {
  return [
    { title: 'Sync', href: null }
  ]
}

</script>

