import { LitElement, css, html } from 'lit';

class AsideElement extends LitElement {
    render() {
        return html`
            <a href="#">Enctypt by Matrix</a>
            <a href="#">Encypt by Cipher of Ceasar</a>
            <a href="#">Encypt by Assimetric key</a>
            <a href="#">Encypt by Hash</a>
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

window.customElements.define('aside-element', AsideElement)