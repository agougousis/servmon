<?php

namespace App\Packages\Gougousis\Transformers;

use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Manager;

/**
 * Transforms API response before sending
 *
 * Al responses are transformed to array structures using one of the
 * predefined transformers of this package.
 *
 * @author Alexandros Gougousis
 */
class Transformer
{
    protected $fractal;
    protected $defaultTransformerClass;

    public function __construct($defaultTransformer)
    {
        $this->fractal = new Manager();
        $this->fractal->setSerializer(new DataArraySerializer());
        $this->defaultTransformerClass = $defaultTransformer;
    }

    /**
     * Executes the transformation
     *
     * @param mixed $items
     * @param string $transformerClass
     * @return array
     * @throws \Exception
     */
    public function transform($items, $transformerClass = null)
    {
        if (empty($transformerClass)) {
            // Use the default transformer
            $fullTransformerClass = "App\Packages\Gougousis\Transformers\\".$this->defaultTransformerClass;
            $transformer = new $fullTransformerClass;
        } else {
            // Use the transformer class provided (if  exists)
            $fullTransformerClass = "App\Packages\Gougousis\Transformers\\$transformerClass";
            if (!class_exists($fullTransformerClass)) {
                throw new \Exception("Transformer class '$fullTransformerClass' does not exist!");
            }
            $transformer = new $fullTransformerClass;
        }

        // Object parameter
        if (is_object($items)) {
            $className = get_class($items);
            switch ($className) {
                case 'Illuminate\Database\Eloquent\Collection':
                case 'Baum\Extensions\Eloquent\Collection':
                    $fractalCollection = new FractalCollection($items, $transformer);
                    break;
                case 'stdClass':
                case 'App\User':
                case (preg_match('/App\\\\Models.*/', $className)? true : false):
                    $fractalCollection = new FractalItem($items, $transformer);
                    break;
                default:
                    throw new \Exception("Unexpected class name '$className' as parameter!");
                    break;
            }
            return $this->fractal->createData($fractalCollection)->toArray();
        }

        // Array parameter
        if (is_array($items)) {
            $fractalCollection = new FractalCollection($items, $transformer);
            return $this->fractal->createData($fractalCollection)->toArray();
        }

        throw new \Exception('Item to be transformed was neither an array nor an object!');
    }
}
