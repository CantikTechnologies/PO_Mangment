<?php
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $actionReqBy = $_POST['actionReqBy'] ?? '';
    $requestDate = $_POST['requestDate'] ?? '';
    $costCenter = $_POST['costCenter'] ?? '';
    $actionReq = $_POST['actionReq'] ?? '';
    $actionOwner = $_POST['actionOwner'] ?? '';
    $status = $_POST['status'] ?? '';
    $completionDate = $_POST['completionDate'] ?? null;
    $remark = $_POST['remark'] ?? '';

    if (!$id || empty($actionReqBy) || empty($requestDate) || empty($costCenter) || 
        empty($actionReq) || empty($actionOwner) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        exit;
    }

    try {
        $pdo = getDatabaseConnection();
        $sql = "UPDATE finance_tasks SET 
                action_req_by = :action_req_by, 
                request_date = :request_date, 
                cost_center = :cost_center, 
                action_req = :action_req, 
                action_owner = :action_owner, 
                status = :status, 
                completion_date = :completion_date, 
                remark = :remark,
                updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':action_req_by', $actionReqBy);
        $stmt->bindParam(':request_date', $requestDate);
        $stmt->bindParam(':cost_center', $costCenter);
        $stmt->bindParam(':action_req', $actionReq);
        $stmt->bindParam(':action_owner', $actionOwner);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':completion_date', $completionDate);
        $stmt->bindParam(':remark', $remark);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or task not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>