<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("CREATE TRIGGER trg_saleitem_after_insert AFTER INSERT ON sale_item FOR EACH ROW BEGIN UPDATE product SET quantity_on_hand = quantity_on_hand - NEW.quantity WHERE product_id = NEW.product_id; END");
        DB::unprepared("CREATE TRIGGER trg_saleitem_after_delete AFTER DELETE ON sale_item FOR EACH ROW BEGIN UPDATE product SET quantity_on_hand = quantity_on_hand + OLD.quantity WHERE product_id = OLD.product_id; END");
        DB::unprepared("CREATE TRIGGER trg_orderitem_after_update AFTER UPDATE ON order_item FOR EACH ROW BEGIN IF NEW.quantity_received <> OLD.quantity_received THEN UPDATE product SET quantity_on_hand = quantity_on_hand + (NEW.quantity_received - OLD.quantity_received) WHERE product_id = NEW.product_id; END IF; END");
        DB::unprepared("CREATE TRIGGER trg_stockadjustment_after_insert AFTER INSERT ON stock_adjustment FOR EACH ROW BEGIN UPDATE product SET quantity_on_hand = quantity_on_hand + NEW.quantity_change WHERE product_id = NEW.product_id; END");

        DB::statement("CREATE VIEW v_low_stock AS SELECT p.product_id, p.product_name, c.category_name, p.quantity_on_hand, p.reorder_level FROM product p JOIN category c ON c.category_id = p.category_id WHERE p.quantity_on_hand <= p.reorder_level AND p.is_active = 1");
        DB::statement("CREATE VIEW v_product_movement AS SELECT p.product_id, p.product_name, COALESCE(SUM(si.quantity),0) AS units_sold_30d, CASE WHEN COALESCE(SUM(si.quantity),0) >= 20 THEN 'fast_moving' ELSE 'slow_moving' END AS movement FROM product p LEFT JOIN sale_item si ON si.product_id = p.product_id LEFT JOIN sale s ON s.sale_id = si.sale_id AND s.sale_date >= CURDATE() - INTERVAL 30 DAY AND s.status = 'completed' GROUP BY p.product_id, p.product_name");
        DB::statement("CREATE VIEW v_daily_sales AS SELECT CAST(sale_date AS DATE) AS sale_day, COUNT(*) AS transaction_count, SUM(total_amount) AS total_sales FROM sale WHERE status = 'completed' GROUP BY CAST(sale_date AS DATE)");
        DB::statement("CREATE VIEW v_open_cases AS SELECT 'return' AS case_type, r.return_id AS case_id, r.product_id, r.reason, r.status, r.return_date AS opened_on FROM return_record r WHERE r.status = 'open' UNION ALL SELECT 'warranty' AS case_type, w.warranty_id AS case_id, si.product_id, w.claim_status AS reason, w.claim_status AS status, w.claim_date AS opened_on FROM warranty w JOIN sale_item si ON si.sale_item_id = w.sale_item_id WHERE w.claim_status IN ('claimed','in_progress')");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_open_cases');
        DB::statement('DROP VIEW IF EXISTS v_daily_sales');
        DB::statement('DROP VIEW IF EXISTS v_product_movement');
        DB::statement('DROP VIEW IF EXISTS v_low_stock');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_stockadjustment_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orderitem_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_saleitem_after_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_saleitem_after_insert');
    }
};
