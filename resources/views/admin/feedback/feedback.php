<?php
$pageTitle = 'Inbox Customer Feedback';
$activeMenu = 'feedback';

require '../../../backend/config.php';
include '../template-header.php';
include '../template-sidebar.php';

// Ambil semua data feedback
$sql = "SELECT id, name, email, message, status, submitted_at FROM feedback ORDER BY submitted_at DESC";
$result = $conn->query($sql);

$unread_feedback = [];
$read_feedback = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if ($row['status'] === 'new') {
            $unread_feedback[] = $row;
        } else {
            $read_feedback[] = $row;
        }
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">    
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="../style.css">
<style>
    .main-content {
    flex-grow: 1;
    padding: 2rem;
    overflow-y: auto;
    }
    .feedback-tabs {
        display: flex;
        margin-bottom: 1.5rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        width: fit-content;
    }
    .tab-btn {
        padding: 10px 20px;
        background-color: #fff;
        border: none;
        cursor: pointer;
        font-weight: 600;
        color: #555;
        transition: all 0.3s ease;
        position: relative;
    }
    .tab-btn:first-child { border-right: 1px solid #ddd; }
    .tab-btn.active {
        background-color: #FFC72C;
        color: #1F2937;
    }
    
    .feedback-viewport {
        width: 100%;
        overflow: hidden;
    }
    .feedback-slider {
        display: flex;
        width: 200%;
        transition: transform 0.4s ease-in-out;
    }
    .feedback-slider.show-read {
        transform: translateX(-50%);
    }

    .feedback-panel {
        width: 50%;
        padding: 0 10px;
        box-sizing: border-box;
    }
    .feedback-container { display: flex; flex-direction: column; gap: 1.5rem; }
    .feedback-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #ccc; }
    .feedback-card.status-new { border-left-color: #FFC72C; }
    .feedback-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; background: #f8f9fa; border-bottom: 1px solid #eee; }
    .sender-info strong { display: block; }
    .sender-info span { color: #666; font-size: 0.85rem; }
    .submission-time { font-size: 0.85rem; color: #666; }
    .feedback-body { padding: 1.5rem; line-height: 1.6; }
    .feedback-actions { padding: 0 1.5rem 1rem; text-align: right; }
    .btn-mark-read { background-color: #007bff; color: white; padding: 6px 12px; border-radius: 5px; border: none; cursor: pointer; }
</style>

<div class="main-content">
    
    <div class="feedback-tabs">
        <button id="unread-btn" class="tab-btn active">Unread (<?php echo count($unread_feedback); ?>)</button>
        <button id="read-btn" class="tab-btn">Read (<?php echo count($read_feedback); ?>)</button>
    </div>

    <div class="feedback-viewport">
        <div id="feedback-slider" class="feedback-slider">
            
            <div class="feedback-panel">
                <div class="feedback-container">
                    <?php if (!empty($unread_feedback)): ?>
                        <?php foreach ($unread_feedback as $row): ?>
                            <div class="feedback-card status-new">
                                <div class="feedback-header">
                                    <div class="sender-info"><strong><?php echo htmlspecialchars($row['name']); ?></strong><span><?php echo htmlspecialchars($row['email']); ?></span></div>
                                    <div class="submission-time"><?php echo date('d M Y, H:i', strtotime($row['submitted_at'])); ?></div>
                                </div>
                                <div class="feedback-body"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>
                                <div class="feedback-actions">
                                    <button class="btn-mark-read" data-feedbackid="<?php echo $row['id']; ?>">Tandai Sudah Dibaca</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Tidak ada feedback baru.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="feedback-panel">
                <div class="feedback-container">
                    <?php if (!empty($read_feedback)): ?>
                        <?php foreach ($read_feedback as $row): ?>
                            <div class="feedback-card">
                                <div class="feedback-header">
                                    <div class="sender-info"><strong><?php echo htmlspecialchars($row['name']); ?></strong><span><?php echo htmlspecialchars($row['email']); ?></span></div>
                                    <div class="submission-time"><?php echo date('d M Y, H:i', strtotime($row['submitted_at'])); ?></div>
                                </div>
                                <div class="feedback-body"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Tidak ada feedback yang sudah dibaca.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const unreadBtn = document.getElementById('unread-btn');
    const readBtn = document.getElementById('read-btn');
    const slider = document.getElementById('feedback-slider');

    const feedbackViewport = document.querySelector('.feedback-viewport');

    if (unreadBtn) {
        unreadBtn.addEventListener('click', function() {
            slider.classList.remove('show-read');
            unreadBtn.classList.add('active');
            readBtn.classList.remove('active');
        });
    }

    if (readBtn) {
        readBtn.addEventListener('click', function() {
            slider.classList.add('show-read');
            readBtn.classList.add('active');
            unreadBtn.classList.remove('active');
        });
    }

    if (feedbackViewport) {
        feedbackViewport.addEventListener('click', function(event) {
            if (event.target.classList.contains('btn-mark-read')) {
                const button = event.target;
                const feedbackId = button.dataset.feedbackid;
                
                Swal.fire({
                    title: 'Konfirmasi Tindakan',
                    text: "Anda yakin ingin menandai pesan ini sudah dibaca?",
                    icon: 'warning', 
                    showCancelButton: true, 
                    confirmButtonColor: '#007bff', 
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Tandai!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika user menekan "Ya, Tandai!", baru kita jalankan aksi
                        window.location.href = '../../../backend/proses_mark_feedback_read.php?id=' + feedbackId;
                    }
                })
            }
        });
    }
});
</script>
<script src="../script.js"></script>