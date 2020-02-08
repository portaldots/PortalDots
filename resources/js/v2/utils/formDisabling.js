const forms = document.querySelectorAll('form')
const submits = document.querySelectorAll(
  'button[type="submit"], input[type="submit"]'
)

export const registerSubmitHandler = () => {
  const handler = () => {
    /* eslint-disable no-restricted-syntax */
    for (const submit of submits) {
      submit.disabled = true
    }
    /* eslint-enable */
  }
  /* eslint-disable no-restricted-syntax */
  for (const form of forms) {
    form.addEventListener('submit', handler)
  }
  /* eslint- enable */
}

export const reenableSubmit = () => {
  /* eslint-disable no-restricted-syntax */
  for (const submit of submits) {
    submit.disabled = false
  }
  /* eslint-enable */
}
