<!-- Advanced Filter Component -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            @csrf
            <!-- Date Range Filter -->
            @if ($showDateRange)
                <div class="col-md-4">
                    <label class="form-label">Date From</label>
                    <input type="text" name="date_from" data-datepicker="true" class="form-control" value="{{ request('date_from') }}" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-uppercase">Sampai</label>
                    <input type="text" name="date_to" data-datepicker="true" class="form-control" value="{{ request('date_to') }}" placeholder="YYYY-MM-DD">
                </div>
            @endif

            <!-- Status Filter -->
            @if ($showStatus)
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" data-select2-selector="status">
                        <option value="">All</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive
                        </option>
                    </select>
                </div>
            @endif

            <!-- Search -->
            @if ($showSearch)
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search..."
                        value="{{ request('search') }}">
                </div>
            @endif

            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ request()->url() }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>
