<?php
require_once ("../../../eat.php");

Class ActionSet {


    public static function drawKgraph() {
        $payload = Super::getParam("drawKgraph");
        $domain = $payload["domainname"];

        // 1. Ambil Data Tabel
        $tables = (new Set())->useTransactionManager()->MboSetRemote("mxsaitables t")
            ->setRelationship("mxsaitabledomains d", "t.tablename = d.tablename")
            ->where("d.domainname = ?", $domain)
            ->MboRemote("t.tablename, t.description, t.isentrypoint")
            ->getMboSet()->fetchData();

        // 2. Ambil Data Relasi
        $relations = (new Set())->useTransactionManager()->MboSetRemote("mxsairelations")
            ->where("parentdomain = ?", $domain)
            ->getMboSet()->fetchData();

        // 3. Ambil Data Kolom
        $columns = (new Set())->useTransactionManager()->MboSetRemote("mxsaicolumns")
            ->where("domainname = ?", $domain)
            ->getMboSet()->fetchData();

        $nodes = [];
        $links = [];
        $validNodeIds = []; // Array untuk tracking ID yang benar-benar ada

        // Format Nodes TABEL
        foreach ($tables as $t) {
            $id = (string)$t['tablename'];
            $validNodeIds[] = $id; // Daftarkan ID tabel
            
            $isEntry = ($t['isentrypoint'] == 'Y');
            $nodes[] = [
                "id" => $id,
                "name" => strtoupper($id),
                "symbolSize" => $isEntry ? 50 : 35,
                "category" => 0,
                "description" => $t['description']
            ];
        }

        // Format Nodes KOLOM
        // Format Nodes KOLOM - gunakan $colId sebagai key untuk prevent duplicate
        $columnNodes = [];
        foreach ($columns as $c) {
            $id = (string)$c['tablename'];
            if (in_array($id, $validNodeIds)) {
                $colId = $id . "." . $c['columnname'];
                
                // Skip jika colId sudah pernah ditambahkan
                if (isset($columnNodes[$colId])) continue;
                
                $columnNodes[$colId] = true;
                $validNodeIds[] = $colId;

                $nodes[] = [
                    "id" => $colId,
                    "name" => (string)$c['columnname'],
                    "symbolSize" => 10,
                    "category" => 1,
                    "description" => $c['description']
                ];

                $links[] = [
                    "source" => $id,
                    "target" => $colId,
                    "lineStyle" => ["width" => 1, "type" => "dashed"],
                    "isRelation" => false
                ];
            }
        }

        // Format Links RELASI ANTAR TABEL
        foreach ($relations as $r) {
            $src = (string)$r['parenttable'];
            $tgt = (string)$r['childtable'];

            // JARING PENGAMAN: Cek apakah source DAN target ada di daftar nodes
            if (in_array($src, $validNodeIds) && in_array($tgt, $validNodeIds)) {
                $links[] = [
                    "source" => $src,
                    "target" => $tgt,
                    "label" => ["show" => true, "formatter" => (string)$r['relationname']],
                    "lineStyle" => ["width" => 3, "curveness" => 0.1],
                    "isRelation" => true
                ];
            }
        }

        Set::throws([
            "status" => "success",
            "data" => ["nodes" => $nodes, "links" => $links]
        ]);
    }
}

Class ValidateTables {
	
	
}
	

?>