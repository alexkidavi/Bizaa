<?php

namespace App\Exports;

use App\Models\PhonebookContact;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class ContactsExport implements FromCollection, WithHeadings, WithMapping
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

    /**
     * Contact name/phone/email are user-submitted — a value starting with
     * =, +, -, or @ would be interpreted as a formula by Excel/LibreOffice
     * when the exported file is opened (CSV/formula injection). Prefixing
     * a leading single quote forces it to render as literal text instead.
     */
    public function map($contact): array
    {
        return [
            $this->escapeFormula($contact->name),
            $this->escapeFormula($contact->phone),
            $this->escapeFormula($contact->email),
        ];
    }

    private function escapeFormula(?string $value): ?string
    {
        if ($value !== null && preg_match('/^[=+\-@]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }
}
