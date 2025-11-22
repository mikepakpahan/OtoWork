<?php
require '../../../backend/config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../login/login-page.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT 
            s.id AS product_id, 
            s.part_name, 
            s.price, 
            s.image_url,
            c.quantity,
            c.id AS cart_id
        FROM carts c
        JOIN spareparts s ON c.sparepart_id = s.id
        WHERE c.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Efka Workshop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F4F6F8; color: #333; margin: 0; }
        .cart-page-container { max-width: 1100px; margin: 2rem auto; padding: 1rem; }
        .cart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .cart-header .logo { display: flex; align-items: center; gap: 1rem; }
        .cart-header .logo img { height: 40px; }
        .cart-header .logo h1 { font-size: 1.5rem; font-weight: 700; margin: 0; }
        .cart-header .search-bar { width: 300px; position: relative; }
        .cart-header .search-bar input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; }
        .cart-header .search-bar .search-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; }
        .cart-table-wrapper { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .cart-table { width: 100%; border-collapse: collapse; }
        .cart-table th, .cart-table td { text-align: left; padding: 1rem; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        .cart-table th { font-weight: 600; color: #888; text-transform: uppercase; font-size: 0.8rem; }
        .product-cell { display: flex; align-items: center; gap: 1rem; }
        .product-cell img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
        .quantity-stepper { display: flex; align-items: center; border: 1px solid #ddd; border-radius: 6px; }
        .quantity-stepper button { background: #f5f5f5; border: none; padding: 0.5rem 0.75rem; cursor: pointer; font-size: 1rem; line-height: 1; }
        .quantity-stepper input { width: 40px; text-align: center; border: none; border-left: 1px solid #ddd; border-right: 1px solid #ddd; padding: 0.5rem 0; }
        .quantity-stepper input:focus { outline: none; }
        .delete-btn { background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 1.2rem; }
        .cart-summary { display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; padding: 1.5rem; background: #fff; border-radius: 12px; }
        .checkout-btn { background-color: #FFC20E; color: #1F2937; padding: 1rem 2rem; border: none; border-radius: 8px; font-weight: 700; font-size: 1rem; cursor: pointer; }
        .grand-total { font-size: 1.5rem; font-weight: 700; }
        .grand-total span { font-size: 0.9rem; font-weight: 400; color: #888; }
        .empty-cart { text-align: center; padding: 50px; }
        input[type="checkbox"] { width: 20px; height: 20px; }
    </style>
</head>
<body>

<div class="cart-page-container">
    <header class="cart-header">
        <div class="logo">
            <img src="/EfkaWorkshop/assets/logo-efka.png" alt="EFKA Workshop Logo">
            <h1>Shopping Cart</h1>
        </div>
        <div class="search-bar">
            <input type="text" placeholder="Search">
            <i class="fas fa-search search-icon"></i>
        </div>
    </header>

    <div class="cart-table-wrapper">
        <?php if (!empty($cart_items)): ?>
            <form id="checkout-form" action="../../../backend/proses_checkout.php" method="POST">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Produk</th>
                            <th>Harga Satuan</th>
                            <th>Kuantitas</th>
                            <th>Total Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr data-row-id="<?php echo $item['cart_id']; ?>">
                                <td>
                                    <input type="checkbox" class="item-checkbox" name="cart_ids[]" value="<?php echo $item['cart_id']; ?>" 
                                           data-price="<?php echo $item['price']; ?>" data-quantity="<?php echo $item['quantity']; ?>" checked>
                                </td>
                                <td>
                                    <div class="product-cell">
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['part_name']); ?>">
                                        <span><?php echo htmlspecialchars($item['part_name']); ?></span>
                                    </div>
                                </td>
                                <td class="unit-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td>
                                    <div class="quantity-stepper">
                                        <button type="button" class="qty-btn minus" data-cartid="<?php echo $item['cart_id']; ?>">-</button>
                                        <input type="text" class="qty-input" value="<?php echo $item['quantity']; ?>" readonly>
                                        <button type="button" class="qty-btn plus" data-cartid="<?php echo $item['cart_id']; ?>">+</button>
                                    </div>
                                </td>
                                <td class="sub-total">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                <td>
                                    <button type="button" class="delete-btn" data-cartid="<?php echo $item['cart_id']; ?>"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        <?php else: ?>
            <div class="empty-cart">
                <h2>Keranjang belanja Anda masih kosong.</h2>
                <a href="/EfkaWorkshop/Pages/customer/spareparts/sparepart.php">Mulai Belanja Sekarang</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($cart_items)): ?>
    <div class="cart-summary">
        <button type="submit" form="checkout-form" class="checkout-btn">Checkout</button>
        <div class="grand-total">
            Total (<span id="total-product-count">0</span> Product) : <span id="grand-total-price">Rp 0</span>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('.cart-table tbody');
    const selectAllCheckbox = document.getElementById('select-all');

    function updateTotal() {
        let grandTotal = 0;
        let totalItems = 0;
        const itemCheckboxes = document.querySelectorAll('.item-checkbox:checked');
        
        itemCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const price = parseFloat(checkbox.dataset.price);
            const quantity = parseInt(row.querySelector('.qty-input').value);
            grandTotal += price * quantity;
            totalItems += quantity;
        });

        document.getElementById('grand-total-price').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        document.getElementById('total-product-count').textContent = itemCheckboxes.length;
    }

    function updateCartOnServer(cartId, newQuantity) {
        const formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('quantity', newQuantity);

        fetch('../../../backend/update_cart_quantity.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(data => {
            if (data.status !== 'success') {
                alert('Gagal memperbarui keranjang.');
            }
        });
    }

    if(tableBody) {
        tableBody.addEventListener('click', function(e) {
            const target = e.target;
            const row = target.closest('tr');
            if (!row) return;

            const cartId = row.dataset.rowId;
            const unitPrice = parseFloat(row.querySelector('.item-checkbox').dataset.price);
            const qtyInput = row.querySelector('.qty-input');
            let currentQty = parseInt(qtyInput.value);

            if (target.classList.contains('plus')) {
                currentQty++;
                qtyInput.value = currentQty;
                updateCartOnServer(cartId, currentQty);
            }
            if (target.classList.contains('minus') && currentQty > 1) {
                currentQty--;
                qtyInput.value = currentQty;
                updateCartOnServer(cartId, currentQty);
            }
            if (target.classList.contains('delete-btn') || target.closest('.delete-btn')) {
                if (confirm('Yakin ingin menghapus item ini dari keranjang?')) {
                    const formData = new FormData();
                    formData.append('cart_id', cartId);
                    fetch('../../../backend/remove_from_cart.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => response.json()).then(data => {
                        if (data.status === 'success') {
                            row.remove();
                            updateTotal();
                        } else {
                            alert(data.message || 'Gagal menghapus item.');
                        }
                    });
                }
            }

            row.querySelector('.sub-total').textContent = 'Rp ' + (unitPrice * currentQty).toLocaleString('id-ID');
            updateTotal();
        });
    }

    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateTotal);
    });

    if(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateTotal();
        });
    }
    
    updateTotal();
});
</script>

</body>
</html>