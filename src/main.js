import { LitElement, html, css } from 'lit';
import './components/navbar.js';
import './components/footer.js';
import './components/aside.js';  // o menu lateral

class AppShell extends LitElement {
  static properties = {
    activeView: { type: String },
    _viewTemplate: { state: true }
  };

  constructor() {
    super();
    this.activeView = 'matrix';   // view inicial
    this._viewTemplate = html``;
  }

  render() {
    return html`
      <navbar-element></navbar-element>

      <div class="layout">
        <aside-menu 
          .items=${[
            { id: 'matrix', label: 'Hill Cipher' },
            { id: 'cesar',  label: 'Cifra de César' },
            { id: 'rsa',    label: 'RSA' }
          ]}
          .active=${this.activeView}
          @view-change=${this._onViewChange}
        ></aside-menu>

        <main class="content">
          ${this._viewTemplate}
        </main>
      </div>

      <footer-element></footer-element>
    `;
  }

  updated(changed) {
    if (changed.has('activeView')) {
      this._loadView(this.activeView);
    }
  }

  async _loadView(viewId) {
    this._viewTemplate = html`<p>Carregando ${viewId}…</p>`;
    let tpl;
    switch (viewId) {
      case 'matrix':
        await import('./pages/matrix.js');
        tpl = html`<matrix-page></matrix-page>`;
        break;
      case 'cesar':
        await import('./pages/cesar.js');
        tpl = html`<cesar-page></cesar-page>`;
        break;
      case 'rsa':
        await import('./pages/rsa.js');
        tpl = html`<rsa-page></rsa-page>`;
        break;
      default:
        tpl = html`<p>View não encontrada</p>`;
    }
    this._viewTemplate = tpl;
  }

  _onViewChange(e) {
    this.activeView = e.detail.viewId;
  }

  static styles = css`
    :host {
      display: block;
      min-height: 100vh;
      --sidebar-width: 240px;
    }
    .layout {
      display: flex;
    }
    main.content {
      flex: 1;
      padding: 1rem;
    }
  `;
}

customElements.define('app-shell', AppShell);
