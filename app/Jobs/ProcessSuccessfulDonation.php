<?php

namespace App\Jobs;

use App\Models\Donation;
use App\Models\Campaign;
use App\Mail\DonationReceiptMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ProcessSuccessfulDonation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $donationId)
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $donation = Donation::with(['user', 'campaign'])->find($this->donationId);

        if (!$donation || $donation->status !== 'success') {
            Log::warning("ProcessSuccessfulDonation: Donation {$this->donationId} not found or not success.");
            return;
        }

        DB::transaction(function () use ($donation) {
            // 1. Update Campaign Stats
            $campaign = Campaign::findOrFail($donation->campaign_id);
            $campaign->increment('collected_amount', $donation->amount);
            $campaign->increment('donor_count');

            // 2. Generate PDF Invoice
            $pdf = Pdf::loadView('pdf.invoice', ['donation' => $donation]);
            $pdfContent = $pdf->output();

            // 3. Upload Invoice to R2
            $filename = 'invoices/' . $donation->donation_number . '.pdf';
            Storage::disk('r2')->put($filename, $pdfContent);
            $invoiceUrl = Storage::disk('r2')->url($filename);

            // 4. Update Donation record with invoice URL
            $donation->update(['invoice_url' => $invoiceUrl]);

            // 5. Send Email to Donor (if email exists)
            if ($donation->user && $donation->user->email) {
                Mail::to($donation->user->email)->send(new DonationReceiptMail($donation, $pdfContent));
            }

            Log::info("ProcessSuccessfulDonation: Campaign {$campaign->id} updated and invoice sent for donation {$donation->id}.");
        });
    }
}
