<?php
require_once '../../config/database.php';

// Pastikan user login & ada data kiriman dari Cart
if (!isset($_SESSION['user_id']) || !isset($_POST['cart_ids'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_ids = $_POST['cart_ids']; // Array ID cart yang dipilih
$cart_ids_str = implode(',', array_map('intval', $cart_ids)); 

// Ambil detail barang yang mau dibeli
$sql = "SELECT c.id, c.quantity, s.part_name, s.price, s.image_url 
        FROM carts c JOIN spareparts s ON c.sparepart_id = s.id 
        WHERE c.id IN ($cart_ids_str) AND c.user_id = $user_id";
$result = $conn->query($sql);

$items = [];
$grand_total = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $grand_total += ($row['price'] * $row['quantity']);
}

if (empty($items)) {
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Checkout | OtoWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Override default radio button style untuk dark mode */
        input[type="radio"]:checked {
            background-color: #3B82F6;
            border-color: #3B82F6;
        }
    </style>
</head>

<body class="bg-[#0B0E17] text-gray-300 font-sans min-h-screen">

    <?php require_once '../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold text-white mb-8">Checkout Details</h1>

        <form action="<?= BASE_URL ?>/logic/checkout_master.php" method="POST" enctype="multipart/form-data" class="flex flex-col lg:flex-row gap-10">
            <input type="hidden" name="act" value="process_order">
            <input type="hidden" name="cart_ids_str" value="<?= $cart_ids_str ?>">

            <div class="w-full lg:w-2/3 space-y-8">
                
                <div class="bg-[#1F2937] p-6 rounded-lg border border-gray-700">
                    <h3 class="text-xl font-bold text-white mb-6 border-b border-gray-600 pb-2">Billing details</h3>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-white mb-1">First name *</label>
                                <input type="text" value="<?= explode(' ', $_SESSION['name'])[0] ?? '' ?>" class="w-full bg-[#111827] border border-gray-600 text-white rounded p-3 focus:outline-none focus:border-blue-500" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white mb-1">Last name *</label>
                                <input type="text" value="<?= explode(' ', $_SESSION['name'])[1] ?? '' ?>" class="w-full bg-[#111827] border border-gray-600 text-white rounded p-3 focus:outline-none focus:border-blue-500" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white mb-2">Shipping Option *</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="delivery_type" value="delivery" checked onclick="toggleDelivery('delivery')" class="peer sr-only">
                                    <div class="p-4 rounded border border-gray-600 bg-[#111827] peer-checked:border-blue-500 peer-checked:bg-blue-900/20 hover:bg-gray-800 transition text-center">
                                        <i class="fas fa-truck text-xl mb-1 text-gray-400 peer-checked:text-blue-500"></i>
                                        <div class="font-medium text-sm text-white">Delivery</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="delivery_type" value="pickup" onclick="toggleDelivery('pickup')" class="peer sr-only">
                                    <div class="p-4 rounded border border-gray-600 bg-[#111827] peer-checked:border-blue-500 peer-checked:bg-blue-900/20 hover:bg-gray-800 transition text-center">
                                        <i class="fas fa-store text-xl mb-1 text-gray-400 peer-checked:text-blue-500"></i>
                                        <div class="font-medium text-sm text-white">Local Pickup</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="address-input">
                            <label class="block text-sm font-medium text-white mb-1">Street address *</label>
                            <input type="text" name="address" id="inp-address" class="w-full bg-[#111827] border border-gray-600 text-white rounded p-3 mb-2 focus:outline-none focus:border-blue-500" placeholder="House number and street name">
                            <input type="text" class="w-full bg-[#111827] border border-gray-600 text-white rounded p-3 focus:outline-none focus:border-blue-500" placeholder="Apartment, suite, unit, etc. (optional)">
                        </div>

                        <div id="date-input" class="hidden">
                            <div class="bg-blue-900/30 p-4 rounded border border-blue-800 text-sm text-blue-200 mb-3 flex gap-2">
                                <i class="fas fa-info-circle mt-0.5"></i> 
                                <span>Please visit our workshop on the selected date. We will prepare your items.</span>
                            </div>
                            <label class="block text-sm font-medium text-white mb-1">Pickup Date *</label>
                            <input type="date" name="pickup_date" id="inp-date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="w-full bg-[#111827] border border-gray-600 text-white rounded p-3 focus:outline-none focus:border-blue-500 [color-scheme:dark]">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white mb-1">Phone (optional)</label>
                            <input type="text" value="<?= $_SESSION['phone'] ?? '' ?>" class="w-full bg-[#111827] border border-gray-600 text-white rounded p-3 focus:outline-none focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white mb-1">Email address *</label>
                            <input type="email" value="<?= $_SESSION['email'] ?? '' ?>" class="w-full bg-[#111827] border border-gray-600 text-white rounded p-3 focus:outline-none focus:border-blue-500" readonly>
                        </div>
                        
                        <div class="pt-2">
                            <label class="block text-sm font-medium text-white mb-1">Order notes (optional)</label>
                            <textarea name="notes" rows="4" class="w-full bg-[#111827] border border-gray-600 text-white rounded p-3 focus:outline-none focus:border-blue-500" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <div class="w-full lg:w-1/3">
                <div class="bg-[#1F2937] p-6 rounded-lg border border-gray-700 sticky top-24">
                    <h3 class="text-xl font-bold text-white mb-6">Your Order</h3>

                    <div class="space-y-4 mb-6 border-b border-gray-600 pb-6">
                        <div class="flex justify-between text-sm font-bold text-gray-400 uppercase tracking-wider">
                            <span>Product</span>
                            <span>Subtotal</span>
                        </div>
                        
                        <?php foreach ($items as $item): ?>
                            <div class="flex justify-between items-start text-sm">
                                <div class="text-gray-300 pr-4">
                                    <?= htmlspecialchars($item['part_name']) ?> 
                                    <span class="text-gray-500">x <?= $item['quantity'] ?></span>
                                </div>
                                <div class="text-white font-medium whitespace-nowrap">
                                    Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="space-y-3 mb-8">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Subtotal</span>
                            <span class="text-white font-bold">Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Shipping</span>
                            <span class="text-white text-sm">Calculated at next step</span>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t border-gray-600">
                            <span class="text-white font-bold text-lg">Total</span>
                            <span class="text-[#FFC72C] font-bold text-xl">Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <div class="space-y-4 mb-6">
                        <div class="border-b border-gray-700 pb-4">
                            <label class="flex items-start cursor-pointer">
                                <input type="radio" name="payment_method" value="transfer" checked onclick="toggleProof(true)" class="mt-1 w-4 h-4 text-blue-600 bg-gray-700 border-gray-500 focus:ring-blue-500">
                                <div class="ml-3">
                                    <span class="block text-white font-medium">Direct Bank Transfer</span>
                                    <p class="text-xs text-gray-400 mt-1">Make your payment directly into our bank account. Please use your Order ID as the payment reference.</p>
                                </div>
                            </label>
                            
                            <div id="proof-section" class="mt-3 ml-7 p-3 bg-[#111827] rounded border border-gray-600">
                                <label class="block text-xs font-bold text-gray-300 mb-2">Upload Payment Proof</label>
                                <input type="file" name="payment_proof" class="w-full text-xs text-gray-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-gray-700 file:text-white hover:file:bg-gray-600">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="payment_method" value="cod" onclick="toggleProof(false)" class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-500 focus:ring-blue-500">
                                <span class="ml-3 text-white font-medium">Cash On Delivery</span>
                            </label>
                        </div>
                    </div>

                    <div class="text-xs text-gray-500 mb-6">
                        Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our <a href="#" class="text-blue-400 hover:underline">privacy policy</a>.
                    </div>

                    <div class="flex items-start mb-6">
                        <input id="terms" type="checkbox" required class="w-4 h-4 mt-0.5 rounded bg-gray-700 border-gray-500 text-blue-600 focus:ring-blue-500">
                        <label for="terms" class="ml-2 text-sm text-gray-400">I have read and agree to the website <a href="#" class="text-blue-400 hover:underline">terms and conditions</a> *</label>
                    </div>

                    <button type="submit" class="w-full bg-[#3B82F6] hover:bg-blue-600 text-white font-bold py-4 rounded shadow-lg transition duration-200">
                        Place order
                    </button>
                </div>
            </div>

        </form>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
        function toggleDelivery(type) {
            const addrInput = document.getElementById('address-input');
            const dateInput = document.getElementById('date-input');
            const inpAddr = document.getElementById('inp-address');
            const inpDate = document.getElementById('inp-date');

            if (type === 'delivery') {
                addrInput.classList.remove('hidden');
                dateInput.classList.add('hidden');
                inpAddr.required = true;
                inpDate.required = false;
                inpDate.value = '';
            } else {
                addrInput.classList.add('hidden');
                dateInput.classList.remove('hidden');
                inpAddr.required = false;
                inpDate.required = true;
                inpAddr.value = '';
            }
        }
        // Init state
        toggleDelivery('delivery');

        function toggleProof(show) {
            const section = document.getElementById('proof-section');
            const input = section.querySelector('input');
            if (show) {
                section.style.display = 'block';
                input.setAttribute('required', 'required');
            } else {
                section.style.display = 'none';
                input.removeAttribute('required');
            }
        }
    </script>
</body>
</html>