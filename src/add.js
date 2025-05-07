function iniciar() {
    let escolha = prompt("O que você gostaria de fazer? (C) Criptografar ou (D) Descriptografar:");
    if (escolha.toUpperCase() === 'C') {
        criptografar(); 
    } else if (escolha.toUpperCase() === 'D') {
        descriptografar();
    } else {
        console.log("Escolha inválida. Por favor, insira 'C' para criptografar ou 'D' para descriptografar.");
    }
}

iniciar();
  
function criptografar() {
    let frase = prompt("Insira uma frase para criptografar:");
  
    // o prompt vira array
    let arrayFrase = frase.split("");
  
    // a multiplicação de matrizes precisa ter matriz par, entao aqui decide
    if (arrayFrase.length % 2 !== 0) {
      arrayFrase.push(".");
    }
  
    // cria a matriz de pares de caracteres
    let matriz = [];
    for (let i = 0; i < arrayFrase.length; i += 2) {
      matriz.push([arrayFrase[i], arrayFrase[i + 1]]);
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