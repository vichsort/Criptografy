import { LitElement, css, html } from 'lit';
import './encript.js';
import './decript.js';


export class MainElement extends LitElement {
  render() {
    return html`
      <encript-form></encript-form>
      <decript-form></decript-form>
    `
  }

  static get styles() {
    return css`
      :host {
        max-width: 1280px;
        margin: 0 auto;
        padding: 2rem;
        text-align: center;
        display: flex;
        gap: 2rem;
        justify-content: center;
        align-items: flex-start;
      }

      encript-form, decript-form {
        flex: 1;
        border: 1px solid #ccc;
        padding: 1rem;
        box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
      }
    `
  }
}

window.customElements.define('main-element', MainElement)
