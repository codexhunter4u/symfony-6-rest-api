<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\OrdersController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OrdersControllerTest extends TestCase
{
    private $kernel;
    
    private $controller;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->kernel = $this->createMock(KernelInterface::class);
        $this->controller = new OrdersController($this->kernel);
    }

    /**
     * @return void
     */
    public function testIndex(): void
    {
        $response = $this->controller->getOrders();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $orders = json_decode($response->getContent(), true);
        $this->assertIsArray($orders);
        $this->assertNotEmpty($orders);

        foreach ($orders as $order) {
            $this->assertArrayHasKey('order_id', $order);
            $this->assertArrayHasKey('order_date', $order);
            $this->assertArrayHasKey('total_order_value', $order);
            $this->assertArrayHasKey('average_unit_price', $order);
            $this->assertArrayHasKey('unit_count', $order);
            $this->assertArrayHasKey('customer_state', $order);
            $this->assertIsNumeric($order['order_id']);
            $this->assertIsString($order['order_date']);
            $this->assertIsNumeric($order['total_order_value']);
            $this->assertIsNumeric($order['average_unit_price']);
            $this->assertIsNumeric($order['unit_count']);
            $this->assertIsString($order['customer_state']);
        }
    }

    /**
     * @return void
     */
    public function testGetItemDetails(): void
    {
        $items = [
            [
                'unit_price' => 100,
                'quantity' => 2,
            ],
            [
                'unit_price' => 50,
                'quantity' => 3,
            ],
        ];

        $expectedResult = [
            'unit_price' => 150,
            'total_price' => 350,
            'total_units' => 5,
        ];

        $result = $this->controller->getItemDetails($items);
        $this->assertIsArray($result);
        $this->assertEquals($expectedResult, $result);
    }
}
