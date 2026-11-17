<?php

namespace App\Http\Controllers;

use App\Support\DonorPrototypeData;
use App\Support\PrototypeCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DonationPrototypeController extends Controller
{
    public function checkout(Request $request, string $slug)
    {
        $project = collect($this->projects($request))->firstWhere('slug', $slug);
        abort_unless($project, 404);
        abort_unless($project['status'] === 'ACTIVE', 409, 'Kampanye ini tidak sedang menerima donasi.');

        $user = $request->session()->get('prototype_user');

        return view('donation.checkout', compact('project', 'user'));
    }

    public function createPayment(Request $request, string $slug)
    {
        $project = collect($this->projects($request))->firstWhere('slug', $slug);
        abort_unless($project, 404);
        abort_unless($project['status'] === 'ACTIVE', 409, 'Kampanye ini tidak sedang menerima donasi.');

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:10000', 'max:1000000000'],
            'payment_method' => ['required', 'in:qris,virtual_account,e_wallet,card'],
            'display_name' => ['nullable', 'boolean'],
            'email_notification' => ['nullable', 'boolean'],
            'terms' => ['accepted'],
        ], [
            'amount.required' => 'Masukkan nominal donasi terlebih dahulu.',
            'amount.integer' => 'Nominal donasi harus berupa angka bulat dalam Rupiah.',
            'amount.min' => 'Nominal minimum donasi adalah Rp10.000.',
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran yang dipilih tidak tersedia.',
            'terms.accepted' => 'Persetujuan mekanisme donasi dan escrow wajib dicentang.',
        ]);

        $remaining = max(0, $project['target'] - $project['collected']);
        if ((int) $validated['amount'] > $remaining) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal donasi melebihi sisa target kampanye sebesar Rp'.number_format($remaining, 0, ',', '.').'.',
            ]);
        }

        $user = $request->session()->get('prototype_user');
        $reference = 'RF-CHK-'.now()->format('ymdHis').'-'.Str::upper(Str::random(4));
        $methodLabels = [
            'qris' => 'QRIS · Payment Gateway',
            'virtual_account' => 'Virtual Account · Payment Gateway',
            'e_wallet' => 'E-Wallet · Payment Gateway',
            'card' => 'Kartu Debit/Kredit · Payment Gateway',
        ];
        $orders = $request->session()->get('prototype_checkout_orders', []);
        $orders[$reference] = [
            'reference' => $reference,
            'project_slug' => $slug,
            'project_title' => $project['title'],
            'researcher' => $project['researcher'],
            'donor_name' => $user['name'] ?? 'Donatur ResFund',
            'donor_email' => strtolower((string) ($user['email'] ?? '')),
            'amount' => (int) $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_method_label' => $methodLabels[$validated['payment_method']],
            'payment_status' => 'WAITING_PAYMENT',
            'display_name' => (bool) ($validated['display_name'] ?? false),
            'email_notification' => (bool) ($validated['email_notification'] ?? true),
            'created_at' => now()->format('d M Y, H:i'),
        ];
        $request->session()->put('prototype_checkout_orders', $orders);

        return redirect()->route('donation.payment', ['reference' => $reference]);
    }

    public function payment(Request $request, string $reference)
    {
        $order = $this->findOrder($request, $reference);
        if ($order['payment_status'] === 'SETTLED') {
            return redirect()->route('donation.confirmation', ['reference' => $reference]);
        }

        return view('donation.payment', compact('order'));
    }

    public function simulatePaid(Request $request, string $reference)
    {
        $orders = $request->session()->get('prototype_checkout_orders', []);
        $order = $this->findOrder($request, $reference);

        if ($order['payment_status'] === 'SETTLED') {
            return redirect()->route('donation.confirmation', ['reference' => $reference]);
        }

        $order['payment_status'] = 'SETTLED';
        $order['payment_status_label'] = 'Berhasil';
        $order['transaction_ref'] = 'PG-SIM-'.Str::upper(Str::random(10));
        $order['paid_at'] = now()->format('d M Y, H:i');
        $orders[$reference] = $order;
        $request->session()->put('prototype_checkout_orders', $orders);

        $email = $order['donor_email'];
        $userDonations = $request->session()->get('prototype_user_donations', []);
        $project = collect($this->projects($request))->firstWhere('slug', $order['project_slug']);
        $newCollected = (int) ($request->session()->get('prototype_project_collected', [])[$order['project_slug']] ?? 0) + $order['amount'];
        $collectedByProject = $request->session()->get('prototype_project_collected', []);
        $collectedByProject[$order['project_slug']] = $newCollected;
        $request->session()->put('prototype_project_collected', $collectedByProject);

        $updatedProjects = $this->projects($request);
        $updatedProject = collect($updatedProjects)->firstWhere('slug', $order['project_slug']);
        $userDonations[$email] = $userDonations[$email] ?? [];
        $userDonations[$email][] = [
            'id' => $reference,
            'project_slug' => $order['project_slug'],
            'project_title' => $order['project_title'],
            'researcher' => $order['researcher'],
            'category' => $project['category_label'] ?? 'Penelitian',
            'amount' => $order['amount'],
            'date' => now()->translatedFormat('d F Y'),
            'date_iso' => now()->format('Y-m-d'),
            'payment_method' => $order['payment_method_label'],
            'payment_status' => 'SETTLED',
            'payment_status_label' => 'Berhasil',
            'campaign_status' => $updatedProject['status'] ?? 'ACTIVE',
            'campaign_status_label' => $updatedProject['status_label'] ?? 'Sedang menggalang dana',
            'campaign_progress' => $updatedProject['progress'] ?? 0,
            'deadline' => $project['deadline'] ?? '-',
            'transaction_ref' => $order['transaction_ref'],
            'refund_amount' => 0,
            'refund_reference' => null,
            'verified' => true,
            'has_ledger' => count(DonorPrototypeData::ledgerFor($order['project_slug'])) > 0,
            'milestones' => $project['milestones'] ?? [],
            'created_from_prototype_checkout' => true,
        ];
        $request->session()->put('prototype_user_donations', $userDonations);

        return redirect()->route('donation.confirmation', ['reference' => $reference])
            ->with('success', 'Pembayaran simulasi berhasil. Donasi telah ditambahkan ke riwayat akun prototype.');
    }

    public function confirmation(Request $request, string $reference)
    {
        $order = $this->findOrder($request, $reference);

        return view('donation.confirmation', compact('order'));
    }

    private function findOrder(Request $request, string $reference): array
    {
        $orders = $request->session()->get('prototype_checkout_orders', []);
        $order = $orders[$reference] ?? null;
        $user = $request->session()->get('prototype_user', []);

        abort_unless(is_array($order), 404, 'Transaksi prototype tidak ditemukan.');
        abort_unless(strtolower((string) ($order['donor_email'] ?? '')) === strtolower((string) ($user['email'] ?? '')), 403);

        return $order;
    }

    private function projects(Request $request): array
    {
        return PrototypeCatalog::projects($request->session()->get('prototype_project_collected', []));
    }
}
