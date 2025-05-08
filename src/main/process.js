export function processForm(e, cript) {
    e.preventDefault()
    const form = e.target
  
    const formData = new FormData(form)
    const value = formData.get("value")

    if (cript == true) {
        console.log("estamos encriptando");
        encript(value);
    } else {
        console.log("estamos decriptando");
        decript(value);
    }
}

function encript(val) {
    console.log(val);

    const arrayVal = val.split("");
    if (arrayVal.length % 2 !== 0) {
        // matrix multiplications can only be assigned to even numbers
        arrayVal.push(".");
    }

    // generates the 'matrix'
    let matrix = [];
    for (let i = 0; i < arrayVal.length; i += 2) {
      matrix.push([arrayVal[i], arrayVal[i + 1]]);
    }

    let numMatrix = matrix.map(par => par.map(char => char.charCodeAt(0)));
    let randMatrix = numMatrix.map(par => par.map(() => Math.floor(Math.random() * 100) + 1));

    const criptMatrix = numMatrix.map((par, i) =>
        par.map((num, j) => (num * randMatrix[i][j]).toString()).join(".")
      ).join(".");

    const criptKey = randMatrix.map(par => par.join(", ")).join(",. ");

    console.log(criptMatrix)
    console.log('-------')
    console.log(criptKey)
}

function decript(val) {
    console.log(val);
    
}

function criptografar() {
  
    // cria a matriz de pares de caracteres
    let matriz = [];
    for (let i = 0; i < arrayVal.length; i += 2) {
      matriz.push([arrayVal[i], arrayVal[i + 1]]);
    }
  
    // converte para o valor do pc
    let matrizNumeros = matriz.map(par => par.map(char => char.charCodeAt(0)));
  
    // cria uma matriz de números aleatórios
    let matrizAleatoria = matrizNumeros.map(par => par.map(() => Math.floor(Math.random() * 100) + 1));
  
    // multiplica as    matrizes
    let matrizCriptografada = matrizNumeros.map((par, i) =>
      par.map((num, j) => (num * matrizAleatoria[i][j]).toString()).join(".")
    ).join(".");
  
    // formata a matriz aleatória para ser impressa como um texto corrido, separando as linhas por '.'
    let chaveDescriptografia = matrizAleatoria.map(par => par.join(", ")).join(",. ");
  
    document.write("Palavra criptografada:");
    document.write(matrizCriptografada);
    document.write("Chave de descriptografia:");
    document.write(chaveDescriptografia);
}

function descriptografar() {

    let palavraCriptografada = prompt("Insira a palavra criptografada:");
    let chaveDescriptografiaTexto = prompt("Insira a chave de descriptografia:");

    // converte a palavra criptografada em uma matriz de números (removendo os pontos)
    let numerosCriptografados = palavraCriptografada.split(".").map(Number);

    // converte a chave de descriptografia do formato corrido de volta para uma matriz de números
    let matrizAleatoria = chaveDescriptografiaTexto
        .split(',. ') 
        .map(linha => linha.split(', ').map(Number)); 
    if (numerosCriptografados.length !== matrizAleatoria.flat().length) {
        console.error("O tamanho da palavra criptografada e da chave de descriptografia não correspondem.");
        return;
    }

    // cria a matriz de pares com base no tamanho da chave de descrpiptografia
    let indice = 0;
    let matrizOriginal = [];
    for (let i = 0; i < matrizAleatoria.length; i++) {
        let par = [];
        for (let j = 0; j < matrizAleatoria[i].length; j++) {
        // divide essa ´porra (o número criptografado) pelo valor da matriz aleatória correspondente
        let numOriginal = numerosCriptografados[indice] / matrizAleatoria[i][j];
        par.push(Math.round(numOriginal)); // arredonda
        indice++;
        }
        matrizOriginal.push(par);
    }

    // converte essa porra
    let fraseDescriptografada = matrizOriginal.map(par => par.map(num => String.fromCharCode(num)).join("")).join("");

    document.write("Frase descriptografada: " + fraseDescriptografada);
}