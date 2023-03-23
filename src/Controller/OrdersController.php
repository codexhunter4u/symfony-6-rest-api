<?php

declare(strict_types=1);

namespace App\Controller;

use League\Csv\Reader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/order')]
class OrdersController extends AbstractController
{

    /**
     * @param KernelInterface $appKernel
     */
    public function __construct(KernelInterface $appKernel) {}

    #[Route('/all', name: 'all_orders', methods: 'GET')]
    public function all(): JsonResponse
    {
        $orders = $this->getOrders();

        return $this->json($orders, 200);
    }

    #[Route('/order/{orderId}/', name: 'single_order', methods: ['GET'])]
    public function getOrder(int $orderId): JsonResponse
    {
        $orders = $this->getOrders($orderId);

        return $this->json($orders, 200);
    }

    /**
     * @param int|null $orderId
     * @return array
     */
    public function getOrders(?int $orderId = null): array
    {
        $basePath = $this->getParameter('kernel.project_dir');
        $json = file_get_contents($basePath . '/data/order.json');
        $data = json_decode($json, true);
        $orders = [];
        foreach ($data as $order) {
            $itemDetails = $this->getItemDetails($order['items']);
            $customer = $order['customer'];
            $orders[] = [
                'order_id' => $order['order_id'],
                'order_date' => $order['order_datetime'],
                'total_order_value' => $itemDetails['total_price'],
                'average_unit_price' => $itemDetails['total_price'] / $itemDetails['total_units'],
                'unit_count' => $itemDetails['total_units'],
                'customer_state' => array_key_exists('shipping_address', $customer) ?
                    $customer['shipping_address']['state'] :
                    '',
            ];
        }

        return (null === $orderId) ? $orders : $this->getSingleOrder($orderId, $orders);
    }


    function getSingleOrder(int $orderId, array $orders)
    {
        foreach ($orders as $key => $order) {
            if ((int) $order['order_id'] === $orderId) {
                return $order;
            }
        }

        return ['Message' => 'No order found for - ' . $orderId];
    }

    /**
     * Get the total order value and average value
     *
     * @param array $items
     * @return array
     */
    public function getItemDetails(array $items): array
    {
        $unitPrice = 0;
        $totalPrice = 0;
        $totalUnits = 0;

        foreach ($items as $item) {
            $unitPrice = $item['unit_price'] * $item['quantity'];
            $totalPrice += $unitPrice;
            $totalUnits += $item['quantity'];
        }

        return [
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'total_units' => $totalUnits
        ];
    }
}
