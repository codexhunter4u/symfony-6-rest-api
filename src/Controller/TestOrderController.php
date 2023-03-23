<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestOrderController extends AbstractController
{
    private SerializerInterface $serializer;

    /**
     * @param KernelInterface $appKernel
     */
    public function __construct(SerializerInterface $serializer) {
        $this->serializer = $serializer;
    }

    #[Route('/order_data', name: 'gtorder', methods: 'GET')]
    public function index(): Response
    {
        $orders = [];
        $projectRoot = $this->getParameter('kernel.project_dir');
        $file = fopen($projectRoot . '/data/order.json', 'r');
        while ($line = fgets($file)) {
            $order = json_decode($line, true);
            $orders[] = $order;
        }
        fclose($file);
        
        // Transform the data into the required format
        $formattedOrders = [];
        dd($orders);
        foreach ($orders as $order) {
            dd($order);
            $formattedOrder = [
                'order_id' => $order['id'],
                'order_date' => date('d/m/Y', strtotime($order['order_date'])),
                'total_order_value' => $order['items_total'] - $order['discount'],
                'average_unit_price' => $order['items_total'] / $order['units_total'],
                'unit_count' => $order['units_total'],
                'customer_state' => $order['shipping_state'],
            ];
            $formattedOrders[] = $formattedOrder;
        }
        
        // Serialize the data into JSON format and return the response
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $data = $this->serializer->serialize($formattedOrders, 'json', ['json_encode_options' => JSON_PRETTY_PRINT]);

        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }
}
