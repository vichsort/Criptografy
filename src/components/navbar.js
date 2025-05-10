import { LitElement, css, html } from 'lit';

class NavbarElement extends LitElement {
    render() {
        return html`
            <img src="/logo.png" alt="our logo">
        `;
    }
    
    static get styles() {
        return css`
            :host {
                background-color: #242424;
                padding: 0.2rem 0.2rem;
                display: flex;
                justify-content: center;
            }

            img {
                width: 23rem;
                height: 8rem;
            }
        `
    }
}

window.customElements.define('navbar-element', NavbarElement)
