<?php $__env->startSection('title', 'Pembayaran'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container py-4">
        <header class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-credit-card-2-front"></i> Pembayaran
                </h1>
                <p class="text-muted mb-0">Daftar riwayat pembayaran dari transaksi.</p>
            </div>
        </header>

        <form id="payFilter" class="card shadow-sm mb-3">
            <div class="card-body row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="filter_q" class="form-label">Cari</label>
                    <input type="text" class="form-control" id="filter_q" name="q" value="<?php echo e($q); ?>"
                        placeholder="Invoice / Order ID">
                </div>
                <div class="col-6 col-md-2">
                    <label for="filter_status" class="form-label">Status</label>
                    <select class="form-select" id="filter_status" name="status">
                        <option value="">Semua</option>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s->value); ?>" <?php echo e($status === $s->value ? 'selected' : ''); ?>>
                                <?php echo e(strtoupper($s->value)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="filter_method" class="form-label">Metode</label>
                    <select class="form-select" id="filter_method" name="method">
                        <option value="">Semua</option>
                        <?php $__currentLoopData = $methods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m->value); ?>" <?php echo e($method === $m->value ? 'selected' : ''); ?>>
                                <?php echo e(strtoupper($m->value)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-1">
                    <label for="filter_provider" class="form-label">Provider</label>
                    <input type="text" class="form-control" id="filter_provider" name="provider" value="<?php echo e($provider); ?>"
                        placeholder="provider">
                </div>
                <div class="col-6 col-md-2">
                    <label for="filter_from" class="form-label">Dari</label>
                    <input type="date" class="form-control" id="filter_from" name="from" value="<?php echo e($from); ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label for="filter_to" class="form-label">Sampai</label>
                    <input type="date" class="form-control" id="filter_to" name="to" value="<?php echo e($to); ?>">
                </div>
                <div class="col-12 col-md-12 d-flex gap-2 justify-content-end">
                    <button type="button" id="btnReset" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i>
                        Reset</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Terapkan</button>
                </div>
            </div>
        </form>

        <section class="card shadow-sm">
            <div class="table-responsive">
                <table id="paymentsTable" class="table align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>Invoice</th>
                            <th>Kasir</th>
                            <th>Metode</th>
                            <th>Provider</th>
                            <th>Status</th>
                            <th class="text-end">Jumlah</th>
                            <th>Dibuat</th>
                            <th>Dibayar</th>
                            <th class="text-end" style="width:140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/vendor/datatables.min.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('script'); ?>
    <script src="<?php echo e(asset('assets/vendor/jquery-3.7.0.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/vendor/datatables.min.js')); ?>"></script>
    <script>
        (function() {
            const $form = $('#payFilter');
            const table = $('#paymentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?php echo e(route('pembayaran.data')); ?>',
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
                        name: 'transaction.invoice_number'
                    },
                    {
                        data: 'cashier',
                        name: 'transaction.user.name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'method_text',
                        name: 'method'
                    },
                    {
                        data: 'provider',
                        name: 'provider'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        className: 'text-end'
                    },
                    {
                        data: 'created',
                        name: 'created_at'
                    },
                    {
                        data: 'paid',
                        name: 'paid_at',
                        orderable: false,
                        searchable: false
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
                    [7, 'desc']
                ],
                pageLength: 10,
            });

            $form.on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            $('#btnReset').on('click', function() {
                $form[0].reset();
                table.ajax.reload();
            });
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12\resources\views/payments/index.blade.php ENDPATH**/ ?>