// src/components/aside.js
import { LitElement, html, css } from 'lit';

class AsideMenu extends LitElement {
  static properties = {
    items:  { type: Array },
    active: { type: String }
  };

  constructor() {
    super();
    this.items = [];
    this.active = '';
  }

  render() {
    return html`
      <aside>
        ${this.items.map(item => html`
          <button
            class=${item.id === this.active ? 'active' : ''}
            @click=${() => this._select(item.id)}
          >
            ${item.label}
          </button>
        `)}
      </aside>
    `;
  }

  _select(id) {
    this.dispatchEvent(new CustomEvent('view-change', {
      detail: { viewId: id },
      bubbles: true,
      composed: true
    }));
  }

  static styles = css`
    aside {
      width: var(--sidebar-width);
      background: #f4f4f4;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    button {
      background: none;
      border: none;
      text-align: left;
      padding: 0.5rem;
      cursor: pointer;
      font-size: 1rem;
    }
    button.active {
      font-weight: bold;
      background: #ddd;
    }
  `;
}

customElements.define('aside-menu', AsideMenu);
