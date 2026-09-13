<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

try {
    require_once INCLUDES_PATH . '/sheets.php';
    $sheets = new GoogleSheetsHelper();
    
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            throw new Exception("Invalid JSON input");
        }

        // Validate required fields
        $required = [
            'family_member_id', 'loan_name', 'lender', 'loan_type', 
            'principal_amount', 'interest_rate', 'interest_type', 
            'tenure_months', 'start_date', 'emi_amount', 'balance_calculation_method'
        ];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Generate unique ID
        $id = 'LOAN-' . date('Ym') . '-' . uniqid();
        
        // Prepare row data matching the LOANS schema
        // ['loan_id', 'family_member_id', 'loan_name', 'lender', 'loan_type', 'principal_amount', 'interest_rate', 'interest_type', 'tenure_months', 'start_date', 'emi_amount', 'total_paid', 'principal_paid', 'interest_paid', 'outstanding_principal', 'next_emi_date', 'status', 'balance_calculation_method', 'notes']
        $row = [
            $id,
            $data['family_member_id'],
            $data['loan_name'],
            $data['lender'],
            $data['loan_type'],
            $data['principal_amount'],
            $data['interest_rate'],
            $data['interest_type'],
            $data['tenure_months'],
            $data['start_date'],
            $data['emi_amount'],
            0, // total_paid initially 0
            0, // principal_paid initially 0
            0, // interest_paid initially 0
            $data['principal_amount'], // outstanding_principal initially equal to principal
            isset($data['next_emi_date']) && !empty($data['next_emi_date']) ? $data['next_emi_date'] : $data['start_date'],
            'Active', // status
            $data['balance_calculation_method'],
            isset($data['notes']) ? $data['notes'] : ''
        ];

        $sheets->appendRow('LOANS!A:S', $row);
        
        echo json_encode(['success' => true, 'message' => 'Loan added successfully', 'id' => $id]);
    } else if ($method === 'GET') {
        $records = $sheets->getSheetData('LOANS!A:S');
        
        if (empty($records)) {
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }

        $headers = array_shift($records);
        $result = [];
        
        foreach ($records as $row) {
            $item = [];
            foreach ($headers as $index => $key) {
                $item[strtolower(trim($key))] = isset($row[$index]) ? $row[$index] : '';
            }
            $result[] = $item;
        }
        
        echo json_encode(['success' => true, 'data' => $result]);
    } else if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['id'])) {
            throw new Exception("Invalid JSON input or missing loan ID");
        }

        $id = $data['id'];
        $rowIndex = $sheets->findRowIndexById('LOANS!A:A', $id);
        
        if ($rowIndex === false) {
            throw new Exception("Loan not found");
        }

        // Fetch existing row to preserve history
        $existingRows = $sheets->getSheetData('LOANS!A' . ($rowIndex + 1) . ':S' . ($rowIndex + 1));
        if (empty($existingRows)) {
            throw new Exception("Could not fetch existing loan data");
        }
        $existingRow = $existingRows[0];
        
        $totalPaid = isset($existingRow[11]) ? (float)$existingRow[11] : 0;
        $principalPaid = isset($existingRow[12]) ? (float)$existingRow[12] : 0;
        $interestPaid = isset($existingRow[13]) ? (float)$existingRow[13] : 0;
        
        // Recalculate outstanding principal based on new principal amount
        $newPrincipalAmount = isset($data['principal_amount']) ? (float)$data['principal_amount'] : (float)$existingRow[5];
        $outstandingPrincipal = $newPrincipalAmount - $principalPaid;
        
        $status = isset($data['status']) ? $data['status'] : (isset($existingRow[16]) ? $existingRow[16] : 'Active');

        // Prepare row data matching the LOANS schema
        $row = [
            $id,
            isset($data['family_member_id']) ? $data['family_member_id'] : $existingRow[1],
            isset($data['loan_name']) ? $data['loan_name'] : $existingRow[2],
            isset($data['lender']) ? $data['lender'] : $existingRow[3],
            isset($data['loan_type']) ? $data['loan_type'] : $existingRow[4],
            $newPrincipalAmount,
            isset($data['interest_rate']) ? $data['interest_rate'] : $existingRow[6],
            isset($data['interest_type']) ? $data['interest_type'] : $existingRow[7],
            isset($data['tenure_months']) ? $data['tenure_months'] : $existingRow[8],
            isset($data['start_date']) ? $data['start_date'] : $existingRow[9],
            isset($data['emi_amount']) ? $data['emi_amount'] : $existingRow[10],
            $totalPaid,
            $principalPaid,
            $interestPaid,
            $outstandingPrincipal,
            isset($data['next_emi_date']) ? $data['next_emi_date'] : $existingRow[15],
            $status,
            isset($data['balance_calculation_method']) ? $data['balance_calculation_method'] : $existingRow[17],
            isset($data['notes']) ? $data['notes'] : (isset($existingRow[18]) ? $existingRow[18] : '')
        ];

        $sheets->updateRow('LOANS!A' . ($rowIndex + 1) . ':S' . ($rowIndex + 1), $row);
        
        echo json_encode(['success' => true, 'message' => 'Loan updated successfully']);
    } else if ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Sometimes DELETE requests might send data in URL parameters instead
        $id = isset($data['id']) ? $data['id'] : (isset($_GET['id']) ? $_GET['id'] : null);
        
        if (!$id) {
            throw new Exception("Missing loan ID");
        }

        $rowIndex = $sheets->findRowIndexById('LOANS!A:A', $id);
        
        if ($rowIndex === false) {
            throw new Exception("Loan not found");
        }

        // We need the sheet ID for the LOANS sheet to use the batchUpdate deleteRow method
        $metadata = $sheets->getSheetMetadata();
        if (!isset($metadata['LOANS'])) {
            throw new Exception("LOANS sheet not found in metadata");
        }
        
        $sheetId = $metadata['LOANS'];
        $sheets->deleteRow($sheetId, $rowIndex);
        
        echo json_encode(['success' => true, 'message' => 'Loan deleted successfully']);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
