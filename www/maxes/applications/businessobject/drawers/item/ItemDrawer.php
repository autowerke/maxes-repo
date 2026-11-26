<?php

class ItemDrawer
{
    public static function getDrawerData()
    {
        try {
            $payload = Super::getParam("getDrawerItemData");
            $action = $payload['action'] ?? 'list';

            if ($action === 'list') {
                self::getItemList($payload);
            } elseif ($action === 'detail') {
                self::getItemDetail($payload);
            } else {
                Super::sendJsonResponse("error", "Unknown drawer action.");
            }
        } catch (Exception $e) {
            Super::sendJsonResponse("error", "Server Error: " . $e->getMessage());
        }
    }

    private static function getItemList($payload)
    {
        $search = $payload['search'] ?? ''; // Ambil kata kunci dari JS

        $itemSet = (new Set())->useTransactionManager()->MboSetRemote("item");

        if (!empty($search)) {
            $itemSet->where(
                "(itemnum LIKE ? OR description LIKE ? OR manufacturer LIKE ? OR brand LIKE ? OR itemgroup LIKE ?)", 
                "%$search%", "%$search%", "%$search%", "%$search%", "%$search%"
            );
        }

        $items = $itemSet->MboRemote("itemnum, description, manufacturer, brand, itemgroup, stocktype")
            ->limit(500) // Tetap batasi hasil yang dikirim ke browser
            ->getMboSet()
            ->fetchData();

        Super::sendJsonResponse("success", "Item list loaded successfully", $items);
    }

    private static function getItemDetail($payload)
    {
        $itemnum = $payload['itemnum'] ?? '';
        if (empty($itemnum)) {
            throw new InvalidArgumentException("ItemNum is required.");
        }

        // 1. Header Item
        $header = (new Set())->useTransactionManager()
            ->MboSetRemote("item")
            ->where("itemnum = ?", $itemnum)
            ->getMboSet()
            ->fetch();

        if (!$header) {
            throw new Exception("Item '$itemnum' not found.");
        }

        // 2. Classification
        $classifications = (new Set())->useTransactionManager()
            ->MboSetRemote("itemclassification")
            ->where("itemnum = ?", $itemnum)
            ->MboRemote("attributename, value, unit, remarks")
            ->getMboSet()
            ->fetchData();

        // 3. Item Assembly (Structure)
        $assembly = (new Set())->useTransactionManager()
            ->MboSetRemote("itemstruct")
            ->where("itemnum = ?", $itemnum)
            ->MboRemote("subitemnum, quantity, unitcost, linecost")
            ->getMboSet()
            ->fetchData();

        // 4. Stock (FIFO/LIFO + Inventory Join)
        $sqlStock = "
            SELECT 
                f.itemnum, f.conditiontype, f.costdate, f.batchnum, f.warehouse, f.racknum, 
                f.quantity, f.unitcost, f.ponum, f.expdate,
                i.issuetype, i.safetystock, i.reorderqty
            FROM invfifolifocost f
            LEFT JOIN inventory i ON (i.itemnum = f.itemnum AND i.warehouse = f.warehouse)
            WHERE f.itemnum = ? AND f.quantity > 0
            ORDER BY f.costdate ASC
        ";
        $stockRaw = Set::fetchAll($sqlStock, [$itemnum]);
        
        // Hitung Linecost di PHP sesuai permintaan
        $stock = array_map(function($row) {
            $row['linecost'] = (float)$row['quantity'] * (float)$row['unitcost'];
            return $row;
        }, $stockRaw);

        // 5. Purchasing History (InvVendor + Vendor Join)
        $sqlVendor = "
            SELECT 
                iv.intleadtime, iv.extleadtime, iv.leadtime, iv.lastponum, 
                iv.lastorderdate, iv.lastprice, iv.vendorcode,
                v.name as vendorname, v.contact, v.phone, v.city
            FROM invvendor iv
            LEFT JOIN vendor v ON iv.vendorcode = v.vendorcode
            WHERE iv.itemnum = ?
            ORDER BY iv.lastorderdate DESC
        ";
        $vendors = Set::fetchAll($sqlVendor, [$itemnum]);

        // 6. Production Log
        $production = (new Set())->useTransactionManager()
            ->MboSetRemote("productionlog")
            ->where("itemnum = ?", $itemnum)
            ->MboRemote("issueunit, productiondate, quantity, unitcost, linecost, wonum, batchnum")
            ->order("productiondate DESC")
            ->limit(10)
            ->getMboSet()
            ->fetchData();
        $appUrl = Super::getApplicationURL("item");
        
        $applinksownernum = "$itemnum%item";
        Super::sendJsonResponse("success", "Item detail loaded", [
            'header'          => $header,
            'appUrl' => $appUrl,
            'applinksownernum' => $applinksownernum,
            'classifications' => $classifications,
            'assembly'        => $assembly,
            'stock'           => $stock,
            'vendors'         => $vendors,
            'production'      => $production
        ]);
    }
}