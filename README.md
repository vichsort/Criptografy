# Criptografy
Formas diferentes de criptografar.

```text
📁 Criptografy
├── 📁 node_modules
├── 📁 public
├── 📁 src
│   ├── 📁 assets
│   └── 📁 components
│   │   ├── 📄 footer.js
│   │   └── 📄 navbar.js
│   └── 📁 main
│   │   ├── 📄 decript.js // pagina de decriptografia
│   │   ├── 📄 encript.js // pagina de criptografia
│   │   └── 📄 process.js // local onde ocorre a coleta e manuseio de informações
│   └── 📄 index.css
│   └── 📄 main.js // página principal
├── 📄 .gitignore
├── 📄 index.html 
├── 📄 package-lock.json        
├── 📄 package.json
└── 📄 README.md
```

# Encript
recebe tanto texto a encriptar ou decriptar (quando pai mandar)<br>
Encripta uma string usando Hill Cipher 2x2 (matrizes 2×2 por par de caracteres). <br>
Parâmetros - string plana <br>
Retorna - cryptotext: JSON string dos pares criptografados [[c0,c1],…] e matrix:     array de matrizes-chave 2×2 para cada par <br>

# Decript
só recebe texto criptografado e lê a matriz em matrix <br>
só chama a função se a matriz for par <br>
Parâmetros - string JSON de [[c0,c1],…] e matriz par <br>
Retorna - string plana