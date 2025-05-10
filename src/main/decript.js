import { LitElement, html, css } from 'lit';
import { decryptText } from './process.js';

class DecriptForm extends LitElement {
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
      <textarea
        id="cipher"
        rows="4"
        cols="30"
        placeholder="Texto criptografado"
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
      display: block;
      margin-bottom: 1rem;
      width: 100%;
      font-family: monospace;
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

customElements.define('decript-form', DecriptForm);
