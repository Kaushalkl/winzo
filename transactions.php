<?php
require_once 'db.php';
session_start(); if(empty($_SESSION['user_id'])){ header("Location:index.php"); exit; }
$uid = intval($_SESSION['user_id']);
$res = $conn->query("SELECT * FROM transactions WHERE user_id=$uid ORDER BY created_at DESC");
?>
<!doctype html><html><head><meta charset="utf-8"><title>Transactions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body>
<div class="container py-4">
  <div class="card p-3">
    <h4>Transaction History</h4>
    <table class="table">
      <thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Remark</th></tr></thead>
      <tbody>
        <?php while($t=$res->fetch_assoc()): ?>
          <tr>
            <td><?php echo $t['created_at']; ?></td>
            <td><?php echo ucfirst($t['type']); ?></td>
            <td>₹<?php echo $t['amount']; ?></td>
            <td><?php echo htmlspecialchars($t['remark']); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-link">Back</a>
  </div>
</div>
</body></html>
