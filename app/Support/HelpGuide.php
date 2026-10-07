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
     * @return list<array{title: string, steps: list<array{text: string, image: string}>}>
     */
    public static function guides(string $role): array
    {
        return $role === 'owner' ? self::ownerGuides() : self::cashierGuides();
    }

    /**
     * @return list<array{title: string, steps: list<array{text: string, image: string}>}>
     */
    private static function ownerGuides(): array
    {
        return [
            ['title' => 'Add a new product', 'steps' => [
                ['text' => 'Open Inventory and press + Add Product.', 'image' => 'o-add-1'],
                ['text' => 'Type the product name, then the unit price and the starting quantity. Fill in the other boxes if you have them, then press Add Product.', 'image' => 'o-add-2'],
            ]],
            ['title' => 'Stock In (one product)', 'steps' => [
                ['text' => 'In Inventory, press the green arrow on the product\'s row.', 'image' => 'o-in-1'],
                ['text' => 'Enter how many units arrived and a short reason, then press Add to Stock. A message with an Undo button appears in case you made a mistake.', 'image' => 'o-in-2'],
            ]],
            ['title' => 'Stock In (many products)', 'steps' => [
                ['text' => 'In Inventory, press the Stock In button at the top.', 'image' => 'o-bulk-1'],
                ['text' => 'Scan or search each product, enter the quantity for each one, then press Add to Stock.', 'image' => 'o-bulk-2'],
            ]],
            ['title' => 'Stock Out', 'steps' => [
                ['text' => 'In Inventory, press the orange arrow on the product\'s row.', 'image' => 'o-out-1'],
                ['text' => 'Enter the quantity to remove and the reason (for example breakage or a miscount), then press Remove from Stock.', 'image' => 'o-out-2'],
            ]],
            ['title' => 'Undo a stock entry', 'steps' => [
                ['text' => 'Open the product and press History.', 'image' => 'o-undo-1'],
                ['text' => 'Press Reverse beside the entry you typed by mistake. The original stays in the list and a reversal is added, so nothing is hidden.', 'image' => 'o-undo-2'],
            ]],
            ['title' => 'Print barcode labels', 'steps' => [
                ['text' => 'Open the product and press Print label under the barcode.', 'image' => 'o-label-1'],
                ['text' => 'Choose how many copies and the size, then press Print.', 'image' => 'o-label-2'],
            ]],
            ['title' => 'Void a sale', 'steps' => [
                ['text' => 'In Sales Transactions, press the eye icon on the sale.', 'image' => 'o-void-1'],
                ['text' => 'Press Void Sale and give a reason. The items go back to stock. A sale that already has a return cannot be voided.', 'image' => 'o-void-2'],
            ]],
            ['title' => 'Create a purchase order', 'steps' => [
                ['text' => 'In Purchase Orders, press + New Purchase Order.', 'image' => 'o-po-1'],
                ['text' => 'Enter the store, choose each product with its quantity and cost, then press Submit Order.', 'image' => 'o-po-2'],
                ['text' => 'Open an order to receive the delivery, report damaged items, or print the receipt.', 'image' => 'o-po-3'],
            ]],
            ['title' => 'Process a customer return', 'steps' => [
                ['text' => 'Open the sale and press Process Return.', 'image' => 'o-ret-1'],
                ['text' => 'Choose the product and quantity, the reason, the item condition, and what the customer wants (refund, replacement, exchange or repair), then press Process Return.', 'image' => 'o-ret-2'],
            ]],
            ['title' => 'Make a report', 'steps' => [
                ['text' => 'In Reports, choose the report type and the dates, then press Generate Report.', 'image' => 'o-rep-1'],
                ['text' => 'Press Print to print the report.', 'image' => 'o-rep-2'],
            ]],
            ['title' => 'Add or edit a user', 'steps' => [
                ['text' => 'In User Management, press + Add User, fill in the details and save.', 'image' => 'o-user-1'],
                ['text' => 'Use the pencil to edit a user, or the power button to deactivate or reactivate the account.', 'image' => 'o-user-2'],
            ]],
        ];
    }

    /**
     * @return list<array{title: string, steps: list<array{text: string, image: string}>}>
     */
    private static function cashierGuides(): array
    {
        return [
            ['title' => 'Make a sale', 'steps' => [
                ['text' => 'Scan the barcode or type in the search box, then tap a product to add it. Tapping it again adds one more.', 'image' => 'c-sale-1'],
                ['text' => 'Press the Cart button. Change quantities, pick a discount if needed, choose the payment method, enter the amount received, then press Complete Sale and print the receipt.', 'image' => 'c-sale-2'],
            ]],
            ['title' => 'Reprint a receipt', 'steps' => [
                ['text' => 'In Sales Transactions, press the eye icon on the sale (or the printer icon to print straight away).', 'image' => 'c-rec-1'],
                ['text' => 'Press Print Receipt.', 'image' => 'c-rec-2'],
            ]],
            ['title' => 'Start a customer return', 'steps' => [
                ['text' => 'Open the sale and press Process Return.', 'image' => 'c-ret-1'],
                ['text' => 'Choose the product and quantity, give the reason and condition, pick what the customer wants, then press Submit Return. The owner approves it before stock changes.', 'image' => 'c-ret-2'],
            ]],
            ['title' => 'Check stock and price', 'steps' => [
                ['text' => 'In Inventory, type a name or code in the search box. The list shows the price and how many are in stock.', 'image' => 'c-inv-1'],
            ]],
        ];
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
