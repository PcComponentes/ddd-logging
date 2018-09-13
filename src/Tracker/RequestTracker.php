<?php
declare(strict_types=1);
namespace Pccomponentes\DddLogging\Tracker;

use Pccomponentes\DddLogging\Tracker;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestTracker implements Tracker
{
    private const HEADER = 'header';
    private const REQUEST = 'request';
    private const QUERY = 'query';
    private const ATTRIBUTE = 'query';

    private $requestStack;
    private $attributeType;
    private $attributeName;
    private $default;

    public function __construct(
        RequestStack $requestStack,
        string $attributeType,
        string $attributeName,
        string $default
    ) {
        if (false === \in_array($attributeType, [self::HEADER, self::REQUEST, self::QUERY, self::ATTRIBUTE], true)) {
            throw new \InvalidArgumentException(sprintf('Unknow tracker type %s', $attributeType));
        }
        $this->requestStack = $requestStack;
        $this->attributeType = $attributeType;
        $this->attributeName = $attributeName;
        $this->default = $default;
    }

    public function parentOperationId(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return $this->default;
        }

        switch ($this->attributeType) {
            case self::HEADER:
                return $request->headers->get($this->attributeName, $this->default);
            case self::ATTRIBUTE:
                return $request->attributes->get($this->attributeName, $this->default);
            case self::REQUEST:
                return $request->request->get($this->attributeName, $this->default);
            case self::QUERY:
            default:
                return $request->query->get($this->attributeName, $this->default);
        }
    }
}
