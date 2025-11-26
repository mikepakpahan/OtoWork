<?php
require_once '../../config/database.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Please login to book a service!'];
    header("Location: " . BASE_URL . "/customer/views/login.php");
    exit;
}

$activePage = 'booking';
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? '';
$user_phone = $_SESSION['phone_number'] ?? ''; // Asumsi ada session phone, kalau tidak kosongkan

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Service | OtoWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Custom Date Picker Dark Mode Color Scheme */
        ::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
    </style>
</head>

<body class="bg-[#0B0E17] text-gray-300 font-sans min-h-screen flex flex-col">

    <?php require_once '../includes/navbar.php'; ?>

    <div class="bg-[#1F2937] border-b border-gray-700 py-12">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Schedule Your Service</h1>
            <p class="text-gray-400">Skip the queue. Book an appointment for your motorcycle maintenance.</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12 flex-grow">
        <div class="flex flex-col lg:flex-row gap-10 max-w-6xl mx-auto">

            <div class="w-full lg:w-2/3">
                <div class="bg-[#1F2937] p-8 rounded-lg shadow-xl border border-gray-700">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-gray-600 pb-4">
                        <i class="far fa-calendar-check text-[#3B82F6]"></i> Appointment Details
                    </h3>

                    <form action="<?= BASE_URL ?>/logic/booking_master.php?act=create" method="POST">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Customer Name</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" value="<?= htmlspecialchars($user_name) ?>" readonly
                                        class="w-full pl-10 bg-[#111827] border border-gray-600 text-gray-400 rounded-lg p-3 cursor-not-allowed focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">WhatsApp Number</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="fab fa-whatsapp"></i>
                                    </span>
                                    <input type="text" name="phone_number" value="<?= htmlspecialchars($user_phone) ?>" required placeholder="0812..."
                                        class="w-full pl-10 bg-[#111827] border border-gray-600 text-white rounded-lg p-3 focus:outline-none focus:border-[#3B82F6] transition">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Motorcycle Brand/Type</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="fas fa-motorcycle"></i>
                                    </span>
                                    <select name="motor_type" required
                                        class="w-full pl-10 bg-[#111827] border border-gray-600 text-white rounded-lg p-3 focus:outline-none focus:border-[#3B82F6] appearance-none">
                                        <option value="" disabled selected>Select Brand</option>
                                        <option value="Honda">Honda</option>
                                        <option value="Yamaha">Yamaha</option>
                                        <option value="Suzuki">Suzuki</option>
                                        <option value="Kawasaki">Kawasaki</option>
                                        <option value="Vespa">Vespa</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">License Plate</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="fas fa-id-card"></i>
                                    </span>
                                    <input type="text" name="license_plate" required placeholder="BK 1234 AB"
                                        class="w-full pl-10 bg-[#111827] border border-gray-600 text-white rounded-lg p-3 uppercase focus:outline-none focus:border-[#3B82F6] transition">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Preferred Date</label>
                                <input type="date" name="booking_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                    class="w-full bg-[#111827] border border-gray-600 text-white rounded-lg p-3 focus:outline-none focus:border-[#3B82F6] transition">
                                <p class="text-xs text-gray-500 mt-1">*Minimum booking H-1</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Preferred Time</label>
                                <div class="relative">
                                    <select name="booking_time" required
                                        class="w-full bg-[#111827] border border-gray-600 text-white rounded-lg p-3 focus:outline-none focus:border-[#3B82F6] appearance-none">
                                        <option value="" disabled selected>Select Time Slot</option>
                                        <option value="09:00">09:00 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="13:00">01:00 PM</option>
                                        <option value="14:00">02:00 PM</option>
                                        <option value="15:00">03:00 PM</option>
                                        <option value="16:00">04:00 PM</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Service Request / Complaint</label>
                            <textarea name="complaint" rows="4" required placeholder="Describe your issue or requested service (e.g., Oil change, Brake check, Weird noise from engine...)"
                                class="w-full bg-[#111827] border border-gray-600 text-white rounded-lg p-3 focus:outline-none focus:border-[#3B82F6] transition"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-[#3B82F6] hover:bg-blue-600 text-white font-bold py-4 px-6 rounded-lg shadow-lg transform hover:-translate-y-1 transition duration-200 flex items-center justify-center gap-2">
                            <span>Confirm Booking</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>

                    </form>
                </div>
            </div>

            <div class="w-full lg:w-1/3 space-y-8">
                
                <div class="bg-[#1F2937] p-6 rounded-lg border border-gray-700">
                    <h4 class="text-white font-bold text-lg mb-4">Why Choose OtoWork?</h4>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-900/50 flex items-center justify-center text-blue-400 flex-shrink-0">
                                <i class="fas fa-certificate text-sm"></i>
                            </div>
                            <div>
                                <h5 class="text-white font-medium text-sm">Certified Mechanics</h5>
                                <p class="text-gray-400 text-xs">Experienced technicians you can trust.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-900/50 flex items-center justify-center text-blue-400 flex-shrink-0">
                                <i class="fas fa-stopwatch text-sm"></i>
                            </div>
                            <div>
                                <h5 class="text-white font-medium text-sm">No Waiting Time</h5>
                                <p class="text-gray-400 text-xs">Book online and get serviced immediately.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-900/50 flex items-center justify-center text-blue-400 flex-shrink-0">
                                <i class="fas fa-shield-alt text-sm"></i>
                            </div>
                            <div>
                                <h5 class="text-white font-medium text-sm">Service Warranty</h5>
                                <p class="text-gray-400 text-xs">7-day guarantee on all services.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="bg-[#1F2937] p-6 rounded-lg border border-gray-700">
                    <h4 class="text-white font-bold text-lg mb-4">Operating Hours</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between border-b border-gray-700 pb-2">
                            <span class="text-gray-400">Monday - Friday</span>
                            <span class="text-white font-bold">08:00 - 17:00</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-700 pb-2">
                            <span class="text-gray-400">Saturday</span>
                            <span class="text-white font-bold">08:00 - 15:00</span>
                        </div>
                        <div class="flex justify-between text-red-400">
                            <span>Sunday</span>
                            <span class="font-bold">Closed</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-900 to-[#1F2937] p-6 rounded-lg border border-blue-800 text-center">
                    <i class="fas fa-headset text-4xl text-blue-400 mb-3"></i>
                    <h4 class="text-white font-bold mb-2">Need Assistance?</h4>
                    <p class="text-gray-300 text-xs mb-4">Not sure what service you need? Call us before booking.</p>
                    <a href="https://wa.me/628123456789" class="inline-block bg-white text-blue-900 font-bold py-2 px-4 rounded text-sm hover:bg-gray-100 transition">
                        Chat on WhatsApp
                    </a>
                </div>

            </div>

        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            Swal.fire({
                icon: '<?= $_SESSION['alert']['type'] ?>',
                title: '<?= ucfirst($_SESSION['alert']['type']) ?>',
                text: '<?= $_SESSION['alert']['message'] ?>',
                background: '#1F2937',
                color: '#fff',
                confirmButtonColor: '#3B82F6'
            });
        </script>
    <?php unset($_SESSION['alert']); endif; ?>

</body>
</html>