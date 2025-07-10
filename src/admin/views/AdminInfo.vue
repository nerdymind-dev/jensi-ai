<template>
  <div>
    <breadcrumbs :links="getCrumbs()" />

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
                <div
                  v-for="(value) in infoSections"
                  :key="`container.${value.id}`"
                  class="pb-4" >
                  <!--Title-->
                  <h2 class="font-sans break-normal text-gray-600 pb-1 text-lg w-full text-left">
                    {{ value.title }}
                  </h2>

                  <!--Card-->
                  <div class="p-8 mt-6 lg:mt-0 rounded shadow bg-white">
                    <div class="md:flex">
                      <span v-html="value.details"/>
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

const infoSections = ref([
{
    id: 'jensi_ai',
    title: 'JENSi AI',
    details: `
      <p class="mb-2">
        Connect your WordPress site to JENSi AI to process content and provide intelligent suggestions.
      </p>
    `
  }
  // {
  //   id: 'ai_personas',
  //   title: 'Configurations & Personas',
  //   details: `
  //     <p class="mb-2">
  //       Configurations are used to define which personas will be used for the selected category/categories. When enabled, they will be triggered automatically on post create/update, if not previously run. This can also be triggered manually at any point within the post edit page.
  //     </p>
  //     <p class="mb-2">
  //       Personas are used to direct the AIs response. E.g. "<i>You are an informed, knowledgeable, social media marketer. Create a social media post that summarizes the users content.</i>"
  //     </p>
  //     <p>
  //       Personas are attached to configuration to determine how content is generated.
  //     </p>
  //   `
  // },
  // {
  //   id: 'ai_tokens',
  //   title: 'Tokens',
  //   details: `
  //     <p class="mb-2">
  //       Tokens are a key part of interacting with the AI.
  //     </p>
  //     <p>
  //       You can find out more about tokens here: <a class="text-blue-500 hover:text-blue-600" href="https://help.openai.com/en/articles/4936856-what-are-tokens-and-how-to-count-them" target="_blank">https://help.openai.com/en/articles/4936856-what-are-tokens-and-how-to-count-them</a>
  //     </p>
  //   `
  // },
  // {
  //   id: 'ai_response',
  //   title: 'Incomplete response?',
  //   details: `
  //     <p class="mb-2">
  //       Not getting full responses? Try setting the token size on the persona.
  //     </p>
  //     <p class="mb-2">
  //       This is especially true when using the "completions" endpoint. Settings completions to 2048 or lower should be a good starting point.
  //       Currently, the maximum token size is 2048 or 4096 (depending on the model) between the request and the response, so be sure to leave room for the initial request.
  //     </p>
  //     <p>
  //       See here for more information: <a class="text-blue-500 hover:text-blue-600" href="https://help.openai.com/en/articles/5072518-controlling-the-length-of-completions" target="_blank">https://help.openai.com/en/articles/5072518-controlling-the-length-of-completions</a>
  //     </p>
  //   `
  // }
])

const getCrumbs = () => {
  return [
    { title: 'Help', href: null }
  ]
}

</script>

