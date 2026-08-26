<?php

namespace App\Services;

/**
 * Central definition of every exportable report: its title, which key in
 * the ReportsService result holds the row list, and the column set to
 * flatten each row into for Excel/PDF. Keeping this in one place means
 * the Excel export class and the PDF template both drive off the exact
 * same definitions instead of duplicating column lists twice.
 *
 * 'close-day' and 'account-statement' have their own dedicated builders
 * (ReportExportRegistry::closeDayRows()/accountStatementRows()) since
 * their shape isn't a flat "list of rows with a summary" the way every
 * other report is.
 */
class ReportExportRegistry
{
    public static function definitions(): array
    {
        return [
            'items-sale' => [
                'label'   => 'Items Sale Report',
                'rowsKey' => 'items',
                'columns' => [
                    'product_name'   => 'Product',
                    'product_code'   => 'SKU',
                    'qty_sold'       => 'Qty Sold',
                    'avg_price'      => 'Avg Price',
                    'total_discount' => 'Discount',
                    'net_revenue'    => 'Net Revenue',
                    'total_cost'     => 'Cost',
                    'gross_profit'   => 'Profit',
                ],
            ],
            'sales-customer' => [
                'label'   => 'Sales by Customer',
                'rowsKey' => 'customers',
                'columns' => [
                    'customer_name'  => 'Customer',
                    'phone'          => 'Phone',
                    'total_sales'    => 'Sales',
                    'gross_total'    => 'Gross Total',
                    'total_discount' => 'Discount',
                    'total_paid'     => 'Paid',
                    'balance_due'    => 'Balance',
                ],
            ],
            'sales-branch' => [
                'label'   => 'Sales by Branch',
                'rowsKey' => 'branches',
                'columns' => [
                    'branch_name'    => 'Branch',
                    'location'       => 'Location',
                    'total_sales'    => 'Sales',
                    'gross_total'    => 'Gross Total',
                    'total_discount' => 'Discount',
                    'total_paid'     => 'Paid',
                    'balance_due'    => 'Balance',
                ],
            ],
            'items-ledger' => [
                'label'   => 'Items Ledger',
                'rowsKey' => 'ledger',
                'columns' => [
                    'date'          => 'Date',
                    'type'          => 'Type',
                    'reference'     => 'Reference',
                    'product_name'  => 'Product',
                    'product_code'  => 'SKU',
                    'qty_in'        => 'In',
                    'qty_out'       => 'Out',
                    'amount'        => 'Amount',
                ],
            ],
            'customer-balances' => [
                'label'   => 'Customer Balances',
                'rowsKey' => 'customers',
                'columns' => [
                    'name'           => 'Customer',
                    'phone'          => 'Phone',
                    'email'          => 'Email',
                    'total_invoiced' => 'Invoiced',
                    'total_paid'     => 'Paid',
                    'outstanding'    => 'Outstanding',
                ],
            ],
            'supplier-balances' => [
                'label'   => 'Supplier Balances',
                'rowsKey' => 'suppliers',
                'columns' => [
                    'name'             => 'Supplier',
                    'phone'            => 'Phone',
                    'email'            => 'Email',
                    'total_purchased'  => 'Purchased',
                    'total_paid'       => 'Paid',
                    'outstanding'      => 'Outstanding',
                ],
            ],
            'general-sales' => [
                'label'   => 'General Sales Report',
                'rowsKey' => 'sales',
                'columns' => [
                    'reference'      => 'Reference',
                    'date'           => 'Date',
                    'branch_name'    => 'Branch',
                    'customer_name'  => 'Customer',
                    'cashier_name'   => 'Cashier',
                    'items_qty'      => 'Items',
                    'total'          => 'Total',
                    'paid'           => 'Paid',
                    'balance'        => 'Balance',
                    'payment_status' => 'Status',
                ],
            ],
            'revenue-item' => [
                'label'   => 'Revenue per Item',
                'rowsKey' => 'items',
                'columns' => [
                    'product_name'       => 'Product',
                    'category_name'      => 'Category',
                    'qty_sold'           => 'Qty Sold',
                    'revenue'            => 'Revenue',
                    'revenue_percentage' => '% of Total',
                ],
            ],
            'items-purchase' => [
                'label'   => 'Items Purchase Report',
                'rowsKey' => 'items',
                'columns' => [
                    'product_name'    => 'Product',
                    'product_code'    => 'SKU',
                    'purchase_count'  => 'Purchases',
                    'qty_purchased'   => 'Qty Purchased',
                    'avg_unit_price'  => 'Avg Unit Price',
                    'total_cost'      => 'Total Cost',
                ],
            ],
            'profit' => [
                'label'   => 'Profit Report',
                'rowsKey' => 'items',
                'columns' => [
                    'product_name' => 'Product',
                    'product_code' => 'SKU',
                    'qty_sold'     => 'Qty Sold',
                    'revenue'      => 'Revenue',
                    'cost'         => 'Cost',
                    'profit'       => 'Profit',
                ],
            ],
            'staff-sales' => [
                'label'   => 'Staff Sales Report',
                'rowsKey' => 'staff',
                'columns' => [
                    'staff_name'     => 'Staff',
                    'total_sales'    => 'Sales',
                    'gross_total'    => 'Gross Total',
                    'total_discount' => 'Discount',
                    'total_paid'     => 'Paid',
                    'avg_sale_value' => 'Avg Sale',
                ],
            ],
            'expenses' => [
                'label'   => 'General Expenses Report',
                'rowsKey' => 'expenses',
                'columns' => [
                    'date'            => 'Date',
                    'category_name'   => 'Category',
                    'branch_name'     => 'Branch',
                    'description'     => 'Description',
                    'amount'          => 'Amount',
                    'amount_paid'     => 'Paid',
                    'balance'         => 'Balance',
                    'payment_status'  => 'Status',
                    'payment_method'  => 'Method',
                    'recorded_by'     => 'Recorded By',
                ],
            ],
            'sale-payments' => [
                'label'   => 'Sale Payments',
                'rowsKey' => 'payments',
                'columns' => [
                    'reference'    => 'Sale Ref',
                    'party_name'   => 'Customer',
                    'party_phone'  => 'Phone',
                    'payment_method' => 'Method',
                    'amount'       => 'Amount',
                    'received_by'  => 'Received By',
                    'created_at'   => 'Date',
                ],
            ],
            'purchase-payments' => [
                'label'   => 'Purchase Payments',
                'rowsKey' => 'payments',
                'columns' => [
                    'purchase_ref'   => 'Purchase Ref',
                    'party_name'     => 'Supplier',
                    'party_phone'    => 'Phone',
                    'payment_method' => 'Method',
                    'amount'         => 'Amount',
                    'paid_by'        => 'Paid By',
                    'created_at'     => 'Date',
                ],
            ],
        ];
    }

    public static function get(string $reportType): ?array
    {
        return self::definitions()[$reportType] ?? null;
    }

    /**
     * Flattens one row (array or object) down to the definition's column
     * set, in column order — used by both the Excel and PDF generators.
     */
    public static function flattenRow(array $row, array $columns): array
    {
        $out = [];
        foreach (array_keys($columns) as $key) {
            $value = $row[$key] ?? '';
            $out[] = is_bool($value) ? ($value ? 'Yes' : 'No') : (string) $value;
        }
        return $out;
    }
}
