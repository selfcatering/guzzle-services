<?php
namespace GuzzleHttp\Command\Guzzle\ResponseLocation;

use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Command\Result;
use GuzzleHttp\Command\ResultInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Extracts elements from a JSON document.
 */
class JsonLocation extends AbstractLocation
{
    /** @var array The JSON document being visited */
    private $json = [];

    /**
     * Set the name of the location
     *
     * @param string $locationName
     */
    public function __construct($locationName = 'json')
    {
        parent::__construct($locationName);
    }

    /**
     * @param \GuzzleHttp\Command\ResultInterface  $result
     * @param \Psr\Http\Message\ResponseInterface  $response
     * @param \GuzzleHttp\Command\Guzzle\Parameter $model
     *
     * @return \GuzzleHttp\Command\ResultInterface
     */
    public function before(
        ResultInterface $result,
        ResponseInterface $response,
        Parameter $model
    ) {
        $body = (string) $response->getBody();
        $body = $body ?: "{}";
        $this->json = \GuzzleHttp\json_decode($body, true);

        // wrap single item arrays with an array
        $this->json = $this->wrapSingleItemArrays($this->json);

        // relocate named arrays, so that they have the same structure as
        //  arrays nested in objects and visit can work on them in the same way
        if ($model->getType() === 'array' && ($name = $model->getName())) {
            $this->json = [$name => $this->json];
        }

        return $result;
    }

    /**
     * @param ResultInterface $result
     * @param ResponseInterface $response
     * @param Parameter $model
     * @return ResultInterface
     */
    public function after(
        ResultInterface $result,
        ResponseInterface $response,
        Parameter $model
    ) {
        // Handle additional, undefined properties
        $additional = $model->getAdditionalProperties();
        if (!($additional instanceof Parameter)) {
            return $result;
        }

        // Use the model location as the default if one is not set on additional
        $addLocation = $additional->getLocation() ?: $model->getLocation();
        if ($addLocation == $this->locationName) {
            foreach ($this->json as $prop => $val) {
                if (!isset($result[$prop])) {
                    // Only recurse if there is a type specified
                    $result[$prop] = $additional->getType()
                        ? $this->recurse($additional, $val)
                        : $val;
                }
            }
        }

        $this->json = [];

        return $result;
    }

    /**
     * @param ResultInterface $result
     * @param ResponseInterface $response
     * @param Parameter $param
     * @return Result|ResultInterface
     */
    public function visit(
        ResultInterface $result,
        ResponseInterface $response,
        Parameter $param
    ) {
        $name = $param->getName();
        $key = $param->getWireName();

        // Check if the result should be treated as a list
        if ($param->getType() == 'array') {
            // Treat as javascript array
            if ($name) {
                // name provided, store it under a key in the array
                $subArray = isset($this->json[$name]) ? $this->json[$name] : null;
                $result[$name] = $this->recurse($param, $subArray);
            } else {
                // top-level `array` or an empty name
                $result = new Result(array_merge(
                    $result->toArray(),
                    $this->recurse($param, $this->json)
                ));
            }
        } elseif (isset($this->json[$key])) {
            $result[$name] = $this->recurse($param, $this->json[$key]);
        }

        return $result;
    }

    /**
     * Recursively process a parameter while applying filters
     *
     * @param Parameter $param API parameter being validated
     * @param mixed     $value Value to process.
     * @return mixed|null
     */
    private function recurse(Parameter $param, $value)
    {
        if (!is_array($value)) {
            return $param->filter($value);
        }

        $result = [];
        $type = $param->getType();

        if ($type == 'array') {
            $items = $param->getItems();
            foreach ($value as $val) {
                $result[] = $this->recurse($items, $val);
            }
        } elseif ($type == 'object' && !isset($value[0])) {
            // On the above line, we ensure that the array is associative and
            // not numerically indexed
            if ($properties = $param->getProperties()) {
                foreach ($properties as $property) {
                    $key = $property->getWireName();
                    if (array_key_exists($key, $value)) {
                        $result[$property->getName()] = $this->recurse(
                            $property,
                            $value[$key]
                        );
                        // Remove from the value so that AP can later be handled
                        unset($value[$key]);
                    }
                }
            }
            // Only check additional properties if everything wasn't already
            // handled
            if ($value) {
                $additional = $param->getAdditionalProperties();
                if ($additional === null || $additional === true) {
                    // Merge the JSON under the resulting array
                    $result += $value;
                } elseif ($additional instanceof Parameter) {
                    // Process all child elements according to the given schema
                    foreach ($value as $prop => $val) {
                        $result[$prop] = $this->recurse($additional, $val);
                    }
                }
            }
        }

        return $param->filter($result);
    }

    /**
     * Single item arrays are not nested within arrays
     * so wrap them to retain the same structure.
     * Since the array is being iterated process changes by
     * storing array children within a temporary key
     * and reiterating array replacing original leafs
     * with temporary elements
     */
    private function wrapSingleItemArrays(&$array)
    {
        $this->initialSingleItemArrays($array);

        return $this->cleanUpSingleItemArrays($array);
    }

    /**
     * Create temporary array elements for single-child arrays
     */
    private function initialSingleItemArrays(&$array)
    {
        foreach ($array as $key => $value) {
            if (gettype($value) === 'array') {
                $array[$key] = $this->initialSingleItemArrays($value);
            }

            // size found so find the only array
            // element that's within current leaf
            if ($key === '@size') {
                foreach ($array as $key => $value) {
                    if (gettype($value) === 'array' && ! isset($value[0])) {
                        // wrap single item element with an array
                        $array[$key . '__temporary'] = [
                            $this->initialSingleItemArrays($value)
                        ];
                        unset($array[$key]);
                    }
                }
            }
        }

        return $array;
    }

    /**
     * Convert temporary leafs into original array elements
     */
    private function cleanUpSingleItemArrays(&$array)
    {
        foreach ($array as $key => $value) {
            if (gettype($value) === 'array') {
                $array[$key] = $this->cleanUpSingleItemArrays($value);
            }

            if (gettype($key) === 'string' &&  stripos($key, '__temporary') !== false) {
                $originalKey = str_replace('__temporary', '', $key);
                $array[$originalKey] = $array[$key];
                unset($array[$key]);
            }
        }

        return $array;
    }
}
