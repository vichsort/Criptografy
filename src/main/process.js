export function encryptText(text) {
    const chars = text.split('');
    if (chars.length % 2 !== 0) chars.push('.');
  
    const pairs = [];
    for (let i = 0; i < chars.length; i += 2) {
      pairs.push([chars[i].charCodeAt(0), chars[i + 1].charCodeAt(0)]);
    }
  
    const keyMatrices = [];
    const resultPairs = [];
  
    pairs.forEach(pair => {
      const key = [
        [rand(), rand()],
        [rand(), rand()]
      ];
      keyMatrices.push(key);
  
      const [a, b] = pair;
      const c0 = key[0][0] * a + key[0][1] * b;
      const c1 = key[1][0] * a + key[1][1] * b;
      resultPairs.push([c0, c1]);
    });
  
    return {
      cryptotext: JSON.stringify(resultPairs),
      matrix: keyMatrices
    };
}
  
export function decryptText(cryptotextJSON, keyMatrices) {
    const cipherPairs = JSON.parse(cryptotextJSON);
    const chars = [];
  
    cipherPairs.forEach((pair, idx) => {
      const key = keyMatrices[idx];
      const [[k11, k12], [k21, k22]] = key;
      const det = k11 * k22 - k12 * k21;
      if (det === 0) throw new Error('Matriz não invertível');
      const invDet = 1 / det;
  
      const inv = [
        [ k22 * invDet, -k12 * invDet ],
        [ -k21 * invDet, k11 * invDet ]
      ];
  
      const [c0, c1] = pair;
      const p0 = Math.round(inv[0][0] * c0 + inv[0][1] * c1);
      const p1 = Math.round(inv[1][0] * c0 + inv[1][1] * c1);
  
      chars.push(String.fromCharCode(p0), String.fromCharCode(p1));
    });
  
    if (chars[chars.length - 1] === '.') chars.pop();
    return chars.join('');
}
  
function rand() {
    return Math.floor(Math.random() * 25) + 1;
}
  