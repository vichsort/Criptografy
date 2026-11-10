# Criptografy API

API REST para experimentação e aprendizado de conceitos criptográficos, cifras clássicas, criptografia moderna autenticada, funções de hashing e codificações.

O projeto é construído em **Laravel 13** como um backend de API puro (stateless, sem persistência e com suporte a CORS habilitado), pronto para ser consumido por clientes externos (ex.: Next.js).

---

## Arquitetura e Fluxo de Execução

O ciclo de vida de qualquer operação criptográfica na API segue um fluxo estrito e desacoplado:

```text
HTTP Request (POST /api/encrypt, /api/hash, etc.)
  │
  ▼
CryptographyOperationRequest (Validação sintática: algorithm, input, options)
  │
  ▼
Controller (Orquestrador fino: EncryptController, HashController, etc.)
  │
  ▼
Registry (Resolução do algoritmo por identificador estável)
  │
  ▼
Algoritmo Concreto (Caesar, Aes256Gcm, Sha256, etc. - Regra pura)
  │
  ▼
HTTP JSON Response (ou Exception Handler global traduzindo exceções de domínio)
```

### Componentes Principais

* **Controllers Finos**: Cada endpoint possui um controller dedicado (`EncryptController`, `DecryptController`, `HashController`, `EncodeController`, `DecodeController`, `AlgorithmController`). O controller não implementa lógica criptográfica; apenas resolve a instância no `Registry`, valida a interface de contrato e despacha a chamada.
* **Registry Centralizado (`App\Cryptography\Registry`)**: Mapeia identificadores de algoritmos (`'caesar'`, `'aes-256-gcm'`, etc.) para suas instâncias. Restringe os registros estritamente a implementações de `DescribesAlgorithm`.
* **Provedor de Serviço (`App\Providers\CryptographyServiceProvider`)**: Único ponto do sistema que conhece todos os algoritmos ativos e os registra no `Registry`, garantindo aderência ao princípio Open/Closed (OCP).
* **Tratamento de Exceções Global (`bootstrap/app.php`)**: Exceções de domínio herdam de `InvalidAlgorithmOptionsException` ou `AlgorithmNotFoundException` e são convertidas automaticamente em respostas HTTP (422 e 404), mantendo os controllers limpos de status codes HTTP. Toda resposta de erro em `/api/*` é JSON, mesmo sem o header `Accept: application/json`, e erros causados pela entrada do cliente não são registrados no log.
* **Integridade de Entrada (`bootstrap/app.php`)**: O campo `input` é explicitamente protegido contra sanitizações automáticas (`TrimStrings` e `ConvertEmptyStringsToNull`). Em criptografia, espaços e strings vazias são dados significativos (como o hash NIST de string vazia).

---

## Contratos e Interfaces

Os algoritmos estão organizados em `app/Cryptography/Contracts/`:

| Contrato | Responsabilidade | Métodos |
|---|---|---|
| `DescribesAlgorithm` | Auto-descrição de metadados para consumo do frontend e documentação | `describe(): AlgorithmMetadata` |
| `EncryptionAlgorithm` | Criptografia reversível com chave ou parâmetros de deslocamento | `encrypt(string $input, array $options = []): string`<br>`decrypt(string $input, array $options = []): string` |
| `HashAlgorithm` | Digest unidirecional e irreversível (não possui decrypt) | `hash(string $input, array $options = []): string` |
| `EncodingAlgorithm` | Transformação de representação sem garantias de confidencialidade | `encode(string $input): string`<br>`decode(string $input): string` |

---

## Algoritmos Implementados e Limitações

### Cifras Clássicas
* **Caesar (`caesar`)**: Cifra de substituição por deslocamento modular (`C = (P + shift) mod 26`). Apenas letras ASCII (`A-Z`, `a-z`) sofrem rotação; caracteres acentuados, números, símbolos e caracteres multibyte UTF-8 são preservados sem alteração.
* **Vigenère (`vigenere`)**: Cifra polialfabética por soma modular com repetição de chave (`C = (P + K) mod 26`). Caracteres não alfabéticos e acentos são preservados e **não avançam o ponteiro da chave**, mantendo a sincronia do deslocamento letra a letra.
* **Hill (`hill`)**: Cifra poligráfica baseada em álgebra linear matricial (`C = K × P mod 26`).
  * *Limitação*: Opera estritamente sobre blocos fixos de letras ASCII em maiúsculas. Caracteres acentuados, espaços e pontuações são descartados. Se o tamanho do texto não for múltiplo da dimensão da matriz, o último bloco é preenchido com `X`. Exige que o determinante da matriz-chave seja coprimo de 26 (`mdc(det, 26) = 1`).

### Criptografia Moderna
* **AES-256-GCM (`aes-256-gcm`)**: Criptografia autenticada moderna delegada à extensão nativa OpenSSL (`openssl_encrypt`/`openssl_decrypt`).
  * Gera um nonce criptográfico aleatório de 12 bytes a cada execução.
  * Produz uma tag de autenticação GCM de 16 bytes para verificação de integridade.
  * O retorno empacota `base64(nonce . tag . ciphertext)`.
  * **Aviso didático de segurança**: A chave informada é convertida em 256 bits via `hash('sha256', $key, binary: true)`. Isso é uma simplificação deliberada para permitir qualquer string como chave neste demonstrativo. Em produção, chaves devem ser derivadas com KDFs dedicadas (ex.: Argon2id ou PBKDF2) acompanhadas de salt e fator de trabalho.

### Funções de Hash
* **SHA-256 (`sha-256`)**: Digest determinístico de 256 bits (64 caracteres hexadecimais) via `hash()`.
* **SHA-512 (`sha-512`)**: Digest determinístico de 512 bits (128 caracteres hexadecimais) via `hash()`.

### Encodings
* **Base64 (`base64`)** e **Hex (`hex`)**: Mudança de representação binária para texto ASCII. Não conferem confidencialidade ou autenticidade.

---

## Como Adicionar um Novo Algoritmo

Para adicionar um algoritmo sem alterar o código existente (Open/Closed):

1. **Criar a classe do algoritmo**: Crie um diretório em `app/Cryptography/{NomeDoAlgoritmo}/`. A classe deve implementar `DescribesAlgorithm` e um dos contratos de operação (`EncryptionAlgorithm`, `HashAlgorithm` ou `EncodingAlgorithm`).
2. **Definir exceções dedicadas**: Se o algoritmo exigir validações específicas de opções ou chaves, crie uma classe de exceção que herde de `App\Cryptography\Contracts\InvalidAlgorithmOptionsException`.
3. **Registrar no Service Provider**: Adicione uma única linha em `app/Providers/CryptographyServiceProvider.php`:
   ```php
   $registry->register('identificador-unico', $app->make(NovoAlgoritmo::class));
   ```
4. **Adicionar testes unitários**: Crie a suíte em `tests/Unit/Cryptography/{NomeDoAlgoritmo}/` validando reversibilidade, entradas inválidas, strings vazias e metadados.

---

## Execução e Testes

### Requisitos
* PHP 8.3 ou superior
* Extensão OpenSSL do PHP habilitada
* Composer 2+

### Instalação e Execução
```bash
composer install
cp .env.example .env
php artisan key:generate

# Iniciar servidor da API
php artisan serve
```

### Executar Testes
```bash
php artisan test
```
