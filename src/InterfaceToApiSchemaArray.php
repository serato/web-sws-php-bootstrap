<?php

namespace Serato\SwsApp;

/**
 * Defines the methods required for an object to return an array representation
 * of it's data that conforms to a JSON specifications for an API endpoint.
 */
interface InterfaceToApiSchemaArray
{
    /**
     * Returns an array representation of the data that conforms to the
     * JSON schema. Can take an array `$options` of model-specific formatting
     * options that can alter the content or format of the returned array.
     *
     * @param array $options    Formatting options
     *
     * @return array
     */
    public function toApiSchemaArray(array $options = []): array;

    /**
     * Returns the path to a JSON file containing the schema definition for the model
     *
     * @return string
     */
    public function getSchemaFilePath(): string;

    /**
     * Returns the name of the schema definition for the model
     *
     * @return string
     */
    public function getSchemaDefintion(): string;
}
