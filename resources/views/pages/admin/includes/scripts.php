<?php
$base = '/DoAn/DoAnTotNghiep/';
?>
<!-- Scroll to Top Button (chỉ là nút, không có HTML thừa) -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal (cần thiết cho chức năng logout) -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Đăng xuất?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Bạn có chắc chắn muốn đăng xuất?</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                <a class="btn btn-primary" href="admin.php?url=logout">Đăng xuất</a>
            </div>
        </div>
    </div>
</div>

<!-- Các thư viện JS -->
<script src="<?= $base ?>vendor/jquery/jquery.min.js"></script>
<script src="<?= $base ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?>vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= $base ?>js/sb-admin-2.min.js"></script>

<!-- Chart JS (nếu cần) -->
<script src="<?= $base ?>vendor/chart.js/Chart.min.js"></script>
<script src="<?= $base ?>js/demo/chart-area-demo.js"></script>
<script src="<?= $base ?>js/demo/chart-pie-demo.js"></script>
