<?php
declare(strict_types=1);
namespace Pccomponentes\DddLogging\Tests\Unit\Tracker;

use PHPUnit\Framework\TestCase;
use Pccomponentes\DddLogging\Tracker\RequestTracker;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestTrackerTest extends TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function given_parameters_to_build_when_attribute_type_not_among_the_accepted_ones_then_throws_exception()
    {
        new RequestTracker(
            $this->createMock(RequestStack::class),
            'wrong_type',
            'any_name',
            'default value'
        );
    }

    /**
     * @test
     */
    public function given_request_stack_with_null_request_when_get_parent_operation_id_then_default_value_is_returned()
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $returnedValue = (new RequestTracker(
            $requestStack,
            'header',
            'any_name',
            'default value'
        ))->parentOperationId();

        $this->assertEquals('default value', $returnedValue);
    }

    /**
     * @test
     */
    public function given_request_stack_with_value_in_header_when_get_value_then_header_get_is_called()
    {
        $bagMock = $this->getParametersBagMock('custom value');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(
                new class ($bagMock) {
                    public $headers;

                    public function __construct($headers)
                    {
                        $this->headers = $headers;
                    }
                }
            );

        (new RequestTracker(
            $requestStack,
            'header',
            'any_name',
            'default value'
        ))->parentOperationId();
    }

    /**
     * @test
     */
    public function given_request_stack_with_value_in_request_when_get_value_then_requests_get_is_called()
    {
        $bagMock = $this->getParametersBagMock('custom value');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(
                new class ($bagMock) {
                    public $request;

                    public function __construct($request)
                    {
                        $this->request = $request;
                    }
                }
            );

        (new RequestTracker(
            $requestStack,
            'request',
            'any_name',
            'default value'
        ))->parentOperationId();
    }

    /**
     * @test
     */
    public function given_request_stack_with_value_in_attribute_when_get_value_then_attribute_get_is_called()
    {
        $bagMock = $this->getParametersBagMock('custom value');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(
                new class ($bagMock) {
                    public $attributes;

                    public function __construct($attributes)
                    {
                        $this->attributes = $attributes;
                    }
                }
            );

        (new RequestTracker(
            $requestStack,
            'attribute',
            'any_name',
            'default value'
        ))->parentOperationId();
    }

    /**
     * @test
     */
    public function given_request_stack_with_value_in_query_when_get_value_then_query_get_is_called()
    {
        $bagMock = $this->getParametersBagMock('custom value');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(
                new class ($bagMock) {
                    public $query;

                    public function __construct($query)
                    {
                        $this->query = $query;
                    }
                }
            );

        (new RequestTracker(
            $requestStack,
            'query',
            'any_name',
            'default value'
        ))->parentOperationId();
    }

    private function getParametersBagMock($returnValue)
    {
        $parameterBagMock = $this->createMock(ParameterBag::class);
        $parameterBagMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($returnValue)
        ;

        return $parameterBagMock;
    }
}
