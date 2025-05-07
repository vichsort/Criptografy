import { LitElement, html } from 'lit'
import { processForm } from './process.js' // importa sua função

class ContactForm extends LitElement {
    formSubmit(e) {
        const finalData = processForm(e, true)
        this.result = finalData // trata o resultado no componente
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