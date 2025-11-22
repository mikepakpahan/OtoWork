<?php
require '../vendor/autoload.php';
require 'backend/config.php';

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

// Wajib login & ada order_id
if (!isset($_SESSION['logged_in'])) { die("Silakan login terlebih dahulu."); }
if (!isset($_GET['order_id'])) { die("Order tidak ditemukan."); }

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

$sql_order = "SELECT o.id, o.total_amount, o.order_date, o.completion_token, u.name 
              FROM orders o JOIN users u ON o.user_id = u.id 
              WHERE o.id = ? AND o.user_id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("ii", $order_id, $user_id);
$stmt_order->execute();
$order_result = $stmt_order->get_result();
if ($order_result->num_rows === 0) { die("Order tidak valid atau bukan milik Anda."); }
$order = $order_result->fetch_assoc();
$sql_items = "SELECT oi.quantity, oi.price_at_purchase, s.part_name FROM order_items oi JOIN spareparts s ON oi.sparepart_id = s.id WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);

$qr_url = "http://192.168.100.110/EfkaWorkshop/mark_as_complete.php?token=" . $order['completion_token'];

$renderer = new ImageRenderer(
    new RendererStyle(300),
    new SvgImageBackEnd() 
);
$writer = new Writer($renderer);

$qr_code_svg_string = $writer->writeString($qr_url);  
$qr_code_data_uri = 'data:image/svg+xml;base64,' . base64_encode($qr_code_svg_string);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout Berhasil - Efka Workshop</title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #e9e9e9; display: flex; justify-content: center; align-items: center; padding: 2rem; }
        .invoice-box { display: flex; max-width: 800px; background: white; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .invoice-details { padding: 30px; width: 65%; }
        .invoice-header { display: flex; align-items: center; gap: 1rem; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px;}
        .invoice-header img { height: 40px; }
        .invoice-header h2 { margin: 0; }
        .invoice-table { width: 100%; border-collapse: collapse; }
        .invoice-table td { padding: 8px 0; border-bottom: 1px dotted #ccc; }
        .invoice-table .total-row td { border-bottom: 2px solid #333; font-weight: bold; padding-top: 15px; }
        .qr-code-section { width: 35%; background: #f4f4f4; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px; text-align: center; }
        .qr-code-section p { font-weight: bold; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="invoice-details">
            <div class="invoice-header">
                <img src="/EfkaWorkshop/assets/logo-efka.png" alt="Logo">
                <h2>Checkout</h2>
            </div>
            <p>Terima kasih, <strong><?php echo htmlspecialchars($order['name']); ?></strong>! Pesanan Anda telah kami terima.</p>
            <p>Silakan tunjukkan QR Code di samping kepada kasir kami saat pengambilan barang.</p>
            <table class="invoice-table">
                <tr>
                    <td><strong>Produk</strong></td>
                    <td style="text-align:center;"><strong>Kuantitas</strong></td>
                    <td style="text-align:right;"><strong>Total Harga</strong></td>
                </tr>
                <?php 
                $total_qty = 0;
                foreach ($items as $item): 
                    $total_qty += $item['quantity'];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['part_name']); ?></td>
                    <td style="text-align:center;"><?php echo $item['quantity']; ?></td>
                    <td style="text-align:right;">Rp <?php echo number_format($item['price_at_purchase'] * $item['quantity'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td><strong>Total Jumlah dan Harga Produk :</strong></td>
                    <td style="text-align:center;"><?php echo $total_qty; ?></td>
                    <td style="text-align:right;">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                </tr>
            </table>
        </div>
        <div class="qr-code-section">
            <img src="<?php echo $qr_code_data_uri; ?>" alt="QR Code Konfirmasi">
            <p>Order ID: EFKA-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></p>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil order ID dari URL halaman ini
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('order_id');

        // Jika ada order ID, mulai proses polling
        if (orderId) {
            // Set interval untuk menjalankan fungsi setiap 3000 milidetik (3 detik)
            const pollingInterval = setInterval(function() {
                
                // Tanya ke server apa status order ini
                fetch(`backend/check_order_status.php?order_id=${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Checking status...', data.status);

                        // Jika server menjawab 'completed'...
                        if (data.status === 'completed') {
                            // 1. Hentikan proses bertanya lagi
                            clearInterval(pollingInterval);

                            // 2. Beri alert dan arahkan ke halaman lain
                            alert('Pesanan telah diselesaikan! Terima kasih.');
                            window.location.href = 'index.php'; // Arahkan ke halaman utama
                        }
                    })
                    .catch(error => {
                        console.error('Polling error:', error);
                        // Hentikan bertanya jika ada error
                        clearInterval(pollingInterval);
                    });

            }, 3000); // Interval 3 detik
        }
    });
    </script>

    </body>
    </html>
</body>
</html>