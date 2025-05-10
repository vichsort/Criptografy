import { LitElement, html, css } from 'lit';
import { decryptText } from './process.js';

class DecryptForm extends LitElement {
  static properties = {
    encrypted: { type: String },   
    matrix:    { type: Array }
  };

  constructor() {
    super();
    this.encrypted = '';
    this.matrix    = [];
  }

  render() {
    return html`
      <label for="cipher">To decrypt</label>
      <textarea
        id="cipher"
        rows="4"
        cols="30"
        placeholder="Your encrypted text goes here"
        .value=${this.encrypted}
      ></textarea>

      <button 
        ?disabled=${!this.matrix || this.matrix.length === 0}
        @click=${this._decrypt}
      >
        Decrypt
      </button>
    `;
  }

  _decrypt() {
    try {
      const plaintext = decryptText(this.encrypted, this.matrix);

      this.dispatchEvent(new CustomEvent('did-decrypt', {
        detail: { plaintext },
        bubbles: true,
        composed: true
      }));
    } catch (err) {
      console.error('Erro ao descriptografar:', err);
    }
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
    button[disabled] {
      opacity: 0.5;
      cursor: not-allowed;
    }
  `;
}

customElements.define('decrypt-form', DecryptForm);
