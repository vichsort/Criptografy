<?php

namespace App\Cryptography\Contracts;

use InvalidArgumentException;

/**
 * Base class for each algorithm's invalid option/key exceptions
 * (e.g. CaesarException, VigenereException).
 *
 * It lets the rest of the application (e.g. the HTTP error handling in
 * bootstrap/app.php) react uniformly to "invalid input for this algorithm"
 * without knowing every concrete exception.
 */
abstract class InvalidAlgorithmOptionsException extends InvalidArgumentException {}
