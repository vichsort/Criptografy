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
        arrayVal.push(".");
    }

    const matrix = [];
    for (let i = 0; i < arrayVal.length; i += 2) {
        matrix.push([arrayVal[i], arrayVal[i + 1]]);
    }

    const numMatrix = matrix.map(pair => pair.map(char => char.charCodeAt(0)));
    const randMatrix = numMatrix.map(pair => pair.map(() => Math.floor(Math.random() * 20) + 1));

    const criptMatrix = numMatrix.map((pair, i) =>
        pair.map((num, j) => (num * randMatrix[i][j]).toString()).join(".")
    ).join(".");

    const criptKey = JSON.stringify(randMatrix);

    console.log('Criptografado:', criptMatrix);
    console.log('Chave:', criptKey);
}


function decript(val) {
    console.log(val);

    let cript = val.split(".").map(Number);
    let randMatrix = criptKey
        .split(',. ') 
        .map(linha => linha.split(', ').map(Number)); 

    if (cript.length !== randMatrix.flat().length) {
        console.error("Either your encripted text or your key is invalid.");
        return;
    }

    let index = 0;
    let matrix = [];
    for (let i = 0; i < randMatrix.length; i++) {
        let doubles = [];
        for (let j = 0; j < randMatrix[i].length; j++) {
        let originalNum = cript[indice] / randMatrix[i][j];
        doubles.push(Math.round(originalNum)); // arredonda
        index++;
        }
        matrix.push(doubles);
    }

    // converte essa porra
    let ans = matrix.map(par => par.map(num => String.fromCharCode(num)).join("")).join("");

    console.log("Frase descriptografada: ", ans);
}