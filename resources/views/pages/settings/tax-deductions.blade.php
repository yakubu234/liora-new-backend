@extends('layouts.admin')

@section('title', 'Tax & Deductions')
@section('page_title', 'Tax & Pre-tax Deductions')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('settings.tax-deductions.update') }}">
        @csrf
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Tax Configuration</h3></div>
            <div class="card-body">
                <div class="form-group col-md-4 px-0">
                    <label for="tax_rate">Tax percentage</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="tax_rate" name="tax_rate" min="0" max="100" step="0.0001" value="{{ old('tax_rate', $taxRate) }}" required>
                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                    </div>
                </div>
                <p class="text-muted mb-0">Tax is calculated after the active deductions below are removed from the booking subtotal. These deductions remain part of the customer total; they are only exempted from tax.</p>
            </div>
        </div>

        <div class="card card-outline card-secondary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title">Pre-tax Deductions</h3>
                <button type="button" class="btn btn-sm btn-primary ml-auto" id="add-deduction"><i class="fas fa-plus mr-1"></i>Add deduction</button>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered" id="deductions-table">
                    <thead><tr><th>Name</th><th style="width:220px">Amount (NGN)</th><th style="width:100px">Active</th><th style="width:110px">Default</th><th style="width:80px"></th></tr></thead>
                    <tbody>
                    @foreach (old('deductions', $deductions->map(fn ($row) => (array) $row)->all()) as $index => $deduction)
                        <tr>
                            <td><input type="hidden" name="deductions[{{ $index }}][id]" value="{{ $deduction['id'] ?? '' }}"><input class="form-control" name="deductions[{{ $index }}][name]" value="{{ $deduction['name'] }}" required></td>
                            <td><input type="number" class="form-control" name="deductions[{{ $index }}][amount]" min="0" step="0.01" value="{{ $deduction['amount'] }}" required></td>
                            <td class="text-center"><input type="hidden" name="deductions[{{ $index }}][is_active]" value="0"><input type="checkbox" name="deductions[{{ $index }}][is_active]" value="1" {{ !empty($deduction['is_active']) ? 'checked' : '' }}></td>
                            <td class="text-center"><input type="hidden" name="deductions[{{ $index }}][is_default]" value="0"><input type="checkbox" name="deductions[{{ $index }}][is_default]" value="1" {{ !empty($deduction['is_default']) ? 'checked' : '' }}></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-deduction"><i class="fas fa-trash"></i></button></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-right"><button class="btn btn-primary" type="submit">Save Tax Settings</button></div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(function () {
    let nextIndex = $('#deductions-table tbody tr').length;
    $('#add-deduction').on('click', function () {
        $('#deductions-table tbody').append(`<tr><td><input class="form-control" name="deductions[${nextIndex}][name]" required></td><td><input type="number" class="form-control" name="deductions[${nextIndex}][amount]" min="0" step="0.01" required></td><td class="text-center"><input type="hidden" name="deductions[${nextIndex}][is_active]" value="0"><input type="checkbox" name="deductions[${nextIndex}][is_active]" value="1" checked></td><td class="text-center"><input type="hidden" name="deductions[${nextIndex}][is_default]" value="0"><input type="checkbox" name="deductions[${nextIndex}][is_default]" value="1"></td><td><button type="button" class="btn btn-sm btn-outline-danger remove-deduction"><i class="fas fa-trash"></i></button></td></tr>`);
        nextIndex++;
    });
    $(document).on('click', '.remove-deduction', function () { $(this).closest('tr').remove(); });
});
</script>
@endpush
