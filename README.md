# Criptografy API

[![CI](https://github.com/vichsort/Criptografy/actions/workflows/ci.yml/badge.svg)](https://github.com/vichsort/Criptografy/actions/workflows/ci.yml)

REST API for experimenting with and learning cryptographic concepts: classical ciphers, modern authenticated encryption, hash functions and encodings.

The project is built with **Laravel 13** as a pure API backend (stateless, no persistence, CORS enabled), ready to be consumed by external clients (e.g. Next.js).

---

## Architecture and Request Flow

Every cryptographic operation follows the same strict, decoupled flow:

```text
HTTP Request (POST /api/encrypt, /api/hash, etc.)
  │
  ▼
CryptographyOperationRequest (syntactic validation: algorithm, input, options)
  │
  ▼
Controller (thin orchestrator: EncryptController, HashController, etc.)
  │
  ▼
Registry (resolves the algorithm by its stable identifier)
  │
  ▼
Concrete algorithm (Caesar, Aes256Gcm, Sha256, etc. - pure logic)
  │
  ▼
HTTP JSON Response (or the global exception handler translating domain exceptions)
```

### Main Components

* **Thin controllers**: each endpoint has a dedicated controller (`EncryptController`, `DecryptController`, `HashController`, `EncodeController`, `DecodeController`, `AlgorithmController`). Controllers hold no cryptographic logic; they resolve the instance from the `Registry`, check that it implements the right contract and dispatch the call.
* **Central registry (`App\Cryptography\Registry`)**: maps algorithm identifiers (`'caesar'`, `'aes-256-gcm'`, etc.) to their instances. Only `DescribesAlgorithm` implementations can be registered, and each one is registered under its own `describe()->id`.
* **Service provider (`App\Providers\CryptographyServiceProvider`)**: the only place that knows every active algorithm and registers it in the `Registry`, following the Open/Closed principle.
* **Global exception handling (`bootstrap/app.php`)**: domain exceptions are turned into HTTP responses automatically, so controllers never deal with status codes. Every error response under `/api/*` is JSON, even without an `Accept: application/json` header, and errors caused by client input are not written to the log.
* **Rate limiting and proxy (`bootstrap/app.php`, `AppServiceProvider`)**: API routes are limited per IP (`API_RATE_LIMIT`, 60/min by default; above that, 429). `X-Forwarded-For` is trusted so the client's real IP is used behind the hosting platform's proxy.
* **Input integrity (`bootstrap/app.php`)**: `input` and `options.*` are excluded from Laravel's automatic sanitization (`TrimStrings` and `ConvertEmptyStringsToNull`). In cryptography, whitespace and empty strings are meaningful data (e.g. the NIST hash of the empty string, or a key with spaces).

---

## Endpoints

All requests and responses are JSON.

| Method | Path | Body | Success |
|---|---|---|---|
| `GET` | `/api/algorithms` | none | `{ "data": [metadata, ...] }` |
| `POST` | `/api/encrypt` | `{ "algorithm", "input", "options" }` | `{ "result": "..." }` |
| `POST` | `/api/decrypt` | `{ "algorithm", "input", "options" }` | `{ "result": "..." }` |
| `POST` | `/api/hash` | `{ "algorithm", "input" }` | `{ "result": "..." }` |
| `POST` | `/api/encode` | `{ "algorithm", "input" }` | `{ "result": "..." }` |
| `POST` | `/api/decode` | `{ "algorithm", "input" }` | `{ "result": "..." }` |
| `GET` | `/` | none | API status (name, status, version) |
| `GET` | `/up` | none | health check |

Errors always come as `{ "message": "..." }` (plus `errors` for request validation failures):

| Status | When |
|---|---|
| `404` | unknown algorithm (`AlgorithmNotFoundException`) or route |
| `405` | wrong HTTP method |
| `422` | request validation failure; invalid options or key (`InvalidAlgorithmOptionsException` subclasses); operation the algorithm does not support (`UnsupportedOperationException`); result that is not valid UTF-8 text and cannot be carried in JSON (`NonTextResultException`) |
| `429` | rate limit exceeded |

---

## Contracts and Interfaces

The contracts live in `app/Cryptography/Contracts/`:

| Contract | Responsibility | Methods |
|---|---|---|
| `DescribesAlgorithm` | Self-describing metadata for the frontend and documentation | `describe(): AlgorithmMetadata` |
| `EncryptionAlgorithm` | Reversible encryption with a key or shift parameters | `encrypt(string $input, array $options = []): string`<br>`decrypt(string $input, array $options = []): string` |
| `HashAlgorithm` | One-way, irreversible digest (no decrypt) | `hash(string $input, array $options = []): string` |
| `EncodingAlgorithm` | Change of representation with no confidentiality guarantees | `encode(string $input): string`<br>`decode(string $input): string` |

### Options schema (`GET /api/algorithms`)

Each algorithm declares in `describe()` the **JSON Schema** of the options it accepts (the `options` field), built with `App\Cryptography\Contracts\OptionsSchema`. The frontend can generate and validate its form from it without duplicating rules:

```json
{
  "id": "caesar",
  "options": {
    "type": "object",
    "properties": {
      "shift": { "type": "integer", "title": "Shift", "description": "..." }
    },
    "required": ["shift"],
    "additionalProperties": false
  }
}
```

* Only keywords that are identical in draft-07 and 2020-12 are used, and **`$schema` is not declared**, so it works with validators set up for draft-07 (e.g. the default AJV in `react-jsonschema-form`).
* Algorithms without options (hashes and encodings) expose an empty, closed object (`"properties": {}`).
* Rules JSON Schema cannot express (e.g. a Hill matrix that is square and invertible mod 26) are only validated by the backend, which answers 422.
* The server is more lenient than the schema (e.g. it accepts `shift` as a numeric string). The contract enforced by the tests is: whatever the algorithm rejects, the schema rejects too.
* `requiresKey` is kept for compatibility and is equivalent to `options.required` being non-empty.

---

## Implemented Algorithms and Limitations

### Classical ciphers
* **Caesar (`caesar`)**: substitution cipher with a modular shift (`C = (P + shift) mod 26`). Only ASCII letters (`A-Z`, `a-z`) are rotated; accented characters, digits, symbols and multibyte UTF-8 characters are preserved unchanged.
* **Vigenère (`vigenere`)**: polyalphabetic cipher based on a modular sum with a repeating key (`C = (P + K) mod 26`). Non-alphabetic and accented characters are preserved and **do not advance the key position**, keeping the shift in sync letter by letter.
* **Hill (`hill`)**: polygraphic cipher based on matrix algebra (`C = K × P mod 26`).
  * *Limitation*: it works strictly on fixed blocks of uppercase ASCII letters. Accented characters, spaces and punctuation are dropped. If the text length is not a multiple of the matrix size, the last block is padded with `X`. The key matrix's determinant must be coprime with 26 (`gcd(det, 26) = 1`).

### Modern encryption
* **AES-256-GCM (`aes-256-gcm`)**: modern authenticated encryption delegated to PHP's native OpenSSL extension (`openssl_encrypt`/`openssl_decrypt`).
  * Generates a random 12-byte cryptographic nonce on every call.
  * Produces a 16-byte GCM authentication tag for integrity checking.
  * The output packs `base64(nonce . tag . ciphertext)`.
  * **Educational security note**: the given key is turned into 256 bits with `hash('sha256', $key, binary: true)`. This is a deliberate simplification so any string can be used as a key in this demo. In production, keys must be derived with dedicated KDFs (e.g. Argon2id or PBKDF2) with a salt and a work factor.

### Hash functions
* **SHA-256 (`sha-256`)**: deterministic 256-bit digest (64 hex characters) via `hash()`.
* **SHA-512 (`sha-512`)**: deterministic 512-bit digest (128 hex characters) via `hash()`.

### Encodings
* **Base64 (`base64`)** and **Hex (`hex`)**: change binary data into ASCII text. They provide no confidentiality or authenticity.

---

## Adding a New Algorithm

To add an algorithm without changing existing code (Open/Closed):

1. **Create the algorithm class**: create a directory `app/Cryptography/{AlgorithmName}/`. The class must implement `DescribesAlgorithm` and one of the operation contracts (`EncryptionAlgorithm`, `HashAlgorithm` or `EncodingAlgorithm`), and declare its options schema in `describe()` (`OptionsSchema::object(...)` or `OptionsSchema::none()`).
2. **Define dedicated exceptions**: if the algorithm needs specific option or key validation, create an exception class extending `App\Cryptography\Contracts\InvalidAlgorithmOptionsException`. Messages are exposed to API clients, so write them in English.
3. **Register it in the service provider**: add a single line to `app/Providers/CryptographyServiceProvider.php`. The identifier is read from `describe()->id` (duplicate ids are rejected):
   ```php
   $registry->register($app->make(NewAlgorithm::class));
   ```
4. **Add unit tests**: create the suite in `tests/Unit/Cryptography/{AlgorithmName}/`, covering reversibility, invalid input, empty strings and metadata.
5. **Contract tests**: `tests/Contract/AlgorithmContractTest.php` automatically checks every registered algorithm (metadata consistent with the implemented contract, reversibility, hash determinism, option errors). Encryption algorithms only need an entry in `ENCRYPTION_FIXTURES` with valid options (`options`) and options that must be rejected (`invalid`); the suite checks that the JSON Schema agrees with the algorithm in both cases.

---

## Running and Testing

### Requirements
* PHP 8.3 or later
* PHP OpenSSL extension enabled
* Composer 2+

### Install and run
```bash
composer install
cp .env.example .env
php artisan key:generate

# Start the API server
php artisan serve
```

### Run the tests
```bash
php artisan test
```

### Quality checks
The same checks CI runs:
```bash
vendor/bin/pint --test       # code style
vendor/bin/phpstan analyse   # static analysis (Larastan, level max)
composer audit               # known vulnerabilities in dependencies
vendor/bin/phpunit           # Unit, Feature and Contract suites
```

PHPStan runs at level max over the whole codebase, tests included, with no baseline: every error fails the build. `phpstan/phpstan-phpunit` lets assertions such as `assertIsString()` narrow types, which is how the tests handle JSON values (decoded as `mixed`).

---

## Development Workflow

* **`master` is protected**: it only changes through pull requests, and only when every CI check is green.
* **Work happens on `develop`** (or on feature branches created from it). When it is ready, open a pull request from `develop` to `master`.
* **CI** (`.github/workflows/ci.yml`) runs on every push and pull request to `master` and `develop`, with three jobs:
  * `quality`: Pint, PHPStan and `composer audit`;
  * `tests`: the PHPUnit suites with a coverage report (pcov), uploaded as an artifact;
  * `docker`: builds the production image, starts it with an injected `PORT` and smoke-tests the running API.

---

## Deploy (Docker / Render)

The image (`Dockerfile`) uses `php:8.3-apache`, installs production dependencies only and listens on the port set by the `PORT` variable (injected by Render; `8080` by default). At startup, `docker/start.sh` builds the config and route caches.

Defaults already set in the image: `APP_ENV=production`, `APP_DEBUG=false`, `LOG_CHANNEL=stderr`, `CACHE_STORE=file`, `SESSION_DRIVER=array`.

```bash
docker build -t criptografy-api .
docker run --rm -e PORT=8080 -p 8080:8080 criptografy-api
```

On Render: create a **Web Service** with the **Docker** runtime pointing to this repository, and use `/up` as the *Health Check Path*. The API uses no sessions or cookies, so `APP_KEY` is not required for the current setup.

---

## License

Released under the [MIT License](LICENSE).
