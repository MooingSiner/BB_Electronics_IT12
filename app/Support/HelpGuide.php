<?php

namespace App\Support;

class HelpGuide
{
    /**
     * @return list<array{title: string, summary: string, points: list<string>, image: string}>
     */
    public static function topics(string $role): array
    {
        return $role === 'owner' ? self::owner() : self::cashier();
    }

    /**
     * @return list<array{title: string, summary: string, points: list<string>, image: string}>
     */
    private static function owner(): array
    {
        return [
            ['title' => 'Dashboard', 'image' => 'owner-dashboard', 'summary' => 'A quick view of how the store is doing.', 'points' => [
                'The cards show sales, transactions, and low-stock items for the chosen period.',
                'Use the period buttons to switch between day, week, month, and year.',
                'The line graph shows sales over time, and the lists show top sellers and items to reorder.',
            ]],
            ['title' => 'Sales Transactions', 'image' => 'owner-sales', 'summary' => 'Every sale made at the point of sale.', 'points' => [
                'Search by transaction number, or filter by date.',
                'Click the eye icon to open a sale, print its receipt, or see any returns on it.',
                'Use Void on a sale made by mistake; the stock is returned and you must give a reason.',
            ]],
            ['title' => 'Purchase Orders', 'image' => 'owner-purchase-orders', 'summary' => 'Stock bought from other stores.', 'points' => [
                'Create an order, add the items and prices, then receive it when it arrives.',
                'Receiving an order adds the units to inventory automatically.',
                'An order that has not been received can be cancelled.',
            ]],
            ['title' => 'Supplier Orders', 'image' => 'owner-supplier-orders', 'summary' => 'Orders placed with suppliers.', 'points' => [
                'Record the order, receive the delivery, and report damaged products.',
                'Damaged items can be returned to the supplier and replaced.',
                'Manage the supplier list from this page.',
            ]],
            ['title' => 'Inventory', 'image' => 'owner-inventory', 'summary' => 'All products and their stock levels.', 'points' => [
                'Search by name, code, barcode, or category, and filter by status.',
                'Row buttons: view (eye), edit (blue), Stock In (green), Stock Out (orange), history (clock), archive (red).',
                'Capital Price is shown in code on the cashier side; the owner sees the real amount.',
            ]],
            ['title' => 'Product page', 'image' => 'owner-product', 'summary' => 'Everything about a single product.', 'points' => [
                'Stock In adds units (green), Stock Out removes them (orange) with a reason.',
                'After a stock entry, press Undo in the message that appears to cancel it.',
                'History lists every stock movement; manual entries have a Reverse button.',
                'Print barcode labels for the product from this page.',
            ]],
            ['title' => 'Returns & Warranties', 'image' => 'owner-returns', 'summary' => 'Customer returns, replacements, exchanges, and repairs.', 'points' => [
                'Start a return from a sale, choose the product and the reason.',
                'Choose refund, replacement, exchange for another product, or repair.',
                'Approve requests made by cashiers, then print the return slip.',
            ]],
            ['title' => 'Reports', 'image' => 'owner-reports', 'summary' => 'Sales, inventory, and procurement reports.', 'points' => [
                'Pick the report type and the date range.',
                'Refunds are subtracted from revenue.',
                'Use Print to print the report.',
            ]],
            ['title' => 'Audit Log', 'image' => 'owner-audit', 'summary' => 'A record of important actions.', 'points' => [
                'Shows who did what and when: voids, refunds, price changes, and stock changes.',
                'It cannot be edited.',
            ]],
            ['title' => 'User Management', 'image' => 'owner-users', 'summary' => 'Accounts for owners and cashiers.', 'points' => [
                'Add a user and choose the role.',
                'Deactivate an account to block sign-in without deleting its records.',
            ]],
        ];
    }

    /**
     * @return list<array{title: string, summary: string, points: list<string>, image: string}>
     */
    private static function cashier(): array
    {
        return [
            ['title' => 'Point of Sale', 'image' => 'cashier-pos', 'summary' => 'Where sales are made.', 'points' => [
                'Scan a barcode or type a name in the search box, or tap a product to add it to the cart.',
                'Change quantities in the cart, add a discount if needed, and choose the payment method.',
                'Press Complete Sale, then print the receipt.',
            ]],
            ['title' => 'Sales Transactions', 'image' => 'cashier-sales', 'summary' => 'Sales you have made.', 'points' => [
                'Search by transaction number.',
                'Open a sale to see its items, reprint the receipt, or start a return.',
            ]],
            ['title' => 'Inventory', 'image' => 'cashier-inventory', 'summary' => 'Check stock without changing it.', 'points' => [
                'Search for a product to see its price and how many are in stock.',
                'Items marked Low Stock or Out of Stock need the owner to reorder.',
            ]],
            ['title' => 'Returns & Warranties', 'image' => 'cashier-returns', 'summary' => 'Handle customer returns.', 'points' => [
                'Find the sale, choose the item, and record the reason.',
                'A refund, replacement, or exchange needs the owner\'s approval before stock changes.',
                'Print the return slip for the customer.',
            ]],
            ['title' => 'Dashboard', 'image' => 'cashier-dashboard', 'summary' => 'Your day at a glance.', 'points' => [
                'Shows your sales for today and recent transactions.',
            ]],
        ];
    }
}
