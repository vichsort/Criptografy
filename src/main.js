// src/main/main.js
import { LitElement, html, css } from 'lit';
import './components/navbar.js';
import './main/encrypt.js';
import './main/decrypt.js';
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
      <nav>
        <navbar-element></navbar-element>
      </nav>

      <main>
        <encrypt-form
          .value=${this.decryptedText}
          @did-encrypt=${this._onEncrypted}
        ></encrypt-form>

        <matrix-display
          .matrix=${this.matrixKey}
          @matrix-updated=${this._onMatrixProvided}
        ></matrix-display>

        <decrypt-form
          .encrypted=${this.encryptedText}
          .matrix=${this.matrixKey}
          @did-decrypt=${this._onDecrypted}
        ></decrypt-form>
      </main>
    `;
  }

  _onEncrypted(e) {
    this.encryptedText = e.detail.cryptotext;
    this.matrixKey     = e.detail.matrix; 
    this.decryptedText = ''; 
  }

  _onMatrixProvided(e) {
    this.matrixKey = e.detail.matrix;
  }

  _onDecrypted(e) {
    this.decryptedText = e.detail.plaintext;
  }

  static styles = css`
    :host {
      width: 100%;
      height: 100%;
    }

    nav {
      position: fixed;
      top: 0;
      width: 100dvw;
    }

    main {
      display: flex;
      gap: 2rem;
      justify-content: space-around;
      align-items: flex-start;
    }
  `;
}

customElements.define('main-element', MainElement);
