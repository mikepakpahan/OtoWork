<?php
$pageTitle = 'Sparepart Order';
$activeMenu = 'order';

require '../../../backend/config.php';
include '../template-header.php';
include '../template-sidebar.php';

$sql = "SELECT 
            o.id AS order_id,
            o.total_amount,
            o.order_date,
            o.status,
            u.name AS user_name,
            u.email AS user_email,
            oi.quantity,
            oi.price_at_purchase,
            s.part_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN spareparts s ON oi.sparepart_id = s.id
        WHERE o.status = 'processing'
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);

$orders_data = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $order_id = $row['order_id'];

        if (!isset($orders_data[$order_id])) {
            $orders_data[$order_id] = [
                "user_info" => [
                    "name" => $row['user_name'],
                    "email" => $row['user_email']
                ],
                "order_details" => [
                    "date" => $row['order_date'],
                    "total" => $row['total_amount'],
                    "status" => $row['status']
                ],
                "products" => [] 
            ];
        }
        
        $orders_data[$order_id]['products'][] = [
            "name" => $row['part_name'],
            "qty" => $row['quantity'],
            "price" => $row['price_at_purchase']
        ];
    }
}
?>
<link rel="stylesheet" href="style.css"> <link rel="stylesheet" href="../style.css">

<div class="main-content">
    <div class="order-container">
        <div class="order-list">
            <?php
            if (!empty($orders_data)):
                foreach ($orders_data as $order_id => $order):
            ?>
                    <div class="order-card">
                        <div class="profile">
                            <img src="/EfkaWorkshop/assets/icons/user.png" alt="profile">
                            <div>
                                <div class="profile-name"><?php echo htmlspecialchars($order["user_info"]["name"]); ?></div>
                                <div class="profile-email"><?php echo htmlspecialchars($order["user_info"]["email"]); ?></div>
                                <div class="profile-email" style="font-size: 0.8em; color: #888;">Order Date: <?php echo date('d M Y, H:i', strtotime($order["order_details"]["date"])); ?></div>
                            </div>
                        </div>

                        <table>
                            <tr>
                                <td><strong>Nama Produk</strong></td>
                                <td style="text-align:center;"><strong>Jumlah</strong></td>
                                <td style="text-align:right;"><strong>Harga Satuan</strong></td>
                            </tr>
                            <?php foreach ($order["products"] as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td style="text-align:center;"><?php echo $product['qty']; ?></td>
                                    <td style="text-align:right;">Rp<?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="2"><strong>Total Pesanan</strong></td>
                                <td style="text-align:right;"><strong>Rp<?php echo number_format($order["order_details"]["total"], 0, ',', '.'); ?></strong></td>
                            </tr>
                        </table>

                        <div class="buttons">
                            <button class="btn-accept" data-orderid="<?php echo $order_id; ?>">Accept</button>
                            <button class="btn-delete" data-orderid="<?php echo $order_id; ?>">Delete</button>
                        </div>
                    </div>
            <?php 
                endforeach;
            else:
                echo "<p>Tidak ada pesanan sparepart yang perlu diproses saat ini.</p>";
            endif;
            ?>
        </div>
    </div>
</div>

<script src="../script.js"></script>
<script src="script.js"></script>