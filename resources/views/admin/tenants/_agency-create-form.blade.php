@php
    $currencies = ['USD','EUR','GBP','AED','SAR','PKR'];
@endphp
<div class="card-modern mb-4">
    <h3 class="h4 mb-3">Create agency (Super Admin)</h3>
    <p class="text-muted small mb-3">Agency code and agent code are generated automatically. Assigned by: <strong>{{ auth()->user()->first_name }}</strong>.</p>
    <form method="POST" action="{{ route('admin.tenants.store') }}" enctype="multipart/form-data" class="row g-3"
        data-swal-confirm
        data-swal-title="Create this agency?"
        data-swal-text="An approved agency and tenant admin account will be created immediately."
        data-swal-icon="question"
        data-swal-confirm-text="Yes, create"
        data-swal-confirm-color="#0d6efd">
        @csrf
        <div class="col-12"><h5 class="h6 text-secondary">Agency</h5></div>
        <div class="col-md-6">
            <label class="form-label">Office / agency name <span class="text-danger">*</span></label>
            <input type="text" name="office_name" class="form-control" value="{{ old('office_name') }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Agency email</label>
            <input type="email" name="tenant_email" class="form-control" value="{{ old('tenant_email') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Agency phone</label>
            <input type="text" name="tenant_phone" class="form-control" value="{{ old('tenant_phone') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Office type <span class="text-danger">*</span></label>
            <select name="office_type" class="form-select" required>
                <option value="b2b_agent" @selected(old('office_type', 'b2b_agent') === 'b2b_agent')>B2B agent — only create agent</option>
                <option value="gsa_agent" @selected(old('office_type') === 'gsa_agent')>GSA agent — agency or agent</option>
                <option value="api_agent" @selected(old('office_type') === 'api_agent')>API agent — future</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Debtor type <span class="text-danger">*</span></label>
            <select name="debtor_type_id" class="form-select" required>
                <option value="">Select</option>
                @foreach($debtorTypes as $dt)
                    <option value="{{ $dt->id }}" @selected(old('debtor_type_id') == $dt->id)>{{ $dt->name }}</option>
                @endforeach
            </select>
            <div class="form-text"><a href="{{ route('admin.debtor-types.index') }}">Manage debtor types</a></div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Parent agency (GSA only)</label>
            <select name="parent_tenant_id" class="form-select">
                <option value="">— None —</option>
                @foreach($parentAgencies as $p)
                    <option value="{{ $p->id }}" @selected(old('parent_tenant_id') == $p->id)>{{ $p->name }} ({{ $p->agency_code }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tax number</label>
            <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Registration number</label>
            <input type="text" name="reg_number" class="form-control" value="{{ old('reg_number') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Currency <span class="text-danger">*</span></label>
            <select name="currency" class="form-select" required>
                @foreach($currencies as $c)
                    <option value="{{ $c }}" @selected(old('currency', 'USD') === $c)>{{ $c }}</option>
                @endforeach
            </select>
            <div class="form-text">Changing currency later should convert data; conversion is a future step.</div>
        </div>

        <div class="col-12 mt-2"><h5 class="h6 text-secondary">Address</h5></div>
        <div class="col-md-3">
            <label class="form-label">Country</label>
            <input type="text" name="address_country" class="form-control" value="{{ old('address_country') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">State</label>
            <input type="text" name="address_state" class="form-control" value="{{ old('address_state') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">City</label>
            <input type="text" name="address_city" class="form-control" value="{{ old('address_city') }}">
        </div>
        <div class="col-md-12">
            <label class="form-label">Full address</label>
            <textarea name="address_line" class="form-control" rows="2">{{ old('address_line') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Agency logo (max 1 MB)</label>
            <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <div class="col-md-6">
            <label class="form-label">Documents (max 512 KB each)</label>
            <input type="file" name="documents[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <div class="col-12 mt-2"><h5 class="h6 text-secondary">Agent admin</h5></div>
        <div class="col-md-3">
            <label class="form-label">First name <span class="text-danger">*</span></label>
            <input type="text" name="admin_first_name" class="form-control" value="{{ old('admin_first_name') }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Last name <span class="text-danger">*</span></label>
            <input type="text" name="admin_last_name" class="form-control" value="{{ old('admin_last_name') }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Username</label>
            <input type="text" name="admin_username" class="form-control" value="{{ old('admin_username') }}" placeholder="Auto from email if empty">
        </div>
        <div class="col-md-3">
            <label class="form-label">Mobile</label>
            <input type="text" name="admin_phone" class="form-control" value="{{ old('admin_phone') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Country</label>
            <input type="text" name="admin_country" class="form-control" value="{{ old('admin_country') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Profile picture</label>
            <input type="file" name="admin_photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <div class="col-md-3">
            <label class="form-label">Agent document</label>
            <input type="file" name="admin_agent_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        </div>
        <div class="col-md-3">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" name="admin_password" class="form-control" required>
        </div>

        <div class="col-12">
            <button class="btn btn-primary">Create agency</button>
        </div>
    </form>
</div>
