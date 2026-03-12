<?php

namespace App\Livewire;

use App\Models\Campaign;
use App\Models\Fund;
use App\Services\Payment\PaymentService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Donation extends Component
{
    public float $amount = 1000;

    public string $currency = 'XOF';

    public ?int $fundId = null;

    public ?string $campaignId = null;

    public string $customerName = '';

    public string $customerEmail = '';

    public string $phoneNumber = '';

    public string $paymentMethod = 'momo';

    public ?string $description = '';

    public bool $isProcessing = false;

    public ?string $errorMessage = null;

    protected $rules = [
        'amount' => 'required|numeric|min:100',
        'currency' => 'required|string|size:3',
        'customerName' => 'required|string|max:255',
        'customerEmail' => 'required|email|max:255',
        'phoneNumber' => 'required|string|max:50',
        'fundId' => 'nullable|exists:funds,id',
        'paymentMethod' => 'required|string',
    ];

    public function mount(?int $fund = null, ?string $campaign = null, ?float $amount = null): void
    {
        if ($amount) {
            $this->amount = $amount;
        }

        $this->fundId = $fund;
        $this->campaignId = $campaign;
        $this->currency = config('payments.default_currency', 'XOF');

        if ($campaign) {
            $campaignModel = Campaign::where('slug', $campaign)->orWhere('id', $campaign)->first();
            if ($campaignModel) {
                $this->fundId = $campaignModel->fund_id;
                $this->description = 'Donation for '.$campaignModel->name;
            }
        }
    }

    public function donate(PaymentService $service): void
    {
        $this->validate();
        $this->isProcessing = true;
        $this->errorMessage = null;

        try {
            $result = $service->initiatePayment([
                'amount' => $this->amount,
                'currency' => $this->currency,
                'fund_id' => $this->fundId,
                'campaign_id' => $this->campaignId,
                'customer_name' => $this->customerName,
                'customer_email' => $this->customerEmail,
                'phone_number' => $this->phoneNumber,
                'payment_method' => $this->paymentMethod,
                'description' => $this->description ?: 'Offering',
                'return_url' => route('donation.success'),
            ]);

            if ($result['status'] === 'failed') {
                $this->errorMessage = $result['failure_reason'];
                $this->isProcessing = false;

                return;
            }

            if (! empty($result['payment_url'])) {
                $this->redirect($result['payment_url']);
            } else {
                $this->errorMessage = 'Could not generate payment URL.';
                $this->isProcessing = false;
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'An unexpected error occurred: '.$e->getMessage();
            $this->isProcessing = false;
        }
    }

    public function render(): View
    {
        return view('livewire.donation', [
            'funds' => Fund::all(),
        ])->layout('layouts.guest');
    }
}
