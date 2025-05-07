import { LitElement, html } from 'lit'

class ContactForm extends LitElement {
    formSubmit(e) {
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
  
    render() {
      return html`
        <form @submit=${this.formSubmit}>
          <label for="name">Nome:</label>
          <input type="text" id="name" name="name" required />
  
          <label for="birthDate">Data de nascimento:</label>
          <input type="date" id="birthDate" name="birthDate" required />
  
          <label for="subject">Assunto:</label>
          <select id="subject" name="subject" required>
            <option value="">-- Select One --</option>
            <option value="Reclamação">Reclamação</option>
            <option value="Elogio">Elogio</option>
          </select>
  
          <label for="acceptRules">Aceito as condições:</label>
          <input type="checkbox" id="acceptRules" name="acceptRules" />
  
          <input type="submit" />
        </form>
      `
    }
}
  
window.customElements.define('form-test', ContactForm)
