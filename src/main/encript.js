import { LitElement, html, css } from 'lit';
import { encryptText } from './process.js';

class EncriptForm extends LitElement {
  static properties = {
    value: { type: String } 
  };

  constructor() {
    super();
    this.value = ''; 
  }

  render() {
    return html`
      <textarea
        id="input"
        rows="4" cols="30"
        placeholder="Texto a criptografar ou resultado da descriptografia"
        .value=${this.value}
      ></textarea>
      <button @click=${this._encrypt}>Encrypt</button>
    `;
  }

  _encrypt() {
    const text = this.renderRoot.getElementById('input').value;
    const { cryptotext, matrix } = encryptText(text);

    this.dispatchEvent(new CustomEvent('did-encrypt', {
      detail: { cryptotext, matrix },
      bubbles: true,
      composed: true
    }));
  }
}

customElements.define('encript-form', EncriptForm);
