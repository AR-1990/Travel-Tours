<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Signup - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-blue-50 min-h-screen py-12 px-4">
    <div class="max-w-3xl mx-auto w-full bg-white rounded-2xl shadow-xl p-8 border border-indigo-100">
        <h2 class="text-3xl font-bold text-indigo-700 mb-2">Create Agency</h2>
        <p class="text-gray-600 mb-2">Agency code and agent code are generated automatically when you submit.</p>
        <p class="text-gray-500 text-sm mb-6">Super admin approval is required before login. Use <strong>email</strong> or <strong>username</strong> to sign in after approval.</p>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul class="list-disc list-inside text-red-800 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.register') }}" enctype="multipart/form-data" class="space-y-5"
            data-swal-confirm
            data-swal-title="Submit agency signup?"
            data-swal-text="Your request will be sent for super admin approval. You cannot log in until approved."
            data-swal-icon="question"
            data-swal-confirm-text="Yes, submit"
            data-swal-confirm-color="#4f46e5">
            @csrf

            <h3 class="font-semibold text-gray-800 border-b pb-2">Agency</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Office / agency name <span class="text-red-500">*</span></label>
                    <input type="text" name="tenant_name" value="{{ old('tenant_name') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agency email <span class="text-red-500">*</span></label>
                    <input type="email" name="tenant_email" value="{{ old('tenant_email') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agency phone</label>
                    <input type="text" name="tenant_phone" value="{{ old('tenant_phone') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Office type <span class="text-red-500">*</span></label>
                    <select name="office_type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="b2b_agent" @selected(old('office_type', 'b2b_agent') === 'b2b_agent')>B2B agent (only create agent)</option>
                        <option value="gsa_agent" @selected(old('office_type') === 'gsa_agent')>GSA agent (agency or agent)</option>
                        <option value="api_agent" @selected(old('office_type') === 'api_agent')>API agent (future)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Debtor type <span class="text-red-500">*</span></label>
                    <select name="debtor_type_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="">Select</option>
                        @foreach($debtorTypes as $dt)
                            <option value="{{ $dt->id }}" @selected(old('debtor_type_id') == $dt->id)>{{ $dt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax number</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Registration number</label>
                    <input type="text" name="reg_number" value="{{ old('reg_number') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agency currency <span class="text-red-500">*</span></label>
                    <select name="currency" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @foreach(['USD','EUR','GBP','AED','SAR','PKR'] as $c)
                            <option value="{{ $c }}" @selected(old('currency', 'USD') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Changing currency later should convert stored amounts; conversion is not applied automatically yet.</p>
                </div>
            </div>

            <h4 class="font-medium text-gray-800">Address</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <input type="text" name="address_country" value="{{ old('address_country') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">State / region</label>
                    <input type="text" name="address_state" value="{{ old('address_state') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="address_city" value="{{ old('address_city') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full address</label>
                    <textarea name="address_line" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg">{{ old('address_line') }}</textarea>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Agency logo (max 1 MB)</label>
                <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Agency documents (max 512 KB each, PDF or image)</label>
                <input type="file" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm">
            </div>

            <h3 class="font-semibold text-gray-800 border-b pb-2 pt-2">Agent admin (your account)</h3>
            <p class="text-sm text-gray-500">First and last name are for display only. You will log in with email or username.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="Leave blank to auto-generate from email">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
                    <input type="text" name="mobile" value="{{ old('mobile') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <input type="text" name="country" value="{{ old('country') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profile picture</label>
                    <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agent document</label>
                    <input type="file" name="agent_document" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-semibold">Submit agency signup</button>
        </form>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.swal-delegation')
</body>
</html>
