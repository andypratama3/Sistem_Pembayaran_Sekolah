<div class="mt-2 card stretch stretch-full">
    <div class="card-header border-bottom">
        <h5 class="card-title">{{ $formTitle ?? ('Edit Payment Record - ' . ($payment->id ?? 'New')) }}</h5>
    </div>
    <div class="card-body">
        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="student_id" class="fw-semibold">Student <span class="text-danger">*</span></label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-user"></i></div>
                    <select data-select2-selector="default" name="student_id" id="student_id"
                        class="form-control @error('student_id') is-invalid @enderror" required>
                        <option value="">-- Select Student --</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}"
                                {{ old('student_id', $payment->student_id ?? '') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->nisn ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('student_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="payment_title_id" class="fw-semibold">Payment Type <span class="text-danger">*</span></label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-credit-card"></i></div>
                    <select data-select2-selector="default" name="payment_title_id" id="payment_title_id"
                        class="form-control @error('payment_title_id') is-invalid @enderror" required>
                        <option value="">-- Select Payment Type --</option>
                        @foreach ($paymentTitles as $title)
                            <option value="{{ $title->id }}"
                                {{ old('payment_title_id', $payment->payment_title_id ?? '') == $title->id ? 'selected' : '' }}>
                                {{ $title->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('payment_title_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="classroom_id" class="fw-semibold">Classroom <span class="text-danger">*</span></label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-briefcase"></i></div>
                    <select data-select2-selector="default" name="classroom_id" id="classroom_id"
                        class="form-control @error('classroom_id') is-invalid @enderror" required>
                        <option value="">-- Select Classroom --</option>
                        @foreach ($classrooms as $classroom)
                            <option value="{{ $classroom->id }}"
                                {{ old('classroom_id', $payment->classroom_id ?? '') == $classroom->id ? 'selected' : '' }}>
                                {{ $classroom->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('classroom_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="gross_amount" class="fw-semibold">Amount (Rp) <span class="text-danger">*</span></label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-dollar-sign"></i></div>
                    <input type="number" name="gross_amount" id="gross_amount"
                        class="form-control @error('gross_amount') is-invalid @enderror"
                        value="{{ old('gross_amount', $payment->gross_amount ?? '') }}"
                        placeholder="0" min="0" step="0.01" required>
                </div>
                @error('gross_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="start_date" class="fw-semibold">Payment Date</label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-calendar"></i></div>
                    <input type="text" data-datepicker="true" name="start_date" id="start_date"
                        class="form-control @error('start_date') is-invalid @enderror"
                        value="{{ old('start_date', isset($payment) ? $payment->start_date?->toDateString() : now()->toDateString()) }}">
                </div>
                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="payment_method" class="fw-semibold">Payment Method</label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-credit-card"></i></div>
                    <select data-select2-selector="default" name="payment_method" id="payment_method"
                        class="form-control @error('payment_method') is-invalid @enderror">
                        <option value="">-- Select Method --</option>
                        <option value="cash" {{ old('payment_method', $payment->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method', $payment->payment_method ?? '') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="credit_card" {{ old('payment_method', $payment->payment_method ?? '') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        <option value="e_wallet" {{ old('payment_method', $payment->payment_method ?? '') == 'e_wallet' ? 'selected' : '' }}>E-Wallet</option>
                    </select>
                </div>
                @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="transaction_id" class="fw-semibold">Transaction ID</label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-hash"></i></div>
                    <input type="text" name="transaction_id" id="transaction_id"
                        class="form-control @error('transaction_id') is-invalid @enderror"
                        value="{{ old('transaction_id', $payment->transaction_id ?? '') }}"
                        placeholder="Transaction ID from payment gateway" maxlength="100">
                </div>
                @error('transaction_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="receipt_number" class="fw-semibold">Receipt Number</label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-file-text"></i></div>
                    <input type="text" name="receipt_number" id="receipt_number"
                        class="form-control @error('receipt_number') is-invalid @enderror"
                        value="{{ old('receipt_number', $payment->receipt_number ?? '') }}"
                        placeholder="Receipt number" maxlength="50">
                </div>
                @error('receipt_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="description" class="fw-semibold">Description</label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-align-left"></i></div>
                    <textarea name="description" id="description"
                        class="form-control @error('description') is-invalid @enderror"
                        rows="3" placeholder="Payment description or notes" maxlength="500">{{ old('description', $payment->description ?? '') }}</textarea>
                </div>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="status" class="fw-semibold">Status <span class="text-danger">*</span></label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-info"></i></div>
                    <select data-select2-selector="default" name="status" id="status"
                        class="form-control @error('status') is-invalid @enderror" required>
                        <option value="pending" {{ old('status', $payment->status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ old('status', $payment->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="overdue" {{ old('status', $payment->status ?? '') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ old('status', $payment->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <x-form-actions cancel-route="dashboard.payments.index" />
    </div>
</div>
