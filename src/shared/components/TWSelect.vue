<template>
  <select class="nightwind-prevent text-gray-900 block w-full mt-1 border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 rounded-md shadow-sm" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" ref="input">
    <slot></slot>
    <option v-if="placeholder" value="" :selected="!modelValue">
      {{ placeholder }}
    </option>
    <option
      v-for="option in options"
      v-if="options"
      :value="option"
      :disabled="isDisabled(option)"
      :selected="option === modelValue">
      {{ getValue(option) }}
    </option>
  </select>
</template>

<script>
export default {
  props: ['modelValue', 'options', 'disabledValues', 'placeholder', 'ignoredValues', 'translation'],

  emits: ['update:modelValue'],

  methods: {
    focus() {
      this.$refs.input.focus()
    },
    getValue(option) {
      const translation = this.translation || {}
      return translation[option] || option
    },
    isDisabled(option) {
      // Ignore custom fields and ignored fields
      const ignore = Array.isArray(this.ignoredValues) ? this.ignoredValues : []
      if (ignore.includes(option)) {
        return false
      }
      // If no disabled values passed in, no further checks needed
      if (!Array.isArray(this.disabledValues)) {
        return false
      }
      // Check if value has been used
      return this.disabledValues.findIndex(c => c === option) !== -1
    }
  }
}
</script>
