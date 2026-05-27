<!-- Bulk Actions Component -->
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete('{{ $model }}')">
                <i class="fas fa-trash"></i> Delete Selected
            </button>
            <button type="button" class="btn btn-success btn-sm" onclick="bulkExport('{{ $model }}')">
                <i class="fas fa-download"></i> Export Selected
            </button>
            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-upload"></i> Import
            </button>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import {{ $model }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('dashboard.bulk-operations.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="model" value="{{ $model }}">
                    <div class="mb-3">
                        <label class="form-label">Select Excel File</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function bulkDelete(model) {
        const selected = document.querySelectorAll('input[name="selected[]"]:checked');
        if (selected.length === 0) {
            alert('Please select items to delete');
            return;
        }

        if (confirm('Are you sure? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('dashboard.bulk-operations.delete') }}';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const modelInput = document.createElement('input');
            modelInput.type = 'hidden';
            modelInput.name = 'model';
            modelInput.value = model;
            form.appendChild(modelInput);

            selected.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    }

    function bulkExport(model) {
        const selected = document.querySelectorAll('input[name="selected[]"]:checked');
        if (selected.length === 0) {
            alert('Please select items to export');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('dashboard.bulk-operations.export') }}';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        const modelInput = document.createElement('input');
        modelInput.type = 'hidden';
        modelInput.name = 'model';
        modelInput.value = model;
        form.appendChild(modelInput);

        selected.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }
</script>
