      </div>
    </section>
  </div>

  <footer class="main-footer">
    <strong>&copy; <?php echo date('Y'); ?> <a href="../index.php">Aqua Air Cooling</a>.</strong>
    Developed By <a style="color: #007bff"> <b>Mohit Patel</b> 
    <div class="float-right d-none d-sm-inline-block">
      <b>Admin Panel</b>
    </div>
  </footer>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).on('click', '.btn-confirm-action, a[data-confirm]', function(e) {
    e.preventDefault();
    const url = $(this).attr('href');
    const title = $(this).data('title') || $(this).data('confirm') || 'Are you sure?';
    const text = $(this).data('text') || "Please confirm to proceed with this action.";
    const icon = $(this).data('icon') || 'warning';
    const confirmBtnText = $(this).data('confirm-btn') || 'Yes, Proceed';
    const confirmBtnColor = $(this).data('confirm-color') || '#3085d6';

    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: confirmBtnColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmBtnText,
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
});
</script>
<?php if (!empty($extra_js)) { echo $extra_js; } ?>
</body>
</html>
