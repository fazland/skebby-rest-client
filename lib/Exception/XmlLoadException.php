<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Exception;

/**
 * Represents an exception thrown on XML load.
 *
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class XmlLoadException extends Exception
{
    /**
     * @var string
     */
    private $response;

    /**
     * XmlLoadException constructor.
     *
     * @param string $response
     * @param array  $errors
     */
    public function __construct(string $response, array $errors)
    {
        $this->response = $response;
        $this->message = '';

        foreach ($errors as $error) {
            $this->message .= $this->decodeXmlError($error, $response);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return '['.get_class($this).'] '.$this->message."\n".
            'Response: '."\n".
            $this->response
        ;
    }

    /**
     * @param \LibXMLError $error
     * @param string       $xml
     *
     * @return string
     */
    private function decodeXmlError(\LibXMLError $error, string $xml): string
    {
        $return = $xml[$error->line - 1]."\n";

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

        $return .= trim($error->message).
            "\n  Line: $error->line".
            "\n  Column: $error->column";

        if ($error->file) {
            $return .= "\n  File: $error->file";
        }

        return $return."\n\n";
    }
}
