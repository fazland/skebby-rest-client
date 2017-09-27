<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Exception;

class XmlLoadException extends Exception
{
    /**
     * @var string
     */
    private $response;

    public function __construct($response, array $errors)
    {
        $this->response = $response;
        $this->message = '';

        foreach ($errors as $error) {
            $this->message .= $this->decodeXmlError($error, $response);
        }
    }

    public function __toString()
    {
        return '[' . get_class($this) . '] ' . $this->message . "\n" .
            'Response: ' . "\n" .
            $this->response
        ;
    }

    private function decodeXmlError(\LibXMLError $error, $xml)
    {
        $return = $xml[$error->line - 1] . "\n";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $error->code: ";
                break;

            case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;

            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $error->code: ";
                break;
        }

        $return .= trim($error->message) .
            "\n  Line: $error->line" .
            "\n  Column: $error->column";

        if ($error->file) {
            $return .= "\n  File: $error->file";
        }

        return $return . "\n\n";
    }
}
