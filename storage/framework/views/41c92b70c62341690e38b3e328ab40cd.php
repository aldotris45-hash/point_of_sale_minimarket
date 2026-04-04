<?php $__env->startSection('title', 'Transaksi'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h3 mb-0"><i class="bi bi-receipt"></i> Transaksi</h1>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form id="filterForm" class="card shadow-sm mb-3">
            <div class="card-body row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label">Cari</label>
                    <input type="text" class="form-control" name="q" value="<?php echo e($q); ?>"
                        placeholder="No/ket.">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua</option>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s->value); ?>" <?php echo e($status === $s->value ? 'selected' : ''); ?>>
                                <?php echo e(strtoupper($s->value)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Metode</label>
                    <select class="form-select" name="method">
                        <option value="">Semua</option>
                        <?php $__currentLoopData = $methods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m->value); ?>" <?php echo e($method === $m->value ? 'selected' : ''); ?>>
                                <?php echo e(strtoupper($m->value)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Piutang</label>
                    <select class="form-select" name="due">
                        <option value=""<?php echo e(empty($due) ? ' selected' : ''); ?>>Semua</option>
                        <option value="utang"<?php echo e(($due === 'utang') ? ' selected' : ''); ?>>Belum Lunas</option>
                        <option value="lunas"<?php echo e(($due === 'lunas') ? ' selected' : ''); ?>>Sudah Lunas</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Pelanggan</label>
                    <select class="form-select" name="customer_id">
                        <option value="">Semua</option>
                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cust): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cust->id); ?>" <?php echo e(($customer_id == $cust->id) ? 'selected' : ''); ?>><?php echo e($cust->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Dari</label>
                    <input type="date" class="form-control" name="from" value="<?php echo e($from); ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Sampai</label>
                    <input type="date" class="form-control" name="to" value="<?php echo e($to); ?>">
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label">Per halaman</label>
                    <select class="form-select" name="per_page">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div class="col-12 col-md-12 d-flex gap-2 justify-content-end">
                    <button type="button" id="btnReset" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i>
                        Reset</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Terapkan</button>
                </div>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table id="transactionsTable" class="table align-middle mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th>Metode</th>
                            <th>Piutang</th>
                            <th>Status</th>
                            <th class="text-end">Total</th>
                            <th class="text-end" style="width:160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mt-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-muted"><i class="bi bi-calculator"></i> Total Keseluruhan (Hasil Filter)</span>
                <span class="fw-bold fs-5 text-primary" id="grandTotalDisplay">Rp 0</span>
            </div>
        </div>
    </section>

    
    <div class="modal fade" id="editTrxDateModal" tabindex="-1" aria-labelledby="editTrxDateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <form id="editTrxDateForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTrxDateModalLabel">
                            <i class="bi bi-calendar-event"></i> Ubah Tanggal
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">Invoice: <strong id="editTrxInvoice"></strong></p>
                        <label for="editTrxDateInput" class="form-label">Tanggal Baru</label>
                        <input type="date" class="form-control" id="editTrxDateInput" name="date" required
                               max="<?php echo e(date('Y-m-d')); ?>">
                        <small class="text-muted mt-1 d-block">Data pembayaran & buku kas terkait akan otomatis disesuaikan.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/vendor/datatables.min.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('script'); ?>
    <script src="<?php echo e(asset('assets/vendor/jquery-3.7.0.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/vendor/datatables.min.js')); ?>"></script>
    <script>
        (function() {
            const $form = $('#filterForm');
            const $perPage = $form.find('select[name="per_page"]');
            const table = $('#transactionsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?php echo e(route('transaksi.data')); ?>',
                    type: 'GET',
                    data: function(d) {
                        const fd = Object.fromEntries(new FormData($form[0]).entries());
                        return Object.assign(d, fd);
                    }
                },
                language: {
                    url: '<?php echo e(asset('assets/vendor/id.json')); ?>'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'invoice',
                        name: 'invoice_number'
                    },
                    {
                        data: 'date',
                        name: 'created_at'
                    },
                    {
                        data: 'cashier',
                        name: 'user.name',
                        defaultContent: '',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'method',
                        name: 'payment_method'
                    },
                    {
                        data: 'due_badge',
                        name: 'due',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'total',
                        name: 'total',
                        className: 'text-end'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    },
                ],
                order: [
                    [2, 'desc']
                ],
                pageLength: 10,
                drawCallback: function(settings) {
                    const json = settings.json;
                    if (json && json.grand_total !== undefined) {
                        const fmt = Number(json.grand_total || 0).toLocaleString('id-ID');
                        $('#grandTotalDisplay').text('Rp ' + fmt);
                    }
                },
            });

            const initialLen = parseInt($perPage.val() || '10', 10);
            if (!Number.isNaN(initialLen)) {
                table.page.len(initialLen).draw();
            }

            $form.on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            $perPage.on('change', function() {
                const len = parseInt(this.value || '10', 10);
                table.page.len(len).draw();
            });

            $('#btnReset').on('click', function() {
                $form[0].reset();
                $perPage.val('10');
                table.page.len(10).draw();
                table.ajax.reload();
            });

            // Edit Date Modal handler
            const trxModal = new bootstrap.Modal(document.getElementById('editTrxDateModal'));
            const trxForm = document.getElementById('editTrxDateForm');
            const trxDateInput = document.getElementById('editTrxDateInput');
            const trxInvoice = document.getElementById('editTrxInvoice');

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-edit-trx-date');
                if (!btn) return;

                trxForm.action = btn.dataset.url;
                trxDateInput.value = btn.dataset.date;
                trxInvoice.textContent = btn.dataset.invoice;
                trxModal.show();
            });
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12\resources\views/transactions/index.blade.php ENDPATH**/ ?>