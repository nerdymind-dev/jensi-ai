<template>
  <div v-if="loading" :class="sizeClass" class="relative mx-auto text-primary-600">
    <svg class="circular absolute top-0 right-0" viewBox="25 25 50 50">
      <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3"
              stroke-miterlimit="10"
      />
    </svg>
  </div>
</template>
<script>
export default {
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    size: {
      types: Number,
      default: 8
    }
  },

  computed: {
    sizeClass: function () {
      return `w-${this.size} h-${this.size}`
    }
  }
}
</script>

<style lang="scss">
$pageloader-opacity: 0.4 !default;

.pageloader {
  display: flex;
  justify-content: center;
  align-items: center;
  align-content: center;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: #4B5563;
  background-color: rgba(255, 255, 255, $pageloader-opacity);
  z-index: 99;
  opacity: 1;
  color: #fff;

}

.pageloader.absolute {
  position: absolute;
}

$loaderColor1: #2563EB;
//$loaderColor2: #4F46E5;
//$loaderColor3: #DC2626;
//$loaderColor4: #059669;
$loaderColor2: #2563EB;
$loaderColor3: #2563EB;
$loaderColor4: #2563EB;

// scaling... any units
$width: 100px;
$widthSm: 60px;
$widthLg: 200px;

.loader {
  width: $width;

  &.loader-sm {
    width: $widthSm;
  }
  &.loader-lg {
    width: $widthLg;
  }

  &:before {
    content: '';
    display: block;
    padding-top: 100%;
  }
}

.circular {
  animation: rotate 2s linear infinite;
  height: 100%;
  transform-origin: center center;
  width: 100%;
  margin: auto;
  stroke: currentColor;
}

.path {
  stroke-dasharray: 1, 200;
  stroke-dashoffset: 0;
  animation: dash 1.5s ease-in-out infinite, color 6s ease-in-out infinite;
  stroke-linecap: round;
}

@keyframes rotate {
  100% {
    transform: rotate(360deg);
  }
}

@keyframes dash {
  0% {
    stroke-dasharray: 1, 200;
    stroke-dashoffset: 0;
  }
  50% {
    stroke-dasharray: 89, 200;
    stroke-dashoffset: -35px;
  }
  100% {
    stroke-dasharray: 89, 200;
    stroke-dashoffset: -124px;
  }
}

@keyframes color {
  100%,
  0% {
    stroke: $loaderColor3;
  }
  40% {
    stroke: $loaderColor2;
  }
  66% {
    stroke: $loaderColor1;
  }
  80%,
  90% {
    stroke: $loaderColor4;
  }
}
</style>
