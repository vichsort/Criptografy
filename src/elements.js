import { LitElement, css, html } from 'lit'
export class InputComponent extends LitElement {

  render() {
    return html`
      <form method="post" action="{{ url_for('buscar') }}">
        <div class="col-md-4">
          <label for="caller" class="form-label">Buscar por</label>
          <select class="form-select" id="caller" name="caller" required>
            <option value="title">Título</option>
            <option value="id">IMDb ID</option>
          </select>
        </div>
        <div class="col-md-8">
          <label for="info" class="form-label">Título ou ID</label>
          <input
            type="text"
            class="form-control"
            id="info"
            name="info"
            placeholder="Ex: tt1877830 ou The Batman"
            required
          />
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
      </form>
    `
  }

  _onClick() {
    this.count++
  }

  static get styles() {
    return css`
      :host {
        max-width: 1280px;
        margin: 0 auto;
        padding: 2rem;
        text-align: center;
      }

      .logo {
        height: 6em;
        padding: 1.5em;
        will-change: filter;
        transition: filter 300ms;
      }
      .logo:hover {
        filter: drop-shadow(0 0 2em #646cffaa);
      }
      .logo.lit:hover {
        filter: drop-shadow(0 0 2em #325cffaa);
      }

      .card {
        padding: 2em;
      }

      .read-the-docs {
        color: #888;
      }

      a {
        font-weight: 500;
        color: #646cff;
        text-decoration: inherit;
      }
      a:hover {
        color: #535bf2;
      }

      ::slotted(h1) {
        font-size: 3.2em;
        line-height: 1.1;
      }

      button {
        border-radius: 8px;
        border: 1px solid transparent;
        padding: 0.6em 1.2em;
        font-size: 1em;
        font-weight: 500;
        font-family: inherit;
        background-color: #1a1a1a;
        cursor: pointer;
        transition: border-color 0.25s;
      }
      button:hover {
        border-color: #646cff;
      }
      button:focus,
      button:focus-visible {
        outline: 4px auto -webkit-focus-ring-color;
      }

      @media (prefers-color-scheme: light) {
        a:hover {
          color: #747bff;
        }
        button {
          background-color: #f9f9f9;
        }
      }
    `
  }
}

window.customElements.define('input-component', InputComponent)
