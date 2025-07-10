<template>
  <div class="main-wrapper">
    <vue3-progress-bar></vue3-progress-bar>
    <sidebar-layout :navigation="navigation" :meta="appMeta">
      <router-view :key="$route.path"></router-view>
    </sidebar-layout>
  </div>
</template>

<script setup>
import { inject, onMounted, ref } from 'vue'
import SidebarLayout from '~src/admin/layouts/SidebarLayout.vue'
import menuFix from './admin-menu-fix'
import {
  CogIcon,
  AdjustmentsHorizontalIcon,
  UserGroupIcon,
  InformationCircleIcon,
  QueueListIcon
} from '@heroicons/vue/24/outline'

const pluginConfig = inject('pluginConfig', {})

const navigation = ref([
  { name: 'Configuration', href: '/', icon: AdjustmentsHorizontalIcon },
  { name: 'Personas', href: '/personas', icon: UserGroupIcon },
  { name: 'Queue', href: '/queue', icon: QueueListIcon },
  { name: 'Settings', href: '/settings', icon: CogIcon },
  { name: 'Help', href: '/help', icon: InformationCircleIcon },
])

const appMeta = ref([])

onMounted(() => {
  menuFix(pluginConfig.prefix || 'vue-app')
  appMeta.value.push({
    key: 'Version',
    value: pluginConfig.pluginVersion
  })
})

</script>

<style lang="scss">
$vue3-progress-bar-container-z-index: 20 !default;
$vue3-progress-bar-container-transition: all 500ms ease !default;

$vue3-progress-bar-color: #2563eb !default;
$vue3-progress-bar-height: 4px !default;
$vue3-progress-bar-transition: all 200ms ease !default;

.vue3-progress-bar-container {
  position: fixed;
  z-index: $vue3-progress-bar-container-z-index;
  top: 32px;
  left: 0;
  width: 100%;
  opacity: 0;
  transition: $vue3-progress-bar-container-transition;
  &[active="true"] {
    opacity: 1;
    transition: none;
  }
  .vue3-progress-bar {
    width: 100%;
    height: $vue3-progress-bar-height !important;
    transform: translate3d(-100%, 0, 0);
    background-color: $vue3-progress-bar-color;
    transition: $vue3-progress-bar-transition;
  }
}

@media screen and (max-width: 1024px){
  .vue3-progress-bar-container {
    top: calc(56px + 32px);
  }
}

@media screen and (max-width: 782px){
  .vue3-progress-bar-container {
    top: calc(56px + 46px);
  }
}
</style>

<style>
.overflow-footer {
  height: 100px;
  overflow-y: auto;
}
#wpcontent {
  padding-right: 20px;
}
@media screen and (max-width: 782px){
  #wpcontent {
    padding-right: 10px;
  }
}
.update-nag, .updated, .error, .is-dismissible { display: none; }
</style>
