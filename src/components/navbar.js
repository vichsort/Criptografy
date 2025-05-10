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
                background-color: green;
                padding: 1rem 6rem;
                display: flex;
                justify-content: center;
            }

            img {
                width: 10rem;
                height: 4rem;
            }
        `
    }
}

window.customElements.define('navbar-element', NavbarElement)
