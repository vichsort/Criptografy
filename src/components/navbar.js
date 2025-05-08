import { LitElement, css, html } from 'lit';

class NavbarElement extends LitElement {
    render() {
        return html`
            <div class="sidenav" id="sidenav">
                <a href="#" class="close" @click=${this._closeSide}>&times;</a>
                <a href="#">Sobre</a>
                <a href="#">Introdução</a>
                <a href="#">Saiba mais</a>
            </div>

            <button @click=${this._openSide}>Abrir pros cria</button>
        `
    }

    _openside() {
        document.getElementById('sidenav').style.width = '200px';
        console.log('whatsapp');
    }

    _closeside() {
        document.getElementById('sidenav').style.width = '0';
    }
    
    static get styles() {
        return css`
            .sidenav {
                height: 100%;
                width: 0;
                position: fixed;
                z-index: 1;
                top: 0;
                left: 0;
                background-color: #111;
                overflow-x: hidden;
                transition: 0.5s;
                padding-top: 60px;
            }
        `
    }
}

window.customElements.define('navbar-element', NavbarElement)
