<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Exception;

use LibXMLError;

use function sprintf;
use function trim;

use const LIBXML_ERR_ERROR;
use const LIBXML_ERR_FATAL;
use const LIBXML_ERR_WARNING;

/**
 * Represents an exception thrown on XML load.
 */
class XmlLoadException extends Exception
{
    private string $response;

    /**
     * @param LibXMLError[] $errors
     */
    public function __construct(string $response, array $errors)
    {
        parent::__construct();

        $this->response = $response;
        $this->message = '';

        foreach ($errors as $error) {
            $this->message .= $this->decodeXmlError($error, $response);
        }
    }

    public function __toString(): string
    {
        return '[' . static::class . '] ' . $this->message . "\n" .
            'Response: ' . "\n" .
            $this->response;
    }

    private function decodeXmlError(LibXMLError $error, string $xml): string
    {
        $return = $xml[$error->line - 1] . "\n";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= sprintf('Warning %u: ', $error->code);
                break;

            case LIBXML_ERR_ERROR:
                $return .= sprintf('Error %u: ', $error->code);
                break;

            case LIBXML_ERR_FATAL:
                $return .= sprintf('Fatal Error %u: ', $error->code);
                break;
        }

        $return .= sprintf("%s\n  Line: %u\n  Column: %u ", trim($error->message), $error->line, $error->column);
        if ($error->file) {
            $return .= sprintf("\n  File: %s", $error->file);
        }

        return $return . "\n\n";
    }
}
