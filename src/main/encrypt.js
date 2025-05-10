import { LitElement, html, css } from 'lit';
import { encryptText } from './process.js';

class EncryptForm extends LitElement {
  static properties = {
    value: { type: String } 
  };

  constructor() {
    super();
    this.value = ''; 
  }

  render() {
    return html`
      <label for="input">To encrypt</label>
      <textarea
        id="input"
        rows="4" 
        cols="30"
        placeholder="Your unecrypted text goes here"
        .value=${this.value}
      ></textarea>
      <button @click=${this._encrypt}>Encrypt</button>
    `;
  }

  _encrypt() {
    const text = this.renderRoot.getElementById('input').value;
    const { cryptotext, matrix } = encryptText(text);
    this.renderRoot.getElementById('input').value = '';

    this.dispatchEvent(new CustomEvent('did-encrypt', {
      detail: { cryptotext, matrix },
      bubbles: true,
      composed: true
    }));
  }

  static styles = css`
    textarea {
      width: 100%;
      font-family: monospace;
      padding: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      resize: vertical;
      min-height: 5rem;
      max-height: 20rem;
    }
    label {
      font-weight: bold;
    }
    button {
      padding: 0.5rem 1rem;
      cursor: pointer;
    }
  `;
}

customElements.define('encrypt-form', EncryptForm);
