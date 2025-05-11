import { LitElement, html, css } from 'lit';
import { encryptText } from './process.js';

class MatrixEncrypt extends LitElement {
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
        cols="10"
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

    :host {
      display: flex;
      flex-direction: column;
    }

    textarea {
      width: 30dvw;
      height: 45dvh;
      font-family: monospace;
      padding: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      resize: vertical;
      min-height: 20rem;
      max-height: 50rem;
    }

    label {
      text-align: center;
      font-weight: bold;
    }

    button {
      padding: 0.5rem 1rem;
      cursor: pointer;
    }
  `;
}

customElements.define('matrix-encrypt', MatrixEncrypt);
