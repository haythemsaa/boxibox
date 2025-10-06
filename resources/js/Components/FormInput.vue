<template>
  <div class="mb-3">
    <label v-if="label" :for="id" class="form-label">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
    </label>
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur')"
      class="form-control"
      :class="{
        'is-invalid': error || externalError,
        'is-valid': !error && !externalError && touched && modelValue
      }"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :readonly="readonly"
      :maxlength="maxlength"
    />
    <div v-if="error" class="invalid-feedback">
      {{ error }}
    </div>
    <div v-else-if="externalError" class="invalid-feedback">
      {{ externalError }}
    </div>
    <small v-if="helpText && !error && !externalError" class="form-text text-muted">
      {{ helpText }}
    </small>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  maxlength: {
    type: [String, Number],
    default: null
  },
  error: {
    type: String,
    default: ''
  },
  externalError: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  touched: {
    type: Boolean,
    default: false
  }
});

defineEmits(['update:modelValue', 'blur']);
</script>
