<?php

namespace App\Exports;

use App\Models\WrongNumber;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WrongNumbersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return WrongNumber::where('user_id', auth()->id())
            ->select([
                'phone', 'contact_name', 'workflow_id', 'organisation_id', 'user_id',
                'zipcode', 'state', 'city', 'address', 'offer', 'email',
                'age', 'gender', 'lead_score', 'agent', 'novation',
                'creative_price', 'monthly', 'downpayment', 'created_at'
            ])->get();
    }

    public function headings(): array
    {
        return [
            'Phone', 'Contact Name', 'Workflow ID', 'Organisation ID', 'User ID',
            'Zipcode', 'State', 'City', 'Address', 'Offer', 'Email',
            'Age', 'Gender', 'Lead Score', 'Agent', 'Novation',
            'Creative Price', 'Monthly', 'Downpayment', 'Created At'
        ];
    }
}
