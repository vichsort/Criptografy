import { LitElement, css, html } from 'lit';
import './main/encript.js';
import './main/decript.js';
import './components/navbar.js';


export class MainElement extends LitElement {
  render() {
    return html`
      <navbar>
        <navbar-element></navbar-element>
      </navbar>
      
      <main>
        <encript-form></encript-form>
        <decript-form></decript-form>
      </main>

    `
  }

  static get styles() {
    return css`
      :host {
        display: flex;
        max-width: 1280px;
        margin: 0 auto;
        padding: 2rem;
        text-align: center;
        flex-direction: column;
        gap: 2rem;
        justify-content: center;
        align-items: flex-start;
      }

      main {
        display: flex;
      }

      encript-form, decript-form {
        border: 1px solid #ccc;
        padding: 1rem;
        box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
      }
    `
  }
}

window.customElements.define('main-element', MainElement)
