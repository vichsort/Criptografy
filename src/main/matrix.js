// src/main/matrix.js
import { LitElement, html, css } from 'lit';

class MatrixDisplay extends LitElement {
  static properties = {
    matrix: { type: Array },    // recebe a matriz principal
    rawInput: { type: String }  // string para edição manual
  };

  constructor() {
    super();
    this.matrix = [];
    this.rawInput = '';
  }

  updated(changed) {
    // Atualiza rawInput apenas se for diferente
    if (changed.has('matrix')) {
      const newRaw = this.matrixToString(this.matrix);
      if (newRaw !== this.rawInput) {
        this.rawInput = newRaw;
      }
    }
  }

  render() {
    return html`
      <div class="matrix-container">
        <label for="matrix-input">Chave de Matriz (edite livremente):</label>
        <textarea
          id="matrix-input"
          rows="6"
          .value=${this.rawInput}
          @input=${this._onRawInput}
        ></textarea>

        <div class="preview">
          ${this.matrix && this.matrix.length
            ? this.matrix.map(
                (m, i) => html`
                  <div class="matrix-block">
                    <div class="row">${m[0].join(', ')}</div>
                    <div class="row">${m[1].join(', ')}</div>
                  </div>
                `
              )
            : html`<div class="empty">Nenhuma chave disponível</div>`}
        </div>
      </div>
    `;
  }

  // Converte array de matrizes para string editável
  matrixToString(matrixArr) {
    return matrixArr
      .map(m => `${m[0].join(',')}\n${m[1].join(',')}`)
      .join('\n---\n');
  }

  // Trata entrada manual, parse e dispara evento
  _onRawInput(e) {
    const text = e.target.value;
    this.rawInput = text;
    try {
      const blocks = text.split(/---/g).map(b => b.trim()).filter(b => b);
      const parsed = blocks.map(block => {
        const lines = block.split(/\n+/).map(l => l.trim()).filter(l => l);
        if (lines.length !== 2) throw new Error('Formato inválido');
        const row0 = lines[0].split(',').map(n => parseFloat(n.trim()));
        const row1 = lines[1].split(',').map(n => parseFloat(n.trim()));
        return [row0, row1];
      });
      this.dispatchEvent(new CustomEvent('matrix-updated', {
        detail: { matrix: parsed },
        bubbles: true,
        composed: true
      }));
    } catch (err) {
      // parsing falhou, não dispara evento
      console.warn('MatrixDisplay: erro ao parsear entrada', err);
    }
  }

  static styles = css`
    .matrix-container {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      width: 100%;
    }
    label {
      font-weight: bold;
    }
    textarea {
      width: 100%;
      font-family: monospace;
      padding: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      resize: vertical;
    }
    .preview {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-top: 0.5rem;
      max-height: 200px;
      overflow-y: auto;
    }
    .matrix-block {
      background: #e6ffe6;
      color: black;
      padding: 0.5rem;
      border: 1px solid #4caf50;
      border-radius: 4px;
      font-family: monospace;
      text-align: center;
    }
    .row {
      margin: 0;
    }
    .empty {
      color: #777;
      font-style: italic;
    }
  `;
}

customElements.define('matrix-display', MatrixDisplay);
