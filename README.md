# Criptografy — Esqueleto do projeto

Este é o esqueleto inicial do monorepo Laravel + React/TypeScript descrito na
especificação do MVP. Nesta etapa foram criados **apenas a estrutura de
pastas e os arquivos de configuração** — nenhuma lógica de negócio ou
algoritmo foi implementado ainda.

## O que já existe

```text
app/
├── Cryptography/           # um diretório por algoritmo (vazio por enquanto)
│   ├── Contracts/          # interfaces compartilhadas (próxima etapa)
│   ├── Caesar/
│   ├── Vigenere/
│   ├── Hill/
│   ├── Aes256Gcm/
│   ├── Sha256/
│   ├── Sha512/
│   ├── Base64/
│   └── Hex/
├── Http/Controllers/       # controllers ainda não implementados
└── Providers/

resources/
├── js/
│   ├── app.tsx              # bootstrap do Inertia + React
│   ├── Pages/Home.tsx       # placeholder da página principal
│   └── Components/
├── css/app.css              # Tailwind v4 (`@import "tailwindcss";`, sem config file)
└── views/app.blade.php      # root view consumida pelo Inertia

routes/
├── web.php                  # rota da SPA (Inertia)
├── api.php                  # endpoints comentados (Seção 19 da spec)
└── console.php

tests/
├── Unit/Cryptography/       # um teste por algoritmo
└── Feature/                 # testes de integração HTTP -> Controller -> Registry

artisan                      # CLI do Laravel
bootstrap/app.php            # bootstrap da aplicação (Laravel 13, estilo Application::configure)
bootstrap/providers.php
public/index.php             # front controller
config/{app,cache,session,logging,filesystems}.php
composer.json                # deps do backend (Laravel 13 + Inertia 3)
package.json                 # deps do frontend (React + TS + Vite + Tailwind v4)
vite.config.ts
tsconfig.json
phpunit.xml
.env.example                 # sem nenhuma variável de banco de dados
```

## Versões — correção importante

Na primeira versão deste esqueleto eu havia assumido **Laravel 11**, que era a
versão mais recente até o meu conhecimento de treinamento (início de 2026).
Ao validar contra os repositórios oficiais (`laravel/laravel`,
`laravel/react-starter-kit`), confirmei que isso está desatualizado:

| Item | Usado antes | Correto hoje (set/2026) |
|---|---|---|
| Laravel | 11.x | **13.x** — 11 perdeu suporte de segurança em 12/mar/2026; 12 perdeu correção de bugs em 13/ago/2026 |
| PHP | ^8.2 | **^8.3** (mínimo exigido pelo Laravel 13) |
| `inertiajs/inertia-laravel` | ^1.0 | **^3.0** |
| Tailwind CSS | v3 (`tailwind.config.js` + PostCSS) | **v4** — sem arquivo de config, plugin nativo do Vite (`@tailwindcss/vite`), `@import "tailwindcss";` no CSS |

Isso já está corrigido nos arquivos deste zip. Ainda assim, recomendo rodar
`composer install` / `npm install` localmente e conferir se apareceu alguma
versão mais nova entre a geração deste esqueleto e o momento em que você for
usá-lo — eu não tenho como garantir que isso continue atual.

## Estado atual (núcleo já implementado)

Além do esqueleto do framework, já existe uma fatia vertical completa e
funcional do fluxo descrito na Seção 6:

```
HTTP -> Controller -> Validation -> Registry -> Caesar -> Result -> HTTP
```

- `app/Cryptography/Contracts/` — `EncryptionAlgorithm`, `HashAlgorithm`,
  `EncodingAlgorithm`, `DescribesAlgorithm`, `AlgorithmMetadata` (Seções 8 e 16)
- `app/Cryptography/Registry.php` — registra instâncias por id, resolve,
  agrega metadados (Seção 9)
- `app/Cryptography/Caesar/` — implementação completa (`Caesar.php`,
  `CaesarException.php`)
- `app/Cryptography/Vigenere/` — implementação completa (`Vigenere.php`,
  `VigenereException.php`), validada contra o exemplo clássico
  `LEMON`/`ATTACKATDAWN` → `LXFOPVEFRNHR`
- `app/Cryptography/Hill/` — implementação completa (`Hill.php`,
  `HillException.php`): determinante por cofatores, matriz adjugada,
  inverso modular (Euclides estendido), validada contra dois exemplos
  clássicos (2x2 `HELP`→`HIAT` e o 3x3 da Wikipedia `ACT`→`POH`).
  **Diferença deliberada**: por operar em blocos de tamanho fixo, o Hill
  normaliza para apenas letras maiúsculas (remove espaços/pontuação) e
  preenche o último bloco com `X` — ao contrário do Caesar/Vigenère, que
  preservam tudo. É uma limitação estrutural de cifras de bloco clássicas,
  documentada no docblock da classe.
- `app/Cryptography/Aes256Gcm/` — criptografia moderna autenticada via
  **OpenSSL nativo do PHP** (`openssl_encrypt`/`openssl_decrypt`), sem
  reimplementar o AES manualmente (Seção 12). Nonce aleatório a cada
  chamada (duas criptografias do mesmo texto nunca são iguais), tag de
  autenticação GCM detecta chave errada ou dado adulterado. **Nota de
  segurança documentada no código**: a chave informada é transformada em
  256 bits via SHA-256 — não é uma KDF apropriada para senhas reais
  (faltam salt e custo computacional); é uma simplificação deliberada para
  fins didáticos, deixada explícita para não passar falsa sensação de
  segurança de produção.
- `app/Cryptography/Sha256/` e `app/Cryptography/Sha512/` — hashing via
  `hash()` nativo do PHP, sem reimplementação manual (Seção 13). Não
  implementam `EncryptionAlgorithm` — hash não tem `decrypt()`.
- `app/Cryptography/Base64/` e `app/Cryptography/Hex/` — encoding via
  funções nativas do PHP (Seção 14). Implementam `EncodingAlgorithm`
  (`encode`/`decode`), não `EncryptionAlgorithm` — por isso
  `supportsEncryption`/`supportsDecryption` ficam `false` nos metadados,
  mesmo sendo tecnicamente reversíveis: o objetivo é sinalizar ao frontend
  que a operação correta é `/api/encode` e `/api/decode`, não
  `/api/encrypt`/`/api/decrypt`.
- `app/Providers/CryptographyServiceProvider.php` — registra o Caesar no
  Registry; novos algoritmos entram aqui com uma linha, sem tocar em mais
  nada (Open/Closed, Seção 20)
- `app/Http/Controllers/{Algorithm,Encrypt,Decrypt}Controller.php` +
  `CryptographyOperationRequest` — controllers finos, não sabem criptografar
- `bootstrap/app.php` — mapeia exceções de domínio para respostas HTTP
  (404 algoritmo inexistente, 422 opções inválidas/operação não suportada —
  Seção 23)
- `tests/Unit/Cryptography/{Caesar,Registry}Test.php` — PHPUnit puro, sem
  depender do framework
- `tests/Feature/Cryptography/{EncryptDecrypt,AlgorithmController}Test.php`
  — integração HTTP -> Controller -> Registry -> Algoritmo -> Response

### O que foi validado de fato, e o que não foi

Neste ambiente não há acesso ao Packagist, então `composer install` nunca
rodou de verdade aqui. Por isso:

- ✅ **Sintaxe**: todo arquivo PHP passou por `php -l`.
- ✅ **PSR-4**: todo namespace foi conferido contra o caminho do arquivo.
- ✅ **Lógica de negócio**: `Caesar` e `Registry` foram executados de verdade
  (fora do Laravel, com um script que dá `require` direto nos arquivos) —
  40 asserções (Caesar + Vigenère + Hill + Registry), incluindo
  reversibilidade, wraparound, shift negativo/>26, preservação de
  caracteres não-alfabéticos (e, no Vigenère, que eles não desalinham a
  posição da chave), os exemplos clássicos do Vigenère e do Hill (2x2 e
  3x3), padding automático do Hill, e todos os casos de erro — incluindo
  matriz não-quadrada, matriz não-inversível mod 26, e chave com valores
  não-inteiros.
- ❌ **Não executados**: os testes de `tests/Feature` (dependem do
  framework completo — `Illuminate\Foundation\Testing\TestCase`, container,
  roteamento real). Foram escritos seguindo o padrão oficial e revisados
  manualmente, mas só serão comprovados quando você rodar
  `php artisan test` no seu ambiente.

## O que falta (propositalmente, por enquanto)

- Próximos algoritmos: `Aes256Gcm`, `Sha256`, `Sha512`, `Base64`, `Hex`
- `HashController` / `EncodeController` (rotas já comentadas em
  `routes/api.php`, aguardando o primeiro algoritmo de hash/encoding)
- Testes de contrato (garantir que toda implementação de
  `EncryptionAlgorithm` respeita o comportamento esperado — Seção 22)
- Interface do usuário consumindo `GET /api/algorithms` (Seção 15)

## Observação sobre o ambiente

Este esqueleto foi criado manualmente (arquivos de texto), sem rodar
`composer install` ou `npm install`. Para rodar o projeto localmente:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run dev
php artisan serve
```

Não há nenhuma configuração de banco de dados — nem migrations, nem models —
porque o MVP não utiliza persistência (Seção 18 da especificação).
