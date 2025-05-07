export function processForm(e, cript) {
    e.preventDefault()
    const form = e.target
  
    const formData = new FormData(form)
    const formValues = Object.fromEntries(formData.entries())
  
    console.log(formValues)
  
    const name = formValues.name
    const birthDate = new Date(formValues.birthDate)
    const subject = formValues.subject
    const acceptRules = Boolean(formValues.acceptRules)
  
    const finalData = { name, birthDate, subject, acceptRules }

    console.log('Dados processados:', finalData)

    if (cript == true) {
        console.log("estamos encriptando")
    } else {
        console.log("estamos decriptando")
    }

    return finalData
}