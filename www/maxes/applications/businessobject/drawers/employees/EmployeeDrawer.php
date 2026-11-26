<?php

class EmployeeDrawer
{
    public static function getDrawerData()
    {
        try {
            $payload = Super::getParam("getDrawerEmployeeData");
            $action = $payload['action'] ?? 'list';

            if ($action === 'list') {
                self::getEmployeeList($payload);
            } elseif ($action === 'detail') {
                self::getEmployeeDetail($payload);
            } else {
                Super::sendJsonResponse("error", "Unknown drawer action.");
            }
        } catch (Exception $e) {
            Super::sendJsonResponse("error", "Server Error: " . $e->getMessage());
        }
    }

     private static function getEmployeeList($payload)
    {
        $search = $payload['search'] ?? '';

        $empSet = (new Set())->useTransactionManager()->MboSetRemote("employees");

        // LOGIKA SERVER-SIDE SEARCH
        if (!empty($search)) {
            $empSet->where(
                "(personid LIKE ? OR fullname LIKE ? OR title LIKE ? OR department LIKE ? OR section LIKE ?)", 
                "%$search%", "%$search%", "%$search%", "%$search%", "%$search%"
            );
        }

        $employees = $empSet->MboRemote("personid, fullname, title, department, section, primaryphone, primaryemail")
        ->limit(500)
        ->getMboSet()
        ->fetchData();

        Super::sendJsonResponse("success", "Employee list loaded successfully", $employees);
    }

    private static function getEmployeeDetail($payload)
    {
        $personid = $payload['personid'] ?? '';
        if (empty($personid)) {
            throw new InvalidArgumentException("PersonID is required.");
        }

        // 1. Header Employee
        $header = (new Set())->useTransactionManager()
            ->MboSetRemote("employees")
            ->where("personid = ?", $personid)
            ->getMboSet()
            ->fetch();

        if (!$header) {
            throw new Exception("Employee '$personid' not found.");
        }

        // 2. TEAM Membership
        $teams = (new Set())->useTransactionManager()
            ->MboSetRemote("empteam")
            ->where("personid = ?", $personid)
            ->MboRemote("teamnum, leader")
            ->getMboSet()
            ->fetchData();

        // 3. Qualifications (JOIN)
        $sqlQual = "
            SELECT eq.qualification, eq.remarks, q.description, q.skill, q.certificate
            FROM empqual eq
            LEFT JOIN qualification q ON eq.qualification = q.qualification
            WHERE eq.personid = ?
        ";
        $qualifications = Set::fetchAll($sqlQual, [$personid]);

        // 4. On Planning Work Order (Individu ATAU Team Member)
        $teamList = array_column($teams, 'teamnum');
        $planning = (new Set())->useTransactionManager()
            ->MboSetRemote("woplanlabor");
        
        if (!empty($teamList)) {
            // Jika dia punya tim, cek personidnya ATAU timnya
            $planning->where("(personid = ? OR teamnum IN (" . rtrim(str_repeat('?,', count($teamList)), ',') . "))", $personid, ...$teamList);
        } else {
            $planning->where("personid = ?", $personid);
        }
        
        $planningData = $planning->MboRemote("wonum, startdate, enddate, reghours, prelinecost")
            ->order("startdate DESC")
            ->getMboSet()
            ->fetchData();

        // 5. On Running Work Order (Labor Trans)
        $running = (new Set())->useTransactionManager()
            ->MboSetRemote("labortransline")
            ->where("personid = ?", $personid)
            ->MboRemote("wonum, startdate, enddate, reghours, extendedhours, unitcost, linecost")
            ->order("startdate DESC")
            ->limit(10)
            ->getMboSet()
            ->fetchData();

        // 6. Leave Status (Current & Future)
        $today = date('Y-m-d');
        $leaves = (new Set())->useTransactionManager()
            ->MboSetRemote("resourceleave")
            ->where("personid = ? AND enddate >= ?", $personid, $today)
            ->MboRemote("startdate, enddate, leavetype, description")
            ->order("startdate ASC")
            ->getMboSet()
            ->fetchData();
        
        $appUrl = Super::getApplicationURL("employees");
        $applinksownernum = "$personid%employees";
        Super::sendJsonResponse("success", "Employee detail loaded", [
            'header'         => $header,
            'applinksownernum' => $applinksownernum,
            'appUrl' => $appUrl,
            'teams'          => $teams,
            'qualifications' => $qualifications,
            'planning'       => $planningData,
            'running'        => $running,
            'leaves'         => $leaves
        ]);
    }
}