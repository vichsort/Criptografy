export function processForm(e) {
    e.preventDefault()
    const form = e.target

    const formData = new FormData(form)
    const formValues = Object.fromEntries(formData.entries())

    console.log(formValues)

    const name = formValues.name
    const birthDate = new Date(formValues.birthDate) // ou parseISO se quiser importar
    const subject = formValues.subject
    const acceptRules = Boolean(formValues.acceptRules)

    const finalData = { name, birthDate, subject, acceptRules }

    console.log('Dados processados:', finalData)

    // (Opcional) Dispara um evento customizado
    this.dispatchEvent(new CustomEvent('form-data', {
      detail: finalData,
      bubbles: true,
      composed: true
    }))
}