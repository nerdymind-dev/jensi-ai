<template>
  <div>
    <!-- <breadcrumbs :links="getCrumbs()" /> -->

    <section>
      <div class="">
        <div class="sm:flex sm:items-center py-3">
          <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold leading-6 text-gray-700">
              Help
            </h1>
          </div>
        </div>
        <div class="mt-8 flow-root">
          <div class="overflow-x-auto">
            <div class="inline-block min-w-full py-2 align-middle">
              <div class="">

                <div class="pb-4" >
                  <!--Title-->
                  <h2 class="font-sans break-normal text-gray-600 pb-1 text-lg w-full text-left">
                    JENSi AI
                  </h2>

                  <!--Card-->
                  <div class="p-8 mt-6 lg:mt-0 rounded shadow bg-white">
                    <div class="md:flex">
                      <p>
                        Connect your WordPress site to JENSi AI to process content and provide intelligent suggestions.
                      </p>
                    </div>
                  </div>
                  <!--/Card-->
                </div>
                
                <div class="pb-4" >
                  <!--Title-->
                  <h2 class="font-sans break-normal text-gray-600 pb-1 text-lg w-full text-left">
                    Agents
                  </h2>

                  <!--Card-->
                  <div class="p-8 mt-6 lg:mt-0 rounded shadow bg-white">
                    <div class="">
                      <p class="mb-2">
                        Agents are AI personas used to process your content. You can create multiple agents with different settings to handle different types of content.
                      </p>
                      <t-button variant="primary" class="inline-block" :href="config.rest.api_url" target="_blank">
                        <span class="text-white">Manage Your Agents</span>
                      </t-button>
                    </div>
                  </div>
                  <!--/Card-->
                </div>

                <div class="pb-4" >
                  <!--Title-->
                  <h2 class="font-sans break-normal text-gray-600 pb-1 text-lg w-full text-left">
                    Data Sources
                  </h2>

                  <!--Card-->
                  <div class="p-8 mt-6 lg:mt-0 rounded shadow bg-white">
                    <div class="">
                      <p class="mb-2">
                        Data Sources are content repositories that your agents can access to enhance their understanding and responses. You can connect multiple data sources to provide a richer context for your AI agents.
                        This WordPress site is an example of a data source.
                      </p>
                      <t-button variant="primary" class="inline-block" :href="config.rest.api_url" target="_blank">
                        <span class="text-white">Manage Your Data Sources</span>
                      </t-button>
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
import {inject} from 'vue'
import Breadcrumbs from '~src/shared/components/BreadcrumbNavigation.vue'
import {TButton} from '@variantjs/vue'

const config = inject('pluginConfig')

const getCrumbs = () => {
  return [
    { title: 'Help', href: null }
  ]
}
</script>

