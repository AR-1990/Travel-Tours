<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content\PartnerInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerInquiryController extends Controller
{
    protected function ensureSuperAdmin(): void
    {
        $user = Auth::user();

        if (! $user || $user->user_type !== 'super_admin') {
            abort(403, 'Only super admin can view partnership applications.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureSuperAdmin();

        $query = PartnerInquiry::query()->latest();

        $status = (string) $request->query('status', '');
        if (in_array($status, [PartnerInquiry::STATUS_NEW, PartnerInquiry::STATUS_CONTACTED, PartnerInquiry::STATUS_CLOSED], true)) {
            $query->where('status', $status);
        }

        $type = (string) $request->query('type', '');
        if (in_array($type, PartnerInquiry::typeKeys(), true)) {
            $query->where('partner_type', $type);
        }

        $inquiries = $query->paginate(20)->withQueryString();
        $partnerTypes = PartnerInquiry::partnerTypes();
        $newCount = PartnerInquiry::query()->where('status', PartnerInquiry::STATUS_NEW)->count();

        return view('admin.partner-inquiries.index', compact('inquiries', 'partnerTypes', 'newCount', 'status', 'type'));
    }

    public function show(PartnerInquiry $partnerInquiry)
    {
        $this->ensureSuperAdmin();

        if ($partnerInquiry->status === PartnerInquiry::STATUS_NEW && $partnerInquiry->read_at === null) {
            $partnerInquiry->forceFill(['read_at' => now()])->save();
        }

        return view('admin.partner-inquiries.show', [
            'inquiry' => $partnerInquiry->fresh(),
        ]);
    }

    public function update(Request $request, PartnerInquiry $partnerInquiry)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', [
                PartnerInquiry::STATUS_NEW,
                PartnerInquiry::STATUS_CONTACTED,
                PartnerInquiry::STATUS_CLOSED,
            ])],
        ]);

        $partnerInquiry->forceFill([
            'status' => $validated['status'],
            'read_at' => $partnerInquiry->read_at ?? now(),
        ])->save();

        return back()->with('success', 'Application status updated.');
    }

    public function destroy(PartnerInquiry $partnerInquiry)
    {
        $this->ensureSuperAdmin();
        $partnerInquiry->delete();

        return redirect()->route('admin.partner-inquiries.index')->with('success', 'Application deleted.');
    }
}
