// src/main/main.js
import { LitElement, html, css } from 'lit';
import './main/encript.js';
import './main/decript.js';
import './main/matrix.js';

class MainElement extends LitElement {
  static properties = {
    encryptedText: { type: String },
    matrixKey:     { type: Array  },
    decryptedText: { type: String }
  };

  constructor() {
    super();
    this.encryptedText = '';
    this.matrixKey     = [];
    this.decryptedText = '';
  }

  render() {
    return html`
      <div class="layout">
        <!-- repassamos decryptedText como 'value' em encript-form -->
        <encript-form
          .value=${this.decryptedText}
          @did-encrypt=${this._onEncrypted}
        ></encript-form>

        <matrix-display
          .matrix=${this.matrixKey}
          @matrix-updated=${this._onMatrixProvided}
        ></matrix-display>

        <decript-form
          .encrypted=${this.encryptedText}
          .matrix=${this.matrixKey}
          @did-decrypt=${this._onDecrypted}
        ></decript-form>
      </div>
    `;
  }

  _onEncrypted(e) {
    this.encryptedText = e.detail.cryptotext;
    this.matrixKey     = e.detail.matrix; 
    this.decryptedText = '';  // limpa resultado anterior
  }

  _onMatrixProvided(e) {
    this.matrixKey = e.detail.matrix;
  }

  _onDecrypted(e) {
    this.decryptedText = e.detail.plaintext;
  }

  static styles = css`
    .layout {
      display: flex;
      gap: 2rem;
      justify-content: space-around;
      align-items: flex-start;
    }
  `;
}

customElements.define('main-element', MainElement);
