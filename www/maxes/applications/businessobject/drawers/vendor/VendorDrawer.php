<?php

class VendorDrawer
{
    public static function getDrawerData()
    {
        try {
            $payload = Super::getParam("getDrawerVendorData");
            $action = $payload['action'] ?? 'list';

            if ($action === 'list') {
                self::getVendorList($payload);
            } elseif ($action === 'detail') {
                self::getVendorDetail($payload);
            } else {
                Super::sendJsonResponse("error", "Unknown drawer action.");
            }
        } catch (Exception $e) {
            Super::sendJsonResponse("error", "Server Error: " . $e->getMessage());
        }
    }

    /**
     * Task 1: Tab List (Server-Side Search)
     */
    private static function getVendorList($payload)
    {
        $search = $payload['search'] ?? '';
        $vendorSet = (new Set())->useTransactionManager()->MboSetRemote("vendor");

        // Pencarian mencakup kode, nama, dan kota
        if (!empty($search)) {
            $vendorSet->where(
                "(vendorcode LIKE ? OR name LIKE ? OR city LIKE ?)", 
                "%$search%", "%$search%", "%$search%"
            );
        }

        $vendors = $vendorSet->MboRemote("vendorcode, name, city, stateprovince, website")
            ->limit(500)
            ->getMboSet()
            ->fetchData();

        Super::sendJsonResponse("success", "Vendor list loaded successfully", $vendors);
    }

    /**
     * Task 2: Tab Detail (Header, Contacts, Services, Items)
     */
    private static function getVendorDetail($payload)
    {
        $vendorcode = $payload['vendorcode'] ?? '';
        if (empty($vendorcode)) {
            throw new InvalidArgumentException("Vendor Code is required.");
        }

        $tm = TransactionManager::getInstance();
        $tm->beginTransaction();

        try {
            // 2.1 Header Vendor
            $header = (new Set())->useTransactionManager()
                ->MboSetRemote("vendor")
                ->where("vendorcode = ?", $vendorcode)
                ->MboRemote("vendorcode, name, bankname1, bankaccount1, bankaddress, accountholder, website, address, city, stateprovince, zippostalcode, vendorfax")
                ->getMboSet()
                ->fetch();

            if (!$header) {
                throw new Exception("Vendor '$vendorcode' not found.");
            }

            // 2.2 Vendor Contacts
            $contacts = (new Set())->useTransactionManager()
                ->MboSetRemote("vendorcontact")
                ->where("vendorcode = ?", $vendorcode)
                ->MboRemote("contact, position, email, phone, officephone")
                ->getMboSet()
                ->fetchData();

            // 2.3 Service Orders (Distinct / Aggregated with Variance)
            // Menggunakan GROUP BY sebagai pengganti DISTINCT agar kita bisa hitung AVG/MAX unitcost-nya
            $sqlServices = "
                SELECT 
                    pl.serviceitemnum, 
                    si.description,
                    COALESCE(si.stdcost, 0) AS stdcost,
                    AVG(pl.unitcost) AS avg_actualcost,
                    (AVG(pl.unitcost) - COALESCE(si.stdcost, 0)) AS varianceunitcost
                FROM poline pl
                JOIN po p ON pl.ponum = p.ponum
                LEFT JOIN serviceitems si ON pl.serviceitemnum = si.serviceitemnum
                WHERE p.vendor = ? AND pl.linetype = 'services'
                GROUP BY pl.serviceitemnum, si.description, si.stdcost
                ORDER BY pl.serviceitemnum ASC
            ";
            $services = Set::fetchAll($sqlServices, [$vendorcode]);

            // 2.4 Item Orders (Distinct / Aggregated)
            $sqlItems = "
                SELECT 
                    pl.itemnum, 
                    i.description,
                    AVG(pl.unitcost) AS avg_unitcost
                FROM poline pl
                JOIN po p ON pl.ponum = p.ponum
                LEFT JOIN item i ON pl.itemnum = i.itemnum
                WHERE p.vendor = ? AND pl.linetype = 'item'
                GROUP BY pl.itemnum, i.description
                ORDER BY pl.itemnum ASC
            ";
            $items = Set::fetchAll($sqlItems, [$vendorcode]);
            $tm->commit();
            
            $appUrl = Super::getApplicationURL("vendor");
            $applinksownernum = "$vendorcode%vendor";
           
            Super::sendJsonResponse("success", "Vendor detail loaded",[
                'header'   => $header,
                'appUrl' => $appUrl,
                'applinksownernum' => $applinksownernum,
                'contacts' => $contacts,
                'services' => $services,
                'items'    => $items
            ]);

        } catch (Exception $e) {
            $tm->rollback();
            throw $e;
        }
    }
}
?>