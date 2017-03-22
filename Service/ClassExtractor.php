<?php

namespace BowlOfSoup\NormalizerBundle\Service;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionProperty;

class ClassExtractor
{
    /** @var string */
    const TYPE = 'class';

    /** @var bool */
    const GET_PROPERTIES_ONLY_PRIVATES = true;

    /** @var Reader */
    protected $annotationReader;

    /**
     * @param \Doctrine\Common\Annotations\Reader $annotationReader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Extract annotations set on class level.
     *
     * @param object $object
     * @param string $annotation
     *
     * @return array
     */
    public function extractClassAnnotations($object, $annotation)
    {
        $annotations = array();
        $reflectedClass = new ReflectionClass($object);

        $classAnnotations = $this->annotationReader->getClassAnnotations($reflectedClass);
        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation instanceof $annotation) {
                $annotations[] = $classAnnotation;
            }
        }

        return $annotations;
    }

    /**
     * Get all properties for a given class.
     *
     * @param object $object
     *
     * @return \ReflectionProperty[]|array
     */
    public function getProperties($object)
    {
        if (!is_object($object)) {
            return array();
        }

        $reflectedClass = new ReflectionClass($object);
        $classProperties = $this->getClassProperties($reflectedClass);

        // Also get (private) variables from parent class.
        while ($reflectedClass = $reflectedClass->getParentClass()) {
            $classProperties = array_merge(
                $classProperties,
                $this->getClassProperties($reflectedClass, static::GET_PROPERTIES_ONLY_PRIVATES)
            );
        }

        return $classProperties;
    }

    /**
     * Get class properties through reflection.
     *
     * @param \ReflectionClass $reflectedClass
     * @param bool             $onlyPrivates
     *
     * @return \ReflectionProperty[]
     */
    private function getClassProperties(ReflectionClass $reflectedClass, $onlyPrivates = false)
    {
        if ($onlyPrivates) {
            return $reflectedClass->getProperties(ReflectionProperty::IS_PRIVATE);
        }

        return $reflectedClass->getProperties(
            ReflectionProperty::IS_PUBLIC |
            ReflectionProperty::IS_PROTECTED |
            ReflectionProperty::IS_PRIVATE
        );
    }
}
