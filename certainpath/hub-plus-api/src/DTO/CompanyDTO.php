<?php

namespace App\DTO;

class CompanyDTO
{
    public string $account;
    public string $state;
    public string $country;
    public string $accountStatus;
    public string $company;
    public ?string $softwareId;
    public string $product;
    public string $subscriptionStatus;
    public string $frequency;
    public string $start;
    public ?string $end;
    public ?string $scheduledInvoice;
    public string $subscriptionPaymentCollection;
    public ?float $totalBalance;
    public ?float $totalBalancePaused;
    public ?string $paymentSource;
    public ?string $brand;
    public ?string $last4;
    public ?string $label;
    public ?float $lastInvoiceDueAmount;
    public ?float $lastInvoicePaidAmount;
    public ?string $lastInvoiceDate;

    public function __construct(array $data)
    {
        $this->account = $data['Account'];
        $this->state = $data['State'];
        $this->country = $data['Country'];
        $this->accountStatus = $data['Account Status'];
        $this->company = $data['Company'];
        $this->softwareId = $data['Software ID'];
        $this->product = $data['Product'];
        $this->subscriptionStatus = $data['Subscription Status'];
        $this->frequency = $data['Frequency'];
        $this->start = $data['Start'];
        $this->end = $data['End'];
        $this->scheduledInvoice = $data['Scheduled Invoice'];
        $this->subscriptionPaymentCollection = $data['Subscription Payment Collection'];
        $this->totalBalance = $data['Total Balance'];
        $this->totalBalancePaused = $data['Total Balance Paused'];
        $this->paymentSource = $data['Payment Source'];
        $this->brand = $data['Brand'];
        $this->last4 = $data['Last 4'];
        $this->label = $data['Label'];
        $this->lastInvoiceDueAmount = $data['Last Invoice Due Amount'];
        $this->lastInvoicePaidAmount = $data['Last Invoice Paid Amount'];
        $this->lastInvoiceDate = $data['Last Invoice Date'];
    }
}
