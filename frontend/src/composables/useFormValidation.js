import { ref, reactive } from 'vue'

export function useFormValidation() {
  const errors = reactive({})

  const validateRequired = (field, value, message = 'Field ini wajib diisi') => {
    if (!value || value.toString().trim() === '') {
      errors[field] = message
      return false
    }
    delete errors[field]
    return true
  }

  const validateMinMax = (field, value, min, max, message) => {
    const numValue = Number(value)
    if (isNaN(numValue) || numValue < min || numValue > max) {
      errors[field] = message || `Nilai harus antara ${min} dan ${max}`
      return false
    }
    delete errors[field]
    return true
  }

  const validatePattern = (field, value, regex, message) => {
    if (!regex.test(value)) {
      errors[field] = message || 'Format tidak valid'
      return false
    }
    delete errors[field]
    return true
  }

  const clearErrors = () => {
    for (const key in errors) {
      delete errors[key]
    }
  }

  const hasErrors = () => {
    return Object.keys(errors).length > 0
  }

  return {
    errors,
    validateRequired,
    validateMinMax,
    validatePattern,
    clearErrors,
    hasErrors
  }
}
