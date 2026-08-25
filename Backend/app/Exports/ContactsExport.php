<?php

namespace App\Exports;

use App\Models\PhonebookContact;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;

class ContactsExport implements FromCollection, WithHeadings
{
    protected $phonebookId;
    protected $businessId;

    public function __construct($phonebookId = null, $businessId = null)
    {
        $this->phonebookId = $phonebookId;
        $this->businessId  = $businessId ?? Auth::user()->business_id;
    }

    public function collection()
    {
        $query = PhonebookContact::query()
            ->join('phonebooks', 'phonebooks.id', '=', 'phonebook_contacts.phonebook_id')
            ->where('phonebooks.business_id', $this->businessId)
            ->where('phonebook_contacts.status', 1)
            ->select('phonebook_contacts.name', 'phonebook_contacts.phone', 'phonebook_contacts.email');

        if ($this->phonebookId && $this->phonebookId != 100000000000000) {
            $query->where('phonebook_contacts.phonebook_id', $this->phonebookId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['Name', 'Phone', 'Email'];
    }
}
